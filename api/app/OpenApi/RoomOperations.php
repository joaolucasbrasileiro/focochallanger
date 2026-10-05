<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/rooms',
    tags: ['Quartos'],
    summary: 'Lista os quartos cadastrados',
    parameters: [
        new OA\Parameter(name: 'page', in: 'query', required: false, description: 'Pagina da listagem paginada.', schema: new OA\Schema(type: 'integer', minimum: 1, default: 1)),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Quartos encontrados.',
            content: new OA\JsonContent(
                required: ['data', 'links', 'meta'],
                properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Room')),
                    new OA\Property(property: 'links', type: 'object'),
                    new OA\Property(property: 'meta', type: 'object'),
                ],
            ),
        ),
    ],
)]
#[OA\Post(
    path: '/rooms',
    tags: ['Quartos'],
    summary: 'Cadastra um quarto',
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/RoomInput')),
    responses: [
        new OA\Response(response: 201, description: 'Quarto cadastrado.', content: new OA\JsonContent(required: ['data'], properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Room')])),
        new OA\Response(response: 422, description: 'Dados invalidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
    ],
)]
#[OA\Get(
    path: '/rooms/{room}',
    tags: ['Quartos'],
    summary: 'Consulta um quarto pelo identificador interno',
    parameters: [
        new OA\Parameter(name: 'room', in: 'path', required: true, description: 'Identificador interno do quarto.', schema: new OA\Schema(type: 'integer', example: 1)),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Quarto encontrado.', content: new OA\JsonContent(required: ['data'], properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Room')])),
        new OA\Response(response: 404, description: 'Quarto nao encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
    ],
)]
#[OA\Put(
    path: '/rooms/{room}',
    tags: ['Quartos'],
    summary: 'Atualiza os dados informados de um quarto',
    parameters: [
        new OA\Parameter(name: 'room', in: 'path', required: true, description: 'Identificador interno do quarto.', schema: new OA\Schema(type: 'integer', example: 1)),
    ],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/RoomUpdateInput')),
    responses: [
        new OA\Response(response: 200, description: 'Quarto atualizado.', content: new OA\JsonContent(required: ['data'], properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Room')])),
        new OA\Response(response: 404, description: 'Quarto nao encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
        new OA\Response(response: 422, description: 'Dados invalidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
    ],
)]
#[OA\Patch(
    path: '/rooms/{room}',
    tags: ['Quartos'],
    summary: 'Atualiza parcialmente um quarto',
    parameters: [
        new OA\Parameter(name: 'room', in: 'path', required: true, description: 'Identificador interno do quarto.', schema: new OA\Schema(type: 'integer', example: 1)),
    ],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/RoomUpdateInput')),
    responses: [
        new OA\Response(response: 200, description: 'Quarto atualizado.', content: new OA\JsonContent(required: ['data'], properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Room')])),
        new OA\Response(response: 404, description: 'Quarto nao encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
        new OA\Response(response: 422, description: 'Dados invalidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
    ],
)]
#[OA\Delete(
    path: '/rooms/{room}',
    tags: ['Quartos'],
    summary: 'Exclui um quarto sem reservas vinculadas',
    parameters: [
        new OA\Parameter(name: 'room', in: 'path', required: true, description: 'Identificador interno do quarto.', schema: new OA\Schema(type: 'integer', example: 1)),
    ],
    responses: [
        new OA\Response(response: 204, description: 'Quarto excluido.'),
        new OA\Response(response: 404, description: 'Quarto nao encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
        new OA\Response(response: 409, description: 'Quarto possui reservas vinculadas.', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
    ],
)]
class RoomOperations {}
