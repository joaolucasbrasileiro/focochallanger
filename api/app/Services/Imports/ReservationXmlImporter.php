<?php

namespace App\Services\Imports;

use App\Exceptions\Imports\XmlImportException;
use App\Models\ImportIssue;
use App\Models\ImportRun;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class ReservationXmlImporter
{
    public function __construct(private readonly ImportIssueRecorder $issueRecorder) {}

    public function import(SimpleXMLElement $document, ImportRun $importRun): ImportResult
    {
        $imported = 0;
        $rejected = 0;

        foreach ($document->Reserve as $reserveNode) {
            $externalIdentifier = $this->externalIdentifier($reserveNode);

            try {
                DB::transaction(function () use ($reserveNode): void {
                    $this->importReservation($reserveNode);
                });

                $imported++;
            } catch (XmlImportException $exception) {
                $this->issueRecorder->record(
                    $importRun,
                    ImportIssue::SourceReservation,
                    $externalIdentifier,
                    $exception,
                    $this->rawPayload($reserveNode),
                );

                $rejected++;
            }
        }

        return new ImportResult($imported, $rejected);
    }

    private function importReservation(SimpleXMLElement $reserveNode): void
    {
        $externalId = $this->numericAttribute($reserveNode, 'id', 'Reserva');
        $hotelExternalId = $this->numericAttribute($reserveNode, 'hotelCode', "Reserva [{$externalId}]");
        $roomExternalId = $this->numericAttribute($reserveNode, 'roomCode', "Reserva [{$externalId}]");
        $room = Room::query()->with('hotel')->where('external_id', $roomExternalId)->first();

        if ($room === null) {
            throw new XmlImportException(
                "A reserva [{$externalId}] referencia o quarto inexistente [{$roomExternalId}].",
                issueCode: 'room_not_found',
            );
        }

        if ($room->hotel->external_id !== $hotelExternalId) {
            throw new XmlImportException(
                "O quarto [{$roomExternalId}] da reserva [{$externalId}] não pertence ao hotel [{$hotelExternalId}].",
                issueCode: 'room_hotel_mismatch',
            );
        }

        $checkIn = $this->dateValue($reserveNode, 'CheckIn', $externalId);
        $checkOut = $this->dateValue($reserveNode, 'CheckOut', $externalId);

        if ($checkOut <= $checkIn) {
            throw new XmlImportException(
                "A data de check-out da reserva [{$externalId}] deve ser posterior à data de check-in.",
                issueCode: 'invalid_stay_period',
            );
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
    }

    private function externalIdentifier(SimpleXMLElement $reserveNode): ?string
    {
        $value = trim((string) $reserveNode['id']);

        return $value === '' ? null : $value;
    }

    private function rawPayload(SimpleXMLElement $reserveNode): ?string
    {
        $payload = $reserveNode->asXML();

        return is_string($payload) ? $payload : null;
    }

    private function importGuests(Reservation $reservation, SimpleXMLElement $reserveNode): void
    {
        foreach ($reserveNode->Guests->Guest ?? [] as $guestNode) {
            $firstName = trim((string) $guestNode->Name);
            $lastName = trim((string) $guestNode->LastName);
            $phone = trim((string) $guestNode->Phone);

            if ($firstName === '' || $lastName === '' || $phone === '') {
                throw new XmlImportException(
                    "A reserva [{$reservation->external_id}] possui um hóspede com dados incompletos.",
                    issueCode: 'incomplete_guest_data',
                );
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
                throw new XmlImportException(
                    "A reserva [{$reservation->external_id}] possui uma diária em [{$dailyDate}] fora do período de hospedagem.",
                    issueCode: 'daily_outside_stay_period',
                );
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
                throw new XmlImportException(
                    "A reserva [{$reservation->external_id}] possui um pagamento sem método.",
                    issueCode: 'missing_payment_method',
                );
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
            throw new XmlImportException(
                "{$context} possui o atributo {$attribute} inválido [{$value}].",
                issueCode: 'invalid_attribute',
            );
        }

        return (int) $value;
    }

    private function dateValue(SimpleXMLElement $node, string $element, int $reservationExternalId): string
    {
        $value = trim((string) $node->{$element});
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = \DateTimeImmutable::getLastErrors();

        if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            throw new XmlImportException(
                "A reserva [{$reservationExternalId}] possui uma data inválida em {$element} [{$value}].",
                issueCode: 'invalid_date',
            );
        }

        return $date->format('Y-m-d');
    }

    private function amountValue(SimpleXMLElement $node, string $element, int $reservationExternalId): string
    {
        $value = trim((string) $node->{$element});

        if ($value === '' || ! is_numeric($value) || (float) $value < 0) {
            throw new XmlImportException(
                "A reserva [{$reservationExternalId}] possui um valor inválido em {$element} [{$value}].",
                issueCode: 'invalid_amount',
            );
        }

        return number_format((float) $value, 2, '.', '');
    }
}
