<?php

namespace App\Services\Imports;

use App\Exceptions\Imports\XmlImportException;
use App\Models\Hotel;
use App\Models\Room;
use SimpleXMLElement;

class RoomXmlImporter
{
    public function import(SimpleXMLElement $document): int
    {
        $imported = 0;

        foreach ($document->Room as $roomNode) {
            $externalId = $this->numericAttribute($roomNode, 'id', 'Quarto');
            $hotelExternalId = $this->numericAttribute($roomNode, 'hotelCode', "Quarto [{$externalId}]");
            $name = trim((string) $roomNode->Name);

            if ($name === '') {
                throw new XmlImportException("O quarto [{$externalId}] não possui nome.");
            }

            $hotel = Hotel::query()->where('external_id', $hotelExternalId)->first();

            if ($hotel === null) {
                throw new XmlImportException("O quarto [{$externalId}] referencia o hotel inexistente [{$hotelExternalId}].");
            }

            Room::query()->updateOrCreate(
                ['external_id' => $externalId],
                [
                    'hotel_id' => $hotel->id,
                    'name' => $name,
                ],
            );

            $imported++;
        }

        return $imported;
    }

    private function numericAttribute(SimpleXMLElement $node, string $attribute, string $context): int
    {
        $value = trim((string) $node[$attribute]);

        if ($value === '' || ! ctype_digit($value) || (int) $value < 1) {
            throw new XmlImportException("{$context} possui o atributo {$attribute} inválido [{$value}].");
        }

        return (int) $value;
    }
}
