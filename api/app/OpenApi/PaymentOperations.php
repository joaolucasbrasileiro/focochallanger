<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/reservations/{reservation}/payments',
    security: [['sanctum' => []]],
    tags: ['Pagamentos'],
    summary: 'Consulta os pagamentos e o saldo de uma reserva',
    description: 'O valor recebido corresponde a soma dos pagamentos informados. O XML nao informa a data em que esses pagamentos ocorreram.',
    parameters: [
        new OA\Parameter(name: 'reservation', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Situacao financeira da reserva.',
            content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', ref: '#/components/schemas/ReservationPaymentSummary')],
                type: 'object',
            ),
        ),
        new OA\Response(response: 401, description: 'Token ausente ou invalido.', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
        new OA\Response(response: 403, description: 'Usuario sem acesso ao hotel da reserva.', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
        new OA\Response(response: 404, description: 'Reserva nao encontrada.', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
    ],
)]
class PaymentOperations {}
