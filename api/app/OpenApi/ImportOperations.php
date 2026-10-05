<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/import-runs',
    tags: ['Importacoes'],
    summary: 'Lista as execucoes de importacao XML',
    responses: [
        new OA\Response(
            response: 200,
            description: 'Execucoes encontradas.',
            content: new OA\JsonContent(
                required: ['data', 'links', 'meta'],
                properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ImportRun')),
                    new OA\Property(property: 'links', type: 'object'),
                    new OA\Property(property: 'meta', type: 'object'),
                ],
            ),
        ),
    ],
)]
#[OA\Get(
    path: '/import-runs/{importRun}',
    tags: ['Importacoes'],
    summary: 'Consulta uma execucao de importacao',
    parameters: [
        new OA\Parameter(name: 'importRun', in: 'path', required: true, description: 'Identificador interno da execucao.', schema: new OA\Schema(type: 'integer', example: 1)),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Execucao encontrada.', content: new OA\JsonContent(required: ['data'], properties: [new OA\Property(property: 'data', ref: '#/components/schemas/ImportRun')])),
        new OA\Response(response: 404, description: 'Execucao nao encontrada.', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
    ],
)]
#[OA\Get(
    path: '/import-issues',
    tags: ['Importacoes'],
    summary: 'Lista pendencias de importacao',
    parameters: [
        new OA\Parameter(name: 'import_run_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
        new OA\Parameter(name: 'source', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['hotel', 'room', 'reservation'])),
        new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['incomplete', 'resolved', 'ignored'])),
        new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 15)),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Pendencias encontradas.',
            content: new OA\JsonContent(
                required: ['data', 'links', 'meta'],
                properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ImportIssue')),
                    new OA\Property(property: 'links', type: 'object'),
                    new OA\Property(property: 'meta', type: 'object'),
                ],
            ),
        ),
        new OA\Response(response: 422, description: 'Filtros invalidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
    ],
)]
#[OA\Get(
    path: '/import-issues/{importIssue}',
    tags: ['Importacoes'],
    summary: 'Consulta uma pendencia de importacao',
    parameters: [
        new OA\Parameter(name: 'importIssue', in: 'path', required: true, description: 'Identificador interno da pendencia.', schema: new OA\Schema(type: 'integer', example: 1)),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Pendencia encontrada.', content: new OA\JsonContent(required: ['data'], properties: [new OA\Property(property: 'data', ref: '#/components/schemas/ImportIssue')])),
        new OA\Response(response: 404, description: 'Pendencia nao encontrada.', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
    ],
)]
class ImportOperations {}
