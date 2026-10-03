<?php

namespace App\Console\Commands;

use App\Services\Imports\XmlImportService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('imports:run')]
#[Description('Importa hotéis, quartos e reservas a partir de arquivos XML.')]
class RunImports extends Command
{
    public function handle(XmlImportService $importService): int
    {
        try {
            $importRun = $importService->run();

            $this->info("Importação concluída (execução {$importRun->id}): {$importRun->hotels_imported} hotéis,
            {$importRun->rooms_imported} quartos,
            {$importRun->reservations_imported} reservas.");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error("Falha na importação: {$exception->getMessage()}");

            return self::FAILURE;
        }
    }
}
