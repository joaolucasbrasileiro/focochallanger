<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Hotel;
use App\Models\ImportIssue;
use App\Models\ImportRun;
use App\Models\Reservation;
use App\Models\ReservationDaily;
use App\Models\ReservationGuest;
use App\Models\ReservationPayment;
use App\Models\Room;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tests\TestCase;

class RunImportsTest extends TestCase
{
    use LazilyRefreshDatabase;

    private string $importsPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importsPath = storage_path('framework/testing/imports/'.Str::uuid());
        File::ensureDirectoryExists($this->importsPath);

        config([
            'imports.files.hotels' => $this->importsPath.'/hotels.xml',
            'imports.files.rooms' => $this->importsPath.'/rooms.xml',
            'imports.files.reservations' => $this->importsPath.'/reserves.xml',
        ]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->importsPath);

        parent::tearDown();
    }

    public function test_command_imports_xml_files_and_records_a_completed_run(): void
    {
        $this->writeImportFiles();

        $this->artisan('imports:run')
            ->assertExitCode(0);

        $hotel = Hotel::query()->where('external_id', 1)->sole();
        $room = Room::query()->where('external_id', 10)->sole();
        $reservation = Reservation::query()->where('external_id', 100)->sole();
        $importRun = ImportRun::query()->sole();

        $this->assertSame('Hotel Foco Prime', $hotel->name);
        $this->assertSame($hotel->id, $room->hotel_id);
        $this->assertSame($room->id, $reservation->room_id);
        $this->assertSame('2022-12-01', $reservation->check_in->toDateString());
        $this->assertSame('300.00', $reservation->total);
        $this->assertSame(2, ReservationGuest::query()->count());
        $this->assertSame(2, ReservationDaily::query()->count());
        $this->assertSame(1, ReservationPayment::query()->count());
        $this->assertSame(ImportRun::StatusCompleted, $importRun->status);
        $this->assertSame(2, $importRun->hotels_imported);
        $this->assertSame(2, $importRun->rooms_imported);
        $this->assertSame(1, $importRun->reservations_imported);
        $this->assertNotNull($importRun->finished_at);
    }

    public function test_command_is_idempotent_and_refreshes_reservation_children(): void
    {
        $this->writeImportFiles();

        $this->artisan('imports:run')
            ->assertExitCode(0);

        $this->writeImportFiles(
            hotels: <<<'XML'
                <Hotels>
                    <Hotel id="1"><Name>Hotel Foco Prime Updated</Name></Hotel>
                    <Hotel id="2"><Name>Hotel Foco Beach</Name></Hotel>
                </Hotels>
                XML,
            reserves: <<<'XML'
                <Reserves>
                    <Reserve id="100" hotelCode="1" roomCode="10">
                        <CheckIn>2022-12-01</CheckIn>
                        <CheckOut>2022-12-03</CheckOut>
                        <Total>450.00</Total>
                        <Guests>
                            <Guest><Name>Updated</Name><LastName>Guest</LastName><Phone>5571991111111</Phone></Guest>
                        </Guests>
                        <Dailies>
                            <Daily><Date>2022-12-01</Date><Value>225.00</Value></Daily>
                            <Daily><Date>2022-12-02</Date><Value>225.00</Value></Daily>
                        </Dailies>
                        <Payments>
                            <Payment><Method>2</Method><Value>450.00</Value></Payment>
                        </Payments>
                    </Reserve>
                </Reserves>
                XML,
        );

        $this->artisan('imports:run')
            ->assertExitCode(0);

        $reservation = Reservation::query()->where('external_id', 100)->sole();

        $this->assertSame(2, Hotel::query()->count());
        $this->assertSame(2, Room::query()->count());
        $this->assertSame(1, Reservation::query()->count());
        $this->assertSame('Hotel Foco Prime Updated', Hotel::query()->where('external_id', 1)->sole()->name);
        $this->assertSame('450.00', $reservation->total);
        $this->assertSame(1, ReservationGuest::query()->count());
        $this->assertSame('Updated', $reservation->guests()->sole()->first_name);
        $this->assertSame(2, ReservationDaily::query()->count());
        $this->assertSame(1, ReservationPayment::query()->count());
        $this->assertSame('2', $reservation->payments()->sole()->method_code);
        $this->assertSame(2, ImportRun::query()->where('status', ImportRun::StatusCompleted)->count());
    }

    public function test_command_rolls_back_data_when_a_room_references_an_unknown_hotel(): void
    {
        $this->writeImportFiles(
            rooms: <<<'XML'
                <Rooms>
                    <Room id="10" hotelCode="999"><Name>Room 10</Name></Room>
                </Rooms>
                XML,
            reserves: '<Reserves />',
        );

        $this->artisan('imports:run')
            ->expectsOutput('Falha na importação: O quarto [10] referencia o hotel inexistente [999].')
            ->assertExitCode(1);

        $importRun = ImportRun::query()->sole();

        $this->assertSame(0, Hotel::query()->count());
        $this->assertSame(0, Room::query()->count());
        $this->assertSame(0, Reservation::query()->count());
        $this->assertSame(ImportRun::StatusFailed, $importRun->status);
        $this->assertSame('O quarto [10] referencia o hotel inexistente [999].', $importRun->error_message);
        $this->assertNotNull($importRun->finished_at);
    }

    public function test_command_records_an_issue_when_a_reservation_has_an_invalid_stay_period(): void
    {
        $this->writeImportFiles(
            reserves: <<<'XML'
                <Reserves>
                    <Reserve id="100" hotelCode="1" roomCode="10">
                        <CheckIn>2022-12-03</CheckIn>
                        <CheckOut>2022-12-01</CheckOut>
                        <Total>300.00</Total>
                    </Reserve>
                </Reserves>
                XML,
        );

        $this->artisan('imports:run')
            ->assertExitCode(0);

        $importRun = ImportRun::query()->sole();
        $issue = ImportIssue::query()->sole();

        $this->assertSame(2, Hotel::query()->count());
        $this->assertSame(2, Room::query()->count());
        $this->assertSame(0, Reservation::query()->count());
        $this->assertSame(ImportRun::StatusCompletedWithIssues, $importRun->status);
        $this->assertSame(ImportIssue::SourceReservation, $issue->source);
        $this->assertSame('100', $issue->external_identifier);
        $this->assertSame('invalid_stay_period', $issue->error_code);
        $this->assertSame(ImportIssue::StatusIncomplete, $issue->status);
    }

    public function test_command_records_an_issue_when_a_daily_is_outside_the_reservation_period(): void
    {
        $this->writeImportFiles(
            reserves: <<<'XML'
                <Reserves>
                    <Reserve id="100" hotelCode="1" roomCode="10">
                        <CheckIn>2022-12-01</CheckIn>
                        <CheckOut>2022-12-03</CheckOut>
                        <Total>300.00</Total>
                        <Dailies>
                            <Daily><Date>2022-12-03</Date><Value>300.00</Value></Daily>
                        </Dailies>
                    </Reserve>
                </Reserves>
                XML,
        );

        $this->artisan('imports:run')
            ->assertExitCode(0);

        $importRun = ImportRun::query()->sole();
        $issue = ImportIssue::query()->sole();

        $this->assertSame(2, Hotel::query()->count());
        $this->assertSame(2, Room::query()->count());
        $this->assertSame(0, Reservation::query()->count());
        $this->assertSame(0, ReservationDaily::query()->count());
        $this->assertSame(ImportRun::StatusCompletedWithIssues, $importRun->status);
        $this->assertSame('daily_outside_stay_period', $issue->error_code);
        $this->assertStringContainsString('<Reserve id="100"', (string) $issue->raw_payload);
    }

    public function test_command_imports_valid_reservations_when_another_reservation_has_an_issue(): void
    {
        $this->writeImportFiles(
            reserves: <<<'XML'
                <Reserves>
                    <Reserve id="100" hotelCode="1" roomCode="10">
                        <CheckIn>2022-12-01</CheckIn>
                        <CheckOut>2022-12-03</CheckOut>
                        <Total>300.00</Total>
                        <Dailies>
                            <Daily><Date>2022-12-01</Date><Value>150.00</Value></Daily>
                            <Daily><Date>2022-12-02</Date><Value>150.00</Value></Daily>
                        </Dailies>
                    </Reserve>
                    <Reserve id="101" hotelCode="1" roomCode="10">
                        <CheckIn>2022-12-01</CheckIn>
                        <CheckOut>2022-12-03</CheckOut>
                        <Total>300.00</Total>
                        <Dailies>
                            <Daily><Date>2022-12-03</Date><Value>300.00</Value></Daily>
                        </Dailies>
                    </Reserve>
                </Reserves>
                XML,
        );

        $this->artisan('imports:run')
            ->assertExitCode(0);

        $importRun = ImportRun::query()->sole();
        $issue = ImportIssue::query()->sole();

        $this->assertSame(1, Reservation::query()->count());
        $this->assertSame(100, Reservation::query()->sole()->external_id);
        $this->assertSame(ImportRun::StatusCompletedWithIssues, $importRun->status);
        $this->assertSame(1, $importRun->reservations_imported);
        $this->assertSame('101', $issue->external_identifier);
        $this->assertSame('daily_outside_stay_period', $issue->error_code);
    }

    public function test_command_logs_libxml_details_without_exposing_them_in_the_user_message(): void
    {
        Log::spy();

        $this->writeImportFiles(
            hotels: '<Hotels><Hotel id="1"></Hotels>',
        );

        $this->artisan('imports:run')
            ->expectsOutput('Falha na importação: Não foi possível interpretar o arquivo XML ['.$this->importsPath.'/hotels.xml]. O conteúdo XML é inválido.')
            ->assertExitCode(1);

        $this->assertSame(
            "Não foi possível interpretar o arquivo XML [{$this->importsPath}/hotels.xml]. O conteúdo XML é inválido.",
            ImportRun::query()->sole()->error_message,
        );

        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'A importação XML falhou.'
                    && isset($context['erro_libxml'])
                    && $context['erro_libxml'] !== '';
            });
    }

    private function writeImportFiles(?string $hotels = null, ?string $rooms = null, ?string $reserves = null): void
    {
        File::put($this->importsPath.'/hotels.xml', $hotels ?? <<<'XML'
            <Hotels>
                <Hotel id="1"><Name>Hotel Foco Prime</Name></Hotel>
                <Hotel id="2"><Name>Hotel Foco Beach</Name></Hotel>
            </Hotels>
            XML);

        File::put($this->importsPath.'/rooms.xml', $rooms ?? <<<'XML'
            <Rooms>
                <Room id="10" hotelCode="1"><Name>Room 10</Name></Room>
                <Room id="20" hotelCode="2"><Name>Room 20</Name></Room>
            </Rooms>
            XML);

        File::put($this->importsPath.'/reserves.xml', $reserves ?? <<<'XML'
            <Reserves>
                <Reserve id="100" hotelCode="1" roomCode="10">
                    <CheckIn>2022-12-01</CheckIn>
                    <CheckOut>2022-12-03</CheckOut>
                    <Total>300.00</Total>
                    <Guests>
                        <Guest><Name>Ana</Name><LastName>Silva</LastName><Phone>5571990000001</Phone></Guest>
                        <Guest><Name>Joao</Name><LastName>Silva</LastName><Phone>5571990000002</Phone></Guest>
                    </Guests>
                    <Dailies>
                        <Daily><Date>2022-12-01</Date><Value>150.00</Value></Daily>
                        <Daily><Date>2022-12-02</Date><Value>150.00</Value></Daily>
                    </Dailies>
                    <Payments>
                        <Payment><Method>1</Method><Value>300.00</Value></Payment>
                    </Payments>
                </Reserve>
            </Reserves>
            XML);
    }
}
