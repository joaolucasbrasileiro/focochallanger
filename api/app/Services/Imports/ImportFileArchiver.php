<?php

namespace App\Services\Imports;

use App\Models\ImportRun;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Throwable;

class ImportFileArchiver
{
    public function archive(ImportRun $importRun): void
    {
        $archiveDirectory = $this->archiveDirectory($importRun);
        $files = $this->filesToArchive();
        $movedFiles = [];

        if (File::exists($archiveDirectory)) {
            throw new RuntimeException("O diretório de arquivamento [{$archiveDirectory}] já existe.");
        }

        File::ensureDirectoryExists($archiveDirectory);

        try {
            foreach ($files as $sourcePath) {
                $destinationPath = $archiveDirectory.'/'.basename($sourcePath);

                if (! File::move($sourcePath, $destinationPath)) {
                    throw new RuntimeException("Não foi possível arquivar o arquivo XML [{$sourcePath}].");
                }

                $movedFiles[$sourcePath] = $destinationPath;
            }
        } catch (Throwable $exception) {
            $this->restoreMovedFiles($movedFiles);
            File::deleteDirectory($archiveDirectory);

            throw $exception;
        }
    }

    private function archiveDirectory(ImportRun $importRun): string
    {
        $archivePath = config('imports.archive_path');

        if (! is_string($archivePath) || trim($archivePath) === '') {
            throw new RuntimeException('O caminho de arquivamento dos XMLs não está configurado.');
        }

        $date = ($importRun->finished_at ?? now())->toDateString();

        return rtrim($archivePath, '/')."/{$date}/run-{$importRun->id}";
    }

    /**
     * @return list<string>
     */
    private function filesToArchive(): array
    {
        $files = config('imports.files');

        if (! is_array($files)) {
            throw new RuntimeException('Os arquivos XML de importação não estão configurados.');
        }

        $paths = [];

        foreach ($files as $source => $path) {
            if (! is_string($path) || trim($path) === '') {
                throw new RuntimeException("O caminho de importação para [{$source}] não está configurado.");
            }

            if (! File::isFile($path)) {
                throw new RuntimeException("O arquivo XML [{$path}] não foi encontrado para arquivamento.");
            }

            $paths[] = $path;
        }

        return $paths;
    }

    /**
     * @param  array<string, string>  $movedFiles
     */
    private function restoreMovedFiles(array $movedFiles): void
    {
        foreach ($movedFiles as $sourcePath => $destinationPath) {
            if (File::isFile($destinationPath) && ! File::exists($sourcePath)) {
                File::move($destinationPath, $sourcePath);
            }
        }
    }
}
