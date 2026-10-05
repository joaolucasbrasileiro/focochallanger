<?php

namespace App\Services\Imports;

use App\Exceptions\Imports\XmlImportException;
use App\Models\ImportRun;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class XmlImportService
{
    public function __construct(
        private XmlDocumentLoader $documentLoader,
        private HotelXmlImporter $hotelImporter,
        private RoomXmlImporter $roomImporter,
        private ReservationXmlImporter $reservationImporter,
    ) {}

    public function run(): ImportRun
    {
        $importRun = ImportRun::query()->create([
            'status' => ImportRun::StatusRunning,
            'started_at' => now(),
        ]);

        try {
            // pega o pathFor com o padrão configurado para o docker e acrescenta a var (ex:hotels)
            // apontando assim para o xml específico desejado, depois é carregado pelo XmlDocumentLoader
            // por fim armazenado na var em formato de objeto xml (SimplXMElement)
            $hotels = $this->documentLoader->load($this->pathFor('hotels'));
            $rooms = $this->documentLoader->load($this->pathFor('rooms'));
            $reservations = $this->documentLoader->load($this->pathFor('reservations'));

            $counts = DB::transaction(function () use ($hotels, $rooms): array {
                // esse retorno é o valor/quantidade de elemento que foi processado/improtado
                // ficando salvo tabela ImportRun
                return [
                    'hotels_imported' => $this->hotelImporter->import($hotels),
                    'rooms_imported' => $this->roomImporter->import($rooms),
                ];
            });

            $reservationResult = $this->reservationImporter->import($reservations, $importRun);

            $importRun->update([
                'status' => $reservationResult->rejected > 0
                    ? ImportRun::StatusCompletedWithIssues
                    : ImportRun::StatusCompleted,
                'finished_at' => now(),
                'reservations_imported' => $reservationResult->imported,
                ...$counts,
            ]);
        } catch (Throwable $exception) {
            $importRun->update([
                'status' => ImportRun::StatusFailed,
                'finished_at' => now(),
                'error_message' => Str::limit($exception->getMessage(), 4_000),
            ]);

            $logContext = [
                'import_run_id' => $importRun->id,
                'exception' => $exception,
            ];

            if ($exception instanceof XmlImportException && $exception->technicalMessage() !== null) {
                $logContext['erro_libxml'] = $exception->technicalMessage();
            }

            Log::error('A importação XML falhou.', $logContext);

            throw $exception;
        }

        return $importRun->refresh();
    }

    private function pathFor(string $source): string
    {
        $path = config("imports.files.{$source}");

        if (! is_string($path) || trim($path) === '') {
            throw new \RuntimeException("O caminho de importação para [{$source}] não está configurado.");
        }

        return $path;
    }
}
