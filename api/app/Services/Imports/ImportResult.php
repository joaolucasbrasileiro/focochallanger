<?php

namespace App\Services\Imports;

final class ImportResult
{
    public function __construct(
        public readonly int $imported,
        public readonly int $rejected = 0,
    ) {}
}
