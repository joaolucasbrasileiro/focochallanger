<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/hotels',
    tags: ['Hoteis'],
    summary: 'Lista os hoteis cadastrados',
    parameters: [
        new OA\Parameter(
            name: 'page',
            in: 'query',
            required: false,
            description: 'Pagina da listagem paginada.',
            schema: new OA\Schema(type: 'integer', minimum: 1, default: 1),
        ),
        new OA\Parameter(
            name: 'per_page',
            in: 'query',
            required: false,
            description: 'Quantidade de registros por pagina, limitada a 100.',
            schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 15),
        ),
        new OA\Parameter(
            name: 'name',
            in: 'query',
            required: false,
            description: 'Busca parcial pelo nome do hotel.',
            schema: new OA\Schema(type: 'string', maxLength: 255),
        ),
        new OA\Parameter(
            name: 'external_id',
            in: 'query',
            required: false,
            description: 'Identificador externo importado do XML.',
            schema: new OA\Schema(type: 'integer'),
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Hoteis encontrados.',
            content: new OA\JsonContent(
                required: ['data', 'links', 'meta'],
                properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Hotel')),
                    new OA\Property(property: 'links', type: 'object'),
                    new OA\Property(property: 'meta', type: 'object'),
                ],
            ),
        ),
    ],
)]
#[OA\Get(
    path: '/hotels/{hotel}/availability',
    tags: ['Hoteis'],
    summary: 'Consulta a disponibilidade por acomodacao',
    description: 'Agrupa os quartos ativos do hotel pelo nome e informa a quantidade total e disponivel no periodo.',
    parameters: [
        new OA\Parameter(name: 'hotel', in: 'path', required: true, description: 'Identificador interno do hotel.', schema: new OA\Schema(type: 'integer', example: 1)),
        new OA\Parameter(name: 'check_in', in: 'query', required: true, description: 'Data de entrada no formato AAAA-MM-DD.', schema: new OA\Schema(type: 'string', format: 'date', example: '2026-11-10')),
        new OA\Parameter(name: 'check_out', in: 'query', required: true, description: 'Data de saida no formato AAAA-MM-DD. Deve ser posterior ao check-in.', schema: new OA\Schema(type: 'string', format: 'date', example: '2026-11-12')),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Disponibilidade calculada.',
            content: new OA\JsonContent(
                required: ['data'],
                properties: [
                    new OA\Property(
                        property: 'data',
                        required: ['hotel', 'check_in', 'check_out', 'rooms'],
                        properties: [
                            new OA\Property(property: 'hotel', ref: '#/components/schemas/Hotel'),
                            new OA\Property(property: 'check_in', type: 'string', format: 'date', example: '2026-11-10'),
                            new OA\Property(property: 'check_out', type: 'string', format: 'date', example: '2026-11-12'),
                            new OA\Property(property: 'rooms', type: 'array', items: new OA\Items(ref: '#/components/schemas/RoomAvailability')),
                        ],
                        type: 'object',
                    ),
                ],
            ),
        ),
        new OA\Response(response: 404, description: 'Hotel nao encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
        new OA\Response(response: 422, description: 'Periodo invalido.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
    ],
)]
class HotelOperations {}
