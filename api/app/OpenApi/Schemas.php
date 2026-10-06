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
    schema: 'RegisterInput',
    required: ['name', 'email', 'password', 'password_confirmation', 'device_name'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'João Silva'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'joao@foco.test'),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'password123'),
        new OA\Property(property: 'device_name', type: 'string', example: 'Postman'),
    ],
)]
#[OA\Schema(
    schema: 'LoginInput',
    required: ['email', 'password', 'device_name'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@foco.test'),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
        new OA\Property(property: 'device_name', type: 'string', example: 'Postman'),
    ],
)]
#[OA\Schema(
    schema: 'HotelMembership',
    required: ['id', 'hotel', 'role', 'permissions'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'hotel', ref: '#/components/schemas/HotelSummary'),
        new OA\Property(
            property: 'user',
            type: 'object',
            nullable: true,
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'Maria Silva'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'maria@foco.test'),
            ],
        ),
        new OA\Property(property: 'role', type: 'string', enum: ['admin', 'manager', 'receptionist'], example: 'manager'),
        new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(type: 'string')),
    ],
)]
#[OA\Schema(
    schema: 'AuthenticatedUser',
    required: ['id', 'name', 'email', 'memberships'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Maria Silva'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'maria@foco.test'),
        new OA\Property(property: 'memberships', type: 'array', items: new OA\Items(ref: '#/components/schemas/HotelMembership')),
    ],
)]
#[OA\Schema(
    schema: 'HotelUserInput',
    required: ['email', 'role'],
    properties: [
        new OA\Property(property: 'name', type: 'string', nullable: true, example: 'Maria Silva'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'maria@foco.test'),
        new OA\Property(property: 'password', type: 'string', format: 'password', nullable: true, example: 'password123'),
        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', nullable: true, example: 'password123'),
        new OA\Property(property: 'role', type: 'string', enum: ['admin', 'manager', 'receptionist'], example: 'receptionist'),
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
#[OA\Schema(
    schema: 'ReservationFinancialSummary',
    required: ['expected_amount', 'received_amount', 'outstanding_amount', 'overpaid_amount', 'coverage_percentage', 'status'],
    properties: [
        new OA\Property(property: 'expected_amount', type: 'string', example: '300.00'),
        new OA\Property(property: 'received_amount', type: 'string', example: '100.00'),
        new OA\Property(property: 'outstanding_amount', type: 'string', example: '200.00'),
        new OA\Property(property: 'overpaid_amount', type: 'string', example: '0.00'),
        new OA\Property(property: 'coverage_percentage', type: 'string', example: '33.33'),
        new OA\Property(property: 'status', type: 'string', enum: ['unpaid', 'partially_paid', 'paid', 'overpaid'], example: 'partially_paid'),
    ],
)]
#[OA\Schema(
    schema: 'ReservationPaymentSummary',
    required: ['reservation', 'financial', 'payments'],
    properties: [
        new OA\Property(
            property: 'reservation',
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'external_id', type: 'integer', nullable: true, example: 100),
                new OA\Property(property: 'hotel_id', type: 'integer', example: 1),
                new OA\Property(property: 'room_id', type: 'integer', example: 10),
                new OA\Property(property: 'check_in', type: 'string', format: 'date'),
                new OA\Property(property: 'check_out', type: 'string', format: 'date'),
            ],
        ),
        new OA\Property(property: 'financial', ref: '#/components/schemas/ReservationFinancialSummary'),
        new OA\Property(
            property: 'payments',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'method_code', type: 'string', example: '1'),
                    new OA\Property(property: 'amount', type: 'string', example: '100.00'),
                ],
            ),
        ),
    ],
)]
#[OA\Schema(
    schema: 'HotelRevenueReport',
    required: ['hotel', 'period', 'summary', 'groups'],
    properties: [
        new OA\Property(property: 'hotel', ref: '#/components/schemas/HotelSummary'),
        new OA\Property(
            property: 'period',
            type: 'object',
            properties: [
                new OA\Property(property: 'from', type: 'string', format: 'date'),
                new OA\Property(property: 'to', type: 'string', format: 'date'),
                new OA\Property(property: 'group_by', type: 'string', enum: ['day', 'month', 'quarter', 'semester', 'year']),
            ],
        ),
        new OA\Property(property: 'summary', type: 'object', additionalProperties: new OA\AdditionalProperties),
        new OA\Property(
            property: 'groups',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'period', type: 'string', example: '2026-01'),
                    new OA\Property(property: 'lodging_revenue', type: 'string', example: '12500.00'),
                    new OA\Property(property: 'occupied_room_nights', type: 'integer', example: 62),
                    new OA\Property(property: 'reservations_count', type: 'integer', example: 20),
                    new OA\Property(property: 'average_daily_rate', type: 'string', example: '201.61'),
                ],
            ),
        ),
    ],
)]
class Schemas {}
