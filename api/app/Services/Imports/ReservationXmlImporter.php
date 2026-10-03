<?php

namespace App\Services\Imports;

use App\Exceptions\Imports\XmlImportException;
use App\Models\Reservation;
use App\Models\Room;
use SimpleXMLElement;

class ReservationXmlImporter
{
    public function import(SimpleXMLElement $document): int
    {
        $imported = 0;

        foreach ($document->Reserve as $reserveNode) {
            $externalId = $this->numericAttribute($reserveNode, 'id', 'Reserva');
            $hotelExternalId = $this->numericAttribute($reserveNode, 'hotelCode', "Reserva [{$externalId}]");
            $roomExternalId = $this->numericAttribute($reserveNode, 'roomCode', "Reserva [{$externalId}]");
            $room = Room::query()->with('hotel')->where('external_id', $roomExternalId)->first();

            if ($room === null) {
                throw new XmlImportException("A reserva [{$externalId}] referencia o quarto inexistente [{$roomExternalId}].");
            }

            if ($room->hotel->external_id !== $hotelExternalId) {
                throw new XmlImportException("O quarto [{$roomExternalId}] da reserva [{$externalId}] não pertence ao hotel [{$hotelExternalId}].");
            }

            $checkIn = $this->dateValue($reserveNode, 'CheckIn', $externalId);
            $checkOut = $this->dateValue($reserveNode, 'CheckOut', $externalId);

            if ($checkOut <= $checkIn) {
                throw new XmlImportException("A data de check-out da reserva [{$externalId}] deve ser posterior à data de check-in.");
            }

            $reservation = Reservation::query()->updateOrCreate(
                ['external_id' => $externalId],
                [
                    'room_id' => $room->id,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'total' => $this->amountValue($reserveNode, 'Total', $externalId),
                ],
            );

            $reservation->guests()->delete();
            $reservation->dailies()->delete();
            $reservation->payments()->delete();

            $this->importGuests($reservation, $reserveNode);
            $this->importDailies($reservation, $reserveNode, $checkIn, $checkOut);
            $this->importPayments($reservation, $reserveNode);

            $imported++;
        }

        return $imported;
    }

    private function importGuests(Reservation $reservation, SimpleXMLElement $reserveNode): void
    {
        foreach ($reserveNode->Guests->Guest ?? [] as $guestNode) {
            $firstName = trim((string) $guestNode->Name);
            $lastName = trim((string) $guestNode->LastName);
            $phone = trim((string) $guestNode->Phone);

            if ($firstName === '' || $lastName === '' || $phone === '') {
                throw new XmlImportException("A reserva [{$reservation->external_id}] possui um hóspede com dados incompletos.");
            }

            $reservation->guests()->create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone,
            ]);
        }
    }

    private function importDailies(Reservation $reservation, SimpleXMLElement $reserveNode, string $checkIn, string $checkOut): void
    {
        foreach ($reserveNode->Dailies->Daily ?? [] as $dailyNode) {
            $dailyDate = $this->dateValue($dailyNode, 'Date', $reservation->external_id);

            if ($dailyDate < $checkIn || $dailyDate >= $checkOut) {
                throw new XmlImportException("A reserva [{$reservation->external_id}] possui uma diária em [{$dailyDate}] fora do período de hospedagem.");
            }

            $reservation->dailies()->create([
                'daily_date' => $dailyDate,
                'amount' => $this->amountValue($dailyNode, 'Value', $reservation->external_id),
            ]);
        }
    }

    private function importPayments(Reservation $reservation, SimpleXMLElement $reserveNode): void
    {
        foreach ($reserveNode->Payments->Payment ?? [] as $paymentNode) {
            $methodCode = trim((string) $paymentNode->Method);

            if ($methodCode === '') {
                throw new XmlImportException("A reserva [{$reservation->external_id}] possui um pagamento sem método.");
            }

            $reservation->payments()->create([
                'method_code' => $methodCode,
                'amount' => $this->amountValue($paymentNode, 'Value', $reservation->external_id),
            ]);
        }
    }

    private function numericAttribute(SimpleXMLElement $node, string $attribute, string $context): int
    {
        $value = trim((string) $node[$attribute]);

        if ($value === '' || ! ctype_digit($value) || (int) $value < 1) {
            throw new XmlImportException("{$context} possui o atributo {$attribute} inválido [{$value}].");
        }

        return (int) $value;
    }

    private function dateValue(SimpleXMLElement $node, string $element, int $reservationExternalId): string
    {
        $value = trim((string) $node->{$element});
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = \DateTimeImmutable::getLastErrors();

        if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            throw new XmlImportException("A reserva [{$reservationExternalId}] possui uma data inválida em {$element} [{$value}].");
        }

        return $date->format('Y-m-d');
    }

    private function amountValue(SimpleXMLElement $node, string $element, int $reservationExternalId): string
    {
        $value = trim((string) $node->{$element});

        if ($value === '' || ! is_numeric($value) || (float) $value < 0) {
            throw new XmlImportException("A reserva [{$reservationExternalId}] possui um valor inválido em {$element} [{$value}].");
        }

        return number_format((float) $value, 2, '.', '');
    }
}
