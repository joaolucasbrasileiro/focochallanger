<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\HotelMembership;
use App\Models\ImportIssue;
use App\Models\ImportRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ImportIssueApiTest extends TestCase
{
    use RefreshDatabase;

    private Hotel $hotel;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->hotel = Hotel::factory()->create();
        HotelMembership::factory()
            ->for($this->hotel)
            ->for($user)
            ->manager()
            ->create();
        Sanctum::actingAs($user, ['api:access']);
    }

    public function test_it_lists_import_issues_with_filters(): void
    {
        $reservationIssue = ImportIssue::factory()->forHotel($this->hotel)->create([
            'source' => ImportIssue::SourceReservation,
            'status' => ImportIssue::StatusIncomplete,
            'external_identifier' => '6',
        ]);
        ImportIssue::factory()->forHotel($this->hotel)->create([
            'source' => ImportIssue::SourceRoom,
            'status' => ImportIssue::StatusIncomplete,
        ]);

        $this->getJson('/api/v1/import-issues?source=reservation&status=incomplete')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $reservationIssue->id)
            ->assertJsonPath('data.0.external_identifier', '6')
            ->assertJsonPath('data.0.import_run.id', $reservationIssue->import_run_id);
    }

    public function test_it_shows_an_import_issue_with_its_original_payload(): void
    {
        $issue = ImportIssue::factory()->forHotel($this->hotel)->create([
            'raw_payload' => '<Reserve id="6"><Dailies /></Reserve>',
            'error_code' => 'daily_outside_stay_period',
        ]);

        $this->getJson("/api/v1/import-issues/{$issue->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $issue->id)
            ->assertJsonPath('data.error_code', 'daily_outside_stay_period')
            ->assertJsonPath('data.raw_payload', '<Reserve id="6"><Dailies /></Reserve>');
    }

    public function test_it_lists_import_runs_with_issue_counts(): void
    {
        $run = ImportRun::factory()->create([
            'status' => ImportRun::StatusCompletedWithIssues,
        ]);
        ImportIssue::factory()->for($run)->forHotel($this->hotel)->count(2)->create([
            'source' => ImportIssue::SourceReservation,
        ]);

        $this->getJson('/api/v1/import-runs')
            ->assertOk()
            ->assertJsonPath('data.0.id', $run->id)
            ->assertJsonPath('data.0.status', ImportRun::StatusCompletedWithIssues)
            ->assertJsonPath('data.0.issues_count', 2)
            ->assertJsonPath('data.0.reservation_issues_count', 2);
    }

    public function test_it_shows_an_import_run_with_issue_counts(): void
    {
        $run = ImportRun::factory()->create();
        ImportIssue::factory()->for($run)->forHotel($this->hotel)->create([
            'source' => ImportIssue::SourceReservation,
        ]);

        $this->getJson("/api/v1/import-runs/{$run->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $run->id)
            ->assertJsonPath('data.issues_count', 1)
            ->assertJsonPath('data.reservation_issues_count', 1);
    }

    public function test_it_does_not_expose_an_issue_from_another_hotel(): void
    {
        $otherHotel = Hotel::factory()->create();
        $issue = ImportIssue::factory()->forHotel($otherHotel)->create();

        $this->getJson('/api/v1/import-issues')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->getJson("/api/v1/import-issues/{$issue->id}")
            ->assertForbidden()
            ->assertJsonPath('message', 'Você não tem permissão para consultar esta pendência de importação.');
    }
}
