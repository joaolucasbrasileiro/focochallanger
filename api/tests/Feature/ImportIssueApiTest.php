<?php

namespace Tests\Feature;

use App\Models\ImportIssue;
use App\Models\ImportRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportIssueApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_import_issues_with_filters(): void
    {
        $reservationIssue = ImportIssue::factory()->create([
            'source' => ImportIssue::SourceReservation,
            'status' => ImportIssue::StatusIncomplete,
            'external_identifier' => '6',
        ]);
        ImportIssue::factory()->create([
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
        $issue = ImportIssue::factory()->create([
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
        ImportIssue::factory()->for($run)->count(2)->create([
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
        ImportIssue::factory()->for($run)->create([
            'source' => ImportIssue::SourceReservation,
        ]);

        $this->getJson("/api/v1/import-runs/{$run->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $run->id)
            ->assertJsonPath('data.issues_count', 1)
            ->assertJsonPath('data.reservation_issues_count', 1);
    }
}
