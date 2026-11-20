<?php

namespace Tests\Feature;

use App\Enums\SondageStatus;
use App\Models\Participation;
use App\Models\Question;
use App\Models\Sondage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Statistiques des réponses et accès aux résultats. */
class ResultatsTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Sondage $sondage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->role('client')->create();
        $this->sondage = Sondage::factory()->for($this->owner)->enCours()->avecQuestions()->create();
    }

    /** Enregistre une participation : [texte, option du choix unique, options cochées]. */
    private function repondre(?string $texte, string $choix, array $cases): void
    {
        [$libre, $unique, $multiple] = $this->sondage->questions()->with('options')->orderBy('id')->get()->all();
        $option = fn (Question $question, string $contenu) => $question->options->firstWhere('contenu', $contenu)->id;

        $participation = Participation::create([
            'sondage_id' => $this->sondage->id,
            'user_id' => User::factory()->create(['name' => 'Participant '.Participation::count()])->id,
        ]);
        $participation->reponses()->create(['question_id' => $libre->id, 'texte' => $texte]);
        $participation->reponses()->create(['question_id' => $unique->id, 'option_id' => $option($unique, $choix)]);
        foreach ($cases as $contenu) {
            $participation->reponses()->create(['question_id' => $multiple->id, 'option_id' => $option($multiple, $contenu)]);
        }
    }

    public function test_results_show_counts_and_percentages_per_option(): void
    {
        $this->repondre('Très clair', 'Oui', ['PHP', 'Java']);
        $this->repondre('Un peu long', 'Oui', ['PHP']);
        $this->repondre('RAS', 'Oui', ['PHP']);
        $this->repondre('Parfait', 'Non', ['PHP']);

        $this->actingAs($this->owner)->get(route('sondages.resultats', $this->sondage))
            ->assertOk()
            ->assertSeeInOrder(['Participants', '4'])
            // Choix unique : 3 « Oui » et 1 « Non » sur 4 participants
            ->assertSeeInOrder(['Oui', '3 · 75,0 %', 'Non', '1 · 25,0 %'])
            // Choix multiples : pourcentages des participants (total > 100 %)
            ->assertSeeInOrder(['PHP', '4 · 100,0 %', 'Java', '1 · 25,0 %', 'Python', '0 · 0,0 %'])
            ->assertSee('Très clair')
            ->assertSee('Un peu long')
            // Les réponses libres sont présentées sans le nom des participants
            ->assertDontSee('Participant 0');
    }

    public function test_results_without_any_answer(): void
    {
        $this->actingAs($this->owner)->get(route('sondages.resultats', $this->sondage))
            ->assertOk()
            ->assertSee('Aucune réponse pour le moment.')
            ->assertSee('0 · 0,0 %');
    }

    public function test_owner_and_administrator_can_view_results_of_open_or_finished_surveys(): void
    {
        $admin = User::factory()->role('administrateur')->create();

        foreach ([SondageStatus::EN_COURS, SondageStatus::TERMINE] as $statut) {
            $this->sondage->update(['statut' => $statut]);

            $this->actingAs($this->owner)->get(route('sondages.resultats', $this->sondage))->assertOk();
            $this->actingAs($admin)->get(route('sondages.resultats', $this->sondage))->assertOk();
        }
    }

    public function test_other_users_cannot_view_results(): void
    {
        foreach (['client', 'commercial', 'sondeur'] as $role) {
            $this->actingAs(User::factory()->role($role)->create())
                ->get(route('sondages.resultats', $this->sondage))
                ->assertForbidden();
        }
    }

    public function test_results_are_not_available_before_the_survey_is_opened(): void
    {
        foreach ([SondageStatus::EN_ATTENTE, SondageStatus::VALIDE, SondageStatus::REFUSE] as $statut) {
            $this->sondage->update(['statut' => $statut]);

            $this->actingAs($this->owner)->get(route('sondages.resultats', $this->sondage))->assertForbidden();
        }
    }
}
