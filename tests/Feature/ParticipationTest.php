<?php

namespace Tests\Feature;

use App\Enums\SondageStatus;
use App\Models\Participation;
use App\Models\ParticipationReponse;
use App\Models\Question;
use App\Models\Sondage;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Réponse des participants à un sondage ouvert. */
class ParticipationTest extends TestCase
{
    use RefreshDatabase;

    private Sondage $sondage;

    private Question $texte;

    private Question $unique;

    private Question $multiple;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sondage = Sondage::factory()->enCours()->avecQuestions()->create(['titre' => 'Sondage ouvert']);
        [$this->texte, $this->unique, $this->multiple] = $this->sondage->questions()->with('options')->orderBy('id')->get()->all();
    }

    private function participant(string $role = 'client'): User
    {
        return User::factory()->role($role)->create();
    }

    private function reponsesValides(): array
    {
        return ['reponses' => [
            $this->texte->id => '  Très bon cours  ',
            $this->unique->id => $this->unique->options[0]->id,
            $this->multiple->id => [$this->multiple->options[0]->id, $this->multiple->options[2]->id],
        ]];
    }

    public function test_open_surveys_are_listed_for_participants(): void
    {
        $user = $this->participant();
        Sondage::factory()->statut(SondageStatus::VALIDE)->create(['titre' => 'Pas encore ouvert']);
        Sondage::factory()->enCours()->create(['titre' => 'Futur', 'date_debut' => today()->addDay()]);
        Sondage::factory()->enCours()->create(['titre' => 'Expiré', 'date_debut' => today()->subMonth(), 'date_fin' => today()->subDay()]);
        Sondage::factory()->for($user)->enCours()->create(['titre' => 'Le mien']);

        $this->actingAs($user)->get(route('participations.index'))
            ->assertOk()
            ->assertSee('Sondage ouvert')
            ->assertSee('Répondre')
            ->assertDontSee('Pas encore ouvert')
            ->assertDontSee('Futur')
            ->assertDontSee('Expiré')
            ->assertDontSee('Le mien');
    }

    public function test_participant_answers_all_question_types(): void
    {
        $user = $this->participant('sondeur');

        $this->actingAs($user)->get(route('participations.create', $this->sondage))
            ->assertOk()
            ->assertSee('Commentaire')
            ->assertSee('Python');

        $this->actingAs($user)->post(route('participations.store', $this->sondage), $this->reponsesValides())
            ->assertRedirect(route('participations.index'))
            ->assertSessionHas('success');

        $participation = Participation::sole();
        $this->assertSame($user->id, $participation->user_id);

        $reponses = ParticipationReponse::orderBy('id')->get();
        $this->assertCount(4, $reponses, 'texte + choix unique + deux cases cochées');
        $this->assertSame('Très bon cours', $reponses[0]->texte);
        $this->assertNull($reponses[0]->option_id);
        $this->assertSame($this->unique->options[0]->id, $reponses[1]->option_id);
        $this->assertEqualsCanonicalizing(
            [$this->multiple->options[0]->id, $this->multiple->options[2]->id],
            $reponses->where('question_id', $this->multiple->id)->pluck('option_id')->all()
        );

        $this->actingAs($user)->get(route('participations.index'))->assertSee('Déjà répondu');
    }

    /** @return array<string, array{0: callable, 1: string}> */
    public static function reponsesInvalides(): array
    {
        return [
            'réponse libre manquante' => [fn (self $t) => [$t->texte->id => ''], 'texte'],
            'réponse libre trop longue' => [fn (self $t) => [$t->texte->id => str_repeat('a', 2001)], 'texte'],
            'choix unique manquant' => [fn (self $t) => [$t->unique->id => null], 'unique'],
            'option d’une autre question' => [fn (self $t) => [$t->unique->id => $t->multiple->options[0]->id], 'unique'],
            'plusieurs choix pour un choix unique' => [fn (self $t) => [$t->unique->id => [$t->unique->options[0]->id]], 'unique'],
            'aucune case cochée' => [fn (self $t) => [$t->multiple->id => []], 'multiple'],
            'case d’une autre question' => [fn (self $t) => [$t->multiple->id => [$t->unique->options[1]->id]], 'multiple'],
            'case cochée en double' => [fn (self $t) => [$t->multiple->id => [$t->multiple->options[0]->id, $t->multiple->options[0]->id]], 'multiple'],
        ];
    }

    #[DataProvider('reponsesInvalides')]
    public function test_invalid_answers_are_rejected_and_nothing_is_saved(callable $modification, string $question): void
    {
        $donnees = $this->reponsesValides();
        $donnees['reponses'] = array_replace($donnees['reponses'], $modification($this));

        $cle = 'reponses.'.$this->{$question}->id;
        $this->actingAs($this->participant())
            ->post(route('participations.store', $this->sondage), $donnees)
            ->assertSessionHasErrors();

        $erreurs = session('errors')->getBag('default');
        $this->assertTrue($erreurs->has($cle) || $erreurs->has($cle.'.*'), "erreur attendue sur $cle");
        $this->assertDatabaseCount('participations', 0);
        $this->assertDatabaseCount('participation_reponses', 0);
    }

    public function test_a_participant_can_answer_only_once(): void
    {
        $user = $this->participant();

        $this->actingAs($user)->post(route('participations.store', $this->sondage), $this->reponsesValides())->assertSessionHas('success');
        $this->actingAs($user)->post(route('participations.store', $this->sondage), $this->reponsesValides())
            ->assertRedirect(route('participations.index'))
            ->assertSessionHas('info');
        $this->actingAs($user)->get(route('participations.create', $this->sondage))->assertRedirect(route('participations.index'));

        $this->assertDatabaseCount('participations', 1);
        $this->assertDatabaseCount('participation_reponses', 4);
    }

    public function test_the_database_rejects_a_duplicate_participation(): void
    {
        $user = $this->participant();
        Participation::create(['sondage_id' => $this->sondage->id, 'user_id' => $user->id]);

        $this->expectException(UniqueConstraintViolationException::class);
        Participation::create(['sondage_id' => $this->sondage->id, 'user_id' => $user->id]);
    }

    public function test_the_owner_cannot_answer_their_own_survey(): void
    {
        // Le créateur a la permission de répondre aux sondages : c'est bien la
        // règle « pas de réponse à son propre sondage » qui est vérifiée.
        $this->sondage->user->assignRole('client');

        $this->actingAs($this->sondage->user)
            ->post(route('participations.store', $this->sondage), $this->reponsesValides())
            ->assertForbidden();

        $this->assertDatabaseCount('participations', 0);
    }

    public function test_surveys_that_are_not_open_cannot_be_answered(): void
    {
        $user = $this->participant();
        $cas = [
            'en attente' => ['statut' => SondageStatus::EN_ATTENTE],
            'validé' => ['statut' => SondageStatus::VALIDE],
            'terminé' => ['statut' => SondageStatus::TERMINE],
            'pas encore commencé' => ['date_debut' => today()->addDay(), 'date_fin' => today()->addWeek()],
            'date de fin dépassée' => ['date_debut' => today()->subWeek(), 'date_fin' => today()->subDay()],
        ];

        foreach ($cas as $libelle => $attributs) {
            $this->sondage->update(array_merge(
                ['statut' => SondageStatus::EN_COURS, 'date_debut' => today()->subDay(), 'date_fin' => today()->addDay()],
                $attributs
            ));

            $this->actingAs($user)->get(route('participations.create', $this->sondage))->assertForbidden();
            $this->actingAs($user)->post(route('participations.store', $this->sondage), $this->reponsesValides())->assertForbidden();
            $this->assertSame(0, Participation::count(), "cas : $libelle");
        }
    }

    public function test_the_last_day_of_the_period_is_included(): void
    {
        $this->sondage->update(['date_fin' => today()]);

        $this->actingAs($this->participant())
            ->post(route('participations.store', $this->sondage), $this->reponsesValides())
            ->assertSessionHas('success');
    }

    public function test_users_without_a_role_cannot_answer(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('participations.index'))->assertOk()->assertDontSee('>Répondre<', false);
        $this->actingAs($user)->post(route('participations.store', $this->sondage), $this->reponsesValides())->assertForbidden();

        $this->assertDatabaseCount('participations', 0);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('participations.index'))->assertRedirect(route('login'));
        $this->post(route('participations.store', $this->sondage), $this->reponsesValides())->assertRedirect(route('login'));
    }
}
