<?php

namespace App\Exceptions\Imports;

use RuntimeException;

class XmlImportException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly ?string $technicalMessage = null,
        private readonly ?string $issueCode = null,
    ) {
        parent::__construct($message);
    }

    public function technicalMessage(): ?string
    {
        return $this->technicalMessage;
    }

    public function issueCode(): ?string
    {
        return $this->issueCode;
    }
}
