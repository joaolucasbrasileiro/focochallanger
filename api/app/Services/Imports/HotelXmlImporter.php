<?php

namespace App\Services\Imports;

use App\Exceptions\Imports\XmlImportException;
use App\Models\Hotel;
use SimpleXMLElement;

class HotelXmlImporter
{
    public function import(SimpleXMLElement $document): int
    {
        $imported = 0;

        foreach ($document->Hotel as $hotelNode) {
            $externalId = $this->externalId($hotelNode, 'Hotel');
            $name = trim((string) $hotelNode->Name);

            if ($name === '') {
                throw new XmlImportException("O hotel [{$externalId}] não possui nome.");
            }

            Hotel::query()->updateOrCreate(
                ['external_id' => $externalId],
                ['name' => $name],
            );

            $imported++;
        }

        return $imported;
    }

    private function externalId(SimpleXMLElement $node, string $element): int
    {
        $externalId = trim((string) $node['id']);

        if ($externalId === '' || ! ctype_digit($externalId) || (int) $externalId < 1) {
            throw new XmlImportException("{$element} possui um id inválido [{$externalId}].");
        }

        return (int) $externalId;
    }
}
