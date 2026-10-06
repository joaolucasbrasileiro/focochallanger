<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/hotels/{hotel}/users',
    security: [['sanctum' => []]],
    tags: ['Usuarios'],
    summary: 'Lista os usuários vinculados ao hotel',
    parameters: [new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [
        new OA\Response(response: 200, description: 'Usuários encontrados.'),
        new OA\Response(response: 401, description: 'Não autenticado.'),
        new OA\Response(response: 403, description: 'Sem permissão para administrar o hotel.'),
    ],
)]
#[OA\Post(
    path: '/hotels/{hotel}/users',
    security: [['sanctum' => []]],
    tags: ['Usuarios'],
    summary: 'Cria ou vincula um usuário ao hotel',
    parameters: [new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/HotelUserInput')),
    responses: [
        new OA\Response(response: 201, description: 'Usuário vinculado.', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/HotelMembership')], type: 'object')),
        new OA\Response(response: 403, description: 'Sem permissão para administrar o hotel.'),
        new OA\Response(response: 422, description: 'Dados inválidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
    ],
)]
#[OA\Patch(
    path: '/hotels/{hotel}/users/{user}',
    security: [['sanctum' => []]],
    tags: ['Usuarios'],
    summary: 'Altera o papel de um usuário no hotel',
    parameters: [
        new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['role'],
            properties: [new OA\Property(property: 'role', type: 'string', enum: ['admin', 'manager', 'receptionist'])],
        ),
    ),
    responses: [
        new OA\Response(response: 200, description: 'Papel atualizado.'),
        new OA\Response(response: 409, description: 'A operação removeria o último administrador.'),
    ],
)]
#[OA\Delete(
    path: '/hotels/{hotel}/users/{user}',
    security: [['sanctum' => []]],
    tags: ['Usuarios'],
    summary: 'Remove o vínculo de um usuário com o hotel',
    parameters: [
        new OA\Parameter(name: 'hotel', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ],
    responses: [
        new OA\Response(response: 204, description: 'Vínculo removido.'),
        new OA\Response(response: 409, description: 'A operação removeria o último administrador.'),
    ],
)]
class HotelUserOperations {}
