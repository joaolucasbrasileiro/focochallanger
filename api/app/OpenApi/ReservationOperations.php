<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/reservations',
    security: [['sanctum' => []]],
    tags: ['Reservas'],
    summary: 'Cria uma reserva',
    description: 'Seleciona uma unidade ativa disponivel para a acomodacao informada. As diarias devem cobrir todos os dias entre check-in e check-out.',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: '#/components/schemas/ReservationInput'),
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Reserva criada.',
            content: new OA\JsonContent(
                required: ['data'],
                properties: [
                    new OA\Property(property: 'data', ref: '#/components/schemas/Reservation'),
                ],
            ),
        ),
        new OA\Response(
            response: 409,
            description: 'Nao ha disponibilidade para a acomodacao no periodo informado.',
            content: new OA\JsonContent(ref: '#/components/schemas/MessageError'),
        ),
        new OA\Response(
            response: 422,
            description: 'Dados invalidos, incluindo datas, hospedes ou diarias inconsistentes.',
            content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'),
        ),
    ],
)]
class ReservationOperations {}
