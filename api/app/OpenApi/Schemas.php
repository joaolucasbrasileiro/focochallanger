<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'HotelSummary',
    required: ['id', 'external_id', 'name'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'external_id', type: 'integer', nullable: true, example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Hotel Foco Prime'),
    ],
)]
#[OA\Schema(
    schema: 'Hotel',
    required: ['id', 'external_id', 'name', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'external_id', type: 'integer', nullable: true, example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Hotel Foco Prime'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-10-05T12:00:00.000000Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-10-05T12:00:00.000000Z'),
    ],
)]
#[OA\Schema(
    schema: 'Room',
    required: ['id', 'external_id', 'hotel_id', 'name', 'is_active', 'hotel', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'external_id', type: 'integer', nullable: true, example: 1),
        new OA\Property(property: 'hotel_id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Standard'),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'hotel', ref: '#/components/schemas/HotelSummary'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-10-05T12:00:00.000000Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-10-05T12:00:00.000000Z'),
    ],
)]
#[OA\Schema(
    schema: 'RoomInput',
    required: ['hotel_id', 'name'],
    properties: [
        new OA\Property(property: 'hotel_id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Standard'),
        new OA\Property(property: 'is_active', type: 'boolean', default: true, example: true),
    ],
)]
#[OA\Schema(
    schema: 'RoomUpdateInput',
    properties: [
        new OA\Property(property: 'hotel_id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Luxo'),
        new OA\Property(property: 'is_active', type: 'boolean', example: false),
    ],
)]
#[OA\Schema(
    schema: 'RoomAvailability',
    required: ['name', 'total_units', 'available_units'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Standard'),
        new OA\Property(property: 'total_units', type: 'integer', example: 10),
        new OA\Property(property: 'available_units', type: 'integer', example: 7),
    ],
)]
#[OA\Schema(
    schema: 'Guest',
    required: ['id', 'first_name', 'last_name', 'phone'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'first_name', type: 'string', example: 'Maria'),
        new OA\Property(property: 'last_name', type: 'string', example: 'Silva'),
        new OA\Property(property: 'phone', type: 'string', example: '71999999999'),
    ],
)]
#[OA\Schema(
    schema: 'ReservationDaily',
    required: ['id', 'daily_date', 'amount'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'daily_date', type: 'string', format: 'date', example: '2026-11-10'),
        new OA\Property(property: 'amount', type: 'string', example: '250.00'),
    ],
)]
#[OA\Schema(
    schema: 'ReservationPayment',
    required: ['id', 'method_code', 'amount'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'method_code', type: 'integer', example: 1),
        new OA\Property(property: 'amount', type: 'string', example: '500.00'),
    ],
)]
#[OA\Schema(
    schema: 'ReservationRoom',
    required: ['id', 'hotel_id', 'name'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'hotel_id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Standard'),
    ],
)]
#[OA\Schema(
    schema: 'Reservation',
    required: ['id', 'external_id', 'room_id', 'check_in', 'check_out', 'total', 'room', 'guests', 'dailies', 'payments', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'external_id', type: 'integer', nullable: true, example: null),
        new OA\Property(property: 'room_id', type: 'integer', example: 1),
        new OA\Property(property: 'check_in', type: 'string', format: 'date', example: '2026-11-10'),
        new OA\Property(property: 'check_out', type: 'string', format: 'date', example: '2026-11-12'),
        new OA\Property(property: 'total', type: 'string', example: '500.00'),
        new OA\Property(property: 'room', ref: '#/components/schemas/ReservationRoom'),
        new OA\Property(property: 'guests', type: 'array', items: new OA\Items(ref: '#/components/schemas/Guest')),
        new OA\Property(property: 'dailies', type: 'array', items: new OA\Items(ref: '#/components/schemas/ReservationDaily')),
        new OA\Property(property: 'payments', type: 'array', items: new OA\Items(ref: '#/components/schemas/ReservationPayment')),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-10-05T12:00:00.000000Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-10-05T12:00:00.000000Z'),
    ],
)]
#[OA\Schema(
    schema: 'ReservationInput',
    required: ['hotel_id', 'room_name', 'check_in', 'check_out', 'guests', 'dailies'],
    properties: [
        new OA\Property(property: 'hotel_id', type: 'integer', example: 1),
        new OA\Property(property: 'room_name', type: 'string', maxLength: 255, example: 'Standard'),
        new OA\Property(property: 'check_in', type: 'string', format: 'date', example: '2026-11-10'),
        new OA\Property(property: 'check_out', type: 'string', format: 'date', example: '2026-11-12'),
        new OA\Property(
            property: 'guests',
            type: 'array',
            minItems: 1,
            items: new OA\Items(
                required: ['first_name', 'last_name', 'phone'],
                properties: [
                    new OA\Property(property: 'first_name', type: 'string', maxLength: 255, example: 'Maria'),
                    new OA\Property(property: 'last_name', type: 'string', maxLength: 255, example: 'Silva'),
                    new OA\Property(property: 'phone', type: 'string', maxLength: 32, example: '71999999999'),
                ],
            ),
        ),
        new OA\Property(
            property: 'dailies',
            type: 'array',
            minItems: 1,
            items: new OA\Items(
                required: ['daily_date', 'amount'],
                properties: [
                    new OA\Property(property: 'daily_date', type: 'string', format: 'date', example: '2026-11-10'),
                    new OA\Property(property: 'amount', type: 'number', format: 'float', minimum: 0.01, example: 250.00),
                ],
            ),
        ),
    ],
)]
#[OA\Schema(
    schema: 'ValidationError',
    required: ['message', 'errors'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Os dados informados sao invalidos.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(type: 'array', items: new OA\Items(type: 'string')),
        ),
    ],
)]
#[OA\Schema(
    schema: 'MessageError',
    required: ['message'],
    properties: [
        new OA\Property(property: 'message', type: 'string'),
    ],
)]
#[OA\Schema(
    schema: 'ImportRun',
    required: [
        'id',
        'status',
        'started_at',
        'finished_at',
        'hotels_imported',
        'rooms_imported',
        'reservations_imported',
        'issues_count',
        'reservation_issues_count',
    ],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'status', type: 'string', enum: ['running', 'completed', 'completed_with_issues', 'failed'], example: 'completed_with_issues'),
        new OA\Property(property: 'started_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'finished_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'hotels_imported', type: 'integer', example: 2),
        new OA\Property(property: 'rooms_imported', type: 'integer', example: 10),
        new OA\Property(property: 'reservations_imported', type: 'integer', example: 8),
        new OA\Property(property: 'issues_count', type: 'integer', example: 2),
        new OA\Property(property: 'reservation_issues_count', type: 'integer', example: 2),
        new OA\Property(property: 'error_message', type: 'string', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ],
)]
#[OA\Schema(
    schema: 'ImportIssue',
    required: ['id', 'import_run_id', 'source', 'status', 'error_code', 'error_message', 'metadata', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'import_run_id', type: 'integer', example: 1),
        new OA\Property(property: 'source', type: 'string', enum: ['hotel', 'room', 'reservation'], example: 'reservation'),
        new OA\Property(property: 'external_identifier', type: 'string', nullable: true, example: '6'),
        new OA\Property(property: 'status', type: 'string', enum: ['incomplete', 'resolved', 'ignored'], example: 'incomplete'),
        new OA\Property(property: 'error_code', type: 'string', example: 'daily_outside_stay_period'),
        new OA\Property(property: 'error_message', type: 'string', example: 'A reserva [6] possui uma diária fora do período de hospedagem.'),
        new OA\Property(property: 'raw_payload', type: 'string', nullable: true, example: '<Reserve id="6">...</Reserve>'),
        new OA\Property(property: 'metadata', type: 'object', additionalProperties: new OA\AdditionalProperties),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ],
)]
class Schemas {}
