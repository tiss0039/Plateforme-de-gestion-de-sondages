<?php

namespace Tests\Feature;

use App\Enums\SondageStatus;
use App\Models\Option;
use App\Models\Participation;
use App\Models\Sondage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

/** Création, consultation, modification et suppression d'un sondage par son créateur. */
class SondageTest extends TestCase
{
    use RefreshDatabase;

    private function client(): User
    {
        return User::factory()->role('client')->create();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_replace_recursive([
            'titre' => 'Satisfaction client',
            'description' => 'Sondage de test',
            'date_debut' => '2026-12-01',
            'date_fin' => '2026-12-31',
            'questions' => [
                ['title' => 'Satisfaction', 'question' => 'Êtes-vous satisfait ?', 'type_question' => 'multiple', 'options' => ['Oui', 'Non']],
                ['title' => 'Langages', 'question' => '', 'type_question' => 'checkbox', 'options' => ['PHP', 'Java', 'Python']],
                ['title' => 'Commentaire', 'question' => 'Un commentaire ?', 'type_question' => 'text'],
            ],
        ], $overrides);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('sondages.create'))->assertRedirect(route('login'));
        $this->post(route('sondages.store'), $this->validPayload())->assertRedirect(route('login'));
        $this->assertDatabaseCount('sondages', 0);
    }

    public function test_client_creates_a_survey_with_the_three_question_types(): void
    {
        $user = $this->client();

        $this->actingAs($user)->get(route('sondages.create'))->assertOk()->assertSee('Créer le sondage');

        $response = $this->actingAs($user)->post(route('sondages.store'), $this->validPayload());

        $sondage = Sondage::sole();
        $response->assertRedirect(route('sondages.show', $sondage))->assertSessionHas('success');
        $this->assertSame($user->id, $sondage->user_id);
        $this->assertSame(SondageStatus::EN_ATTENTE, $sondage->statut);
        $this->assertSame('2026-12-31', $sondage->date_fin->format('Y-m-d'));

        $questions = $sondage->questions()->orderBy('id')->get();
        $this->assertSame(['multiple', 'checkbox', 'text'], $questions->pluck('question_type')->all());
        $this->assertSame('Êtes-vous satisfait ?', $questions[0]->question);
        $this->assertNull($questions[1]->question, 'une précision vide est enregistrée comme absente');
        $this->assertSame(['Oui', 'Non'], $questions[0]->options()->orderBy('id')->pluck('contenu')->all());
        $this->assertSame(['PHP', 'Java', 'Python'], $questions[1]->options()->orderBy('id')->pluck('contenu')->all());
        $this->assertSame(0, $questions[2]->options()->count());
    }

    public function test_options_sent_with_a_free_text_question_are_ignored(): void
    {
        $payload = $this->validPayload();
        $payload['questions'][2]['options'] = ['Ignorée'];

        $this->actingAs($this->client())->post(route('sondages.store'), $payload)->assertSessionHasNoErrors();

        $this->assertSame(5, Option::count());
    }

    /** @return array<string, array{0: array, 1: string}> */
    public static function invalidPayloads(): array
    {
        return [
            'sans question' => [['questions' => null], 'questions'],
            'titre manquant' => [['titre' => ''], 'titre'],
            'intitulé de question manquant' => [['questions' => [0 => ['title' => '']]], 'questions.0.title'],
            'type de question inconnu' => [['questions' => [0 => ['type_question' => 'inconnu']]], 'questions.0.type_question'],
            'question à choix sans option' => [['questions' => [0 => ['options' => null]]], 'questions.0.options'],
            'question à choix avec une seule option' => [['questions' => [0 => ['options' => [1 => null]]]], 'questions.0.options.1'],
            'options en double (casse ignorée)' => [['questions' => [1 => ['options' => [1 => 'php']]]], 'questions.1.options'],
            'option trop longue' => [['questions' => [0 => ['options' => [0 => str_repeat('a', 256)]]]], 'questions.0.options.0'],
            'date de fin avant la date de début' => [['date_debut' => '2026-12-31', 'date_fin' => '2026-12-01'], 'date_fin'],
            'date invalide' => [['date_debut' => 'demain'], 'date_debut'],
        ];
    }

    #[DataProvider('invalidPayloads')]
    public function test_invalid_input_is_rejected_and_nothing_is_saved(array $overrides, string $errorKey): void
    {
        $payload = $this->validPayload($overrides);
        if (array_key_exists('questions', $overrides) && $overrides['questions'] === null) {
            unset($payload['questions']);
        }

        $this->actingAs($this->client())
            ->post(route('sondages.store'), $payload)
            ->assertSessionHasErrors($errorKey);

        $this->assertDatabaseCount('sondages', 0);
        $this->assertDatabaseCount('questions', 0);
        $this->assertDatabaseCount('options', 0);
    }

    public function test_validation_errors_are_shown_in_french_on_the_form(): void
    {
        $user = $this->client();

        $this->actingAs($user)
            ->from(route('sondages.create'))
            ->followingRedirects()
            ->post(route('sondages.store'), ['titre' => 'Sans questions', 'date_debut' => '2026-12-01', 'date_fin' => '2026-12-31'])
            ->assertOk()
            ->assertSeeText('Le sondage n\'a pas été enregistré', false)
            ->assertSeeText('Ajoutez au moins une question.')
            ->assertSee('value="Sans questions"', false);
    }

    public function test_questions_and_options_are_kept_after_a_validation_error(): void
    {
        $payload = $this->validPayload();
        $payload['questions'][0]['options'] = ['Seule option'];

        $this->actingAs($this->client())
            ->from(route('sondages.create'))
            ->followingRedirects()
            ->post(route('sondages.store'), $payload)
            ->assertOk()
            ->assertSeeText('Une question à choix doit proposer au moins deux options.')
            // Les questions saisies sont réinjectées dans le formulaire dynamique.
            ->assertSee('Seule option')
            ->assertSee('Un commentaire ?')
            ->assertSee('Python');

        $this->assertDatabaseCount('sondages', 0);
    }

    public function test_two_questions_may_offer_the_same_option(): void
    {
        $payload = $this->validPayload(['questions' => [1 => ['options' => ['Oui', 'Non', 'Peut-être']]]]);

        $this->actingAs($this->client())->post(route('sondages.store'), $payload)->assertSessionHasNoErrors();

        $this->assertDatabaseCount('sondages', 1);
    }

    public function test_a_failure_while_saving_leaves_no_partial_survey(): void
    {
        Option::creating(function () {
            throw new RuntimeException('Erreur simulée');
        });

        $this->actingAs($this->client())
            ->post(route('sondages.store'), $this->validPayload())
            ->assertStatus(500);

        $this->assertDatabaseCount('sondages', 0);
        $this->assertDatabaseCount('questions', 0);
    }

    public function test_dashboard_lists_only_the_users_own_surveys_with_their_status(): void
    {
        $user = $this->client();
        Sondage::factory()->for($user)->statut(SondageStatus::REFUSE)->create(['titre' => 'Mon sondage']);
        Sondage::factory()->create(['titre' => 'Sondage d’un autre']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Mon sondage')
            ->assertSee('Refusé')
            ->assertDontSee('Sondage d’un autre');
    }

    public function test_dashboard_lists_surveys_newest_first_even_when_created_in_the_same_second(): void
    {
        $user = $this->client();
        $this->freezeSecond();
        foreach (['Premier', 'Deuxième', 'Troisième'] as $titre) {
            Sondage::factory()->for($user)->create(['titre' => $titre]);
        }

        $this->actingAs($user)->get(route('dashboard'))->assertSeeInOrder(['Troisième', 'Deuxième', 'Premier']);
    }

    public function test_owner_sees_the_survey_details_and_questions(): void
    {
        $user = $this->client();
        $sondage = Sondage::factory()->for($user)->avecQuestions()->create(['titre' => 'Sondage existant']);

        $this->actingAs($user)
            ->get(route('sondages.show', $sondage))
            ->assertOk()
            ->assertSee('Sondage existant')
            ->assertSee('En attente')
            ->assertSee('Choix unique')
            ->assertSee('Python');
    }

    public function test_edit_form_is_prefilled_with_questions_and_dates(): void
    {
        $user = $this->client();
        $sondage = Sondage::factory()->for($user)->avecQuestions()->create(['date_debut' => '2026-12-01']);

        $this->actingAs($user)
            ->get(route('sondages.edit', $sondage))
            ->assertOk()
            ->assertSee('value="2026-12-01"', false)
            ->assertSee('Satisfaction')
            ->assertSee('Python');
    }

    public function test_owner_updates_the_survey_and_replaces_its_questions(): void
    {
        $user = $this->client();
        $sondage = Sondage::factory()->for($user)->avecQuestions()->create();

        $payload = $this->validPayload(['titre' => 'Titre modifié']);
        $payload['questions'] = [['title' => 'Nouvelle question', 'type_question' => 'text']];

        $this->actingAs($user)
            ->put(route('sondages.update', $sondage), $payload)
            ->assertRedirect(route('sondages.show', $sondage));

        $sondage->refresh();
        $this->assertSame('Titre modifié', $sondage->titre);
        $this->assertSame(['Nouvelle question'], $sondage->questions()->pluck('title')->all());
        $this->assertSame(0, Option::count(), 'les anciennes options sont supprimées avec leurs questions');
    }

    public function test_editing_a_validated_or_refused_survey_requires_a_new_validation(): void
    {
        $user = $this->client();

        foreach ([SondageStatus::VALIDE, SondageStatus::REFUSE] as $statut) {
            $sondage = Sondage::factory()->for($user)->statut($statut)->avecQuestions()
                ->create(['motif_refus' => 'Trop vague', 'modere_le' => now()]);

            $this->actingAs($user)->put(route('sondages.update', $sondage), $this->validPayload())->assertSessionHasNoErrors();

            $sondage->refresh();
            $this->assertSame(SondageStatus::EN_ATTENTE, $sondage->statut);
            $this->assertNull($sondage->motif_refus);
            $this->assertNull($sondage->modere_le);
        }
    }

    public function test_open_or_finished_surveys_cannot_be_edited(): void
    {
        $user = $this->client();

        foreach ([SondageStatus::EN_COURS, SondageStatus::TERMINE] as $statut) {
            $sondage = Sondage::factory()->for($user)->statut($statut)->avecQuestions()->create(['titre' => 'Figé']);

            $this->actingAs($user)->get(route('sondages.edit', $sondage))->assertForbidden();
            $this->actingAs($user)->put(route('sondages.update', $sondage), $this->validPayload())->assertForbidden();
            $this->assertSame('Figé', $sondage->fresh()->titre);
        }
    }

    public function test_owner_deletes_a_survey_with_its_questions_and_responses(): void
    {
        $user = $this->client();
        $sondage = Sondage::factory()->for($user)->statut(SondageStatus::TERMINE)->avecQuestions()->create();
        Participation::create(['sondage_id' => $sondage->id, 'user_id' => User::factory()->create()->id]);

        $this->actingAs($user)
            ->delete(route('sondages.destroy', $sondage))
            ->assertRedirect(route('dashboard'));

        $this->assertModelMissing($sondage);
        $this->assertDatabaseCount('questions', 0);
        $this->assertDatabaseCount('options', 0);
        $this->assertDatabaseCount('participations', 0);
    }

    public function test_an_open_survey_must_be_closed_before_deletion(): void
    {
        $user = $this->client();
        $sondage = Sondage::factory()->for($user)->enCours()->create();

        $this->actingAs($user)->delete(route('sondages.destroy', $sondage))->assertForbidden();

        $this->assertModelExists($sondage);
    }

    public function test_status_and_owner_cannot_be_forced_through_the_form(): void
    {
        $user = $this->client();
        $autre = $this->client();
        $champsInterdits = ['statut' => SondageStatus::EN_COURS->value, 'user_id' => $autre->id, 'moderateur_id' => $user->id];

        $this->actingAs($user)->post(route('sondages.store'), $this->validPayload() + $champsInterdits)->assertSessionHasNoErrors();
        $sondage = Sondage::sole();
        $this->assertSame(SondageStatus::EN_ATTENTE, $sondage->statut);
        $this->assertSame($user->id, $sondage->user_id);
        $this->assertNull($sondage->moderateur_id);

        $sondage->update(['statut' => SondageStatus::REFUSE]);
        $this->actingAs($user)->put(route('sondages.update', $sondage), $this->validPayload() + $champsInterdits)->assertSessionHasNoErrors();
        $sondage->refresh();
        $this->assertSame(SondageStatus::EN_ATTENTE, $sondage->statut);
        $this->assertSame($user->id, $sondage->user_id);
    }

    public function test_an_administrator_can_delete_another_users_survey_unless_it_is_open(): void
    {
        $admin = User::factory()->role('administrateur')->create();
        $termine = Sondage::factory()->statut(SondageStatus::TERMINE)->create();
        $ouvert = Sondage::factory()->enCours()->create();

        $this->actingAs($admin)->delete(route('sondages.destroy', $termine))->assertRedirect(route('dashboard'));
        $this->actingAs($admin)->delete(route('sondages.destroy', $ouvert))->assertForbidden();

        $this->assertModelMissing($termine);
        $this->assertModelExists($ouvert);
    }

    public function test_other_users_cannot_view_edit_update_or_delete_a_survey(): void
    {
        $sondage = Sondage::factory()->avecQuestions()->create(['titre' => 'Sondage existant']);
        $other = $this->client();

        $this->actingAs($other)->get(route('sondages.show', $sondage))->assertForbidden();
        $this->actingAs($other)->get(route('sondages.edit', $sondage))->assertForbidden();
        // L'autorisation est vérifiée avant la validation : même un envoi invalide reçoit un 403.
        $this->actingAs($other)->put(route('sondages.update', $sondage), ['titre' => 'Piraté'])->assertForbidden();
        $this->actingAs($other)->delete(route('sondages.destroy', $sondage))->assertForbidden();

        $this->assertSame('Sondage existant', $sondage->fresh()->titre);
        $this->assertSame(3, $sondage->questions()->count());
    }
}
