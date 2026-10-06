<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/hotels/{hotel}/revenue-reports',
    security: [['sanctum' => []]],
    tags: ['Relatorios'],
    summary: 'Consulta a receita de hospedagem por diaria',
    description: 'A receita usa a data e o valor das diarias. O resumo financeiro considera cada reserva com diarias no intervalo uma unica vez e nao representa fluxo de caixa por data de pagamento.',
    parameters: [
        new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        new OA\Parameter(name: 'from', in: 'query', required: true, schema: new OA\Schema(type: 'string', format: 'date', example: '2026-01-01')),
        new OA\Parameter(name: 'to', in: 'query', required: true, schema: new OA\Schema(type: 'string', format: 'date', example: '2026-12-31')),
        new OA\Parameter(
            name: 'group_by',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', default: 'month', enum: ['day', 'month', 'quarter', 'semester', 'year']),
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Relatorio gerencial do hotel.',
            content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', ref: '#/components/schemas/HotelRevenueReport')],
                type: 'object',
            ),
        ),
        new OA\Response(response: 401, description: 'Token ausente ou invalido.', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
        new OA\Response(response: 403, description: 'Usuario sem acesso aos relatorios do hotel.', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
        new OA\Response(response: 422, description: 'Periodo ou agrupamento invalido.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
    ],
)]
class ReportOperations {}
