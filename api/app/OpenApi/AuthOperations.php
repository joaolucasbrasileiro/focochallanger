<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/register',
    tags: ['Autenticacao'],
    summary: 'Cadastra um usuário comum sem vínculo com hotéis e emite um token Sanctum',
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/RegisterInput')),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Usuário comum cadastrado.',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'data',
                        properties: [
                            new OA\Property(property: 'user', ref: '#/components/schemas/AuthenticatedUser'),
                            new OA\Property(property: 'access_token', type: 'string'),
                            new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                            new OA\Property(property: 'expires_at', type: 'string', format: 'date-time'),
                        ],
                        type: 'object',
                    ),
                ],
                type: 'object',
            ),
        ),
        new OA\Response(response: 422, description: 'Dados inválidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        new OA\Response(response: 429, description: 'Limite de tentativas excedido.', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
    ],
)]
#[OA\Post(
    path: '/auth/login',
    tags: ['Autenticacao'],
    summary: 'Autentica um usuário e emite um token Sanctum',
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/LoginInput')),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Usuário autenticado.',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'data',
                        properties: [
                            new OA\Property(property: 'user', ref: '#/components/schemas/AuthenticatedUser'),
                            new OA\Property(property: 'access_token', type: 'string'),
                            new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                            new OA\Property(property: 'expires_at', type: 'string', format: 'date-time'),
                        ],
                        type: 'object',
                    ),
                ],
                type: 'object',
            ),
        ),
        new OA\Response(response: 422, description: 'Credenciais ou dados inválidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        new OA\Response(response: 429, description: 'Limite de tentativas excedido.', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
    ],
)]
#[OA\Get(
    path: '/auth/me',
    security: [['sanctum' => []]],
    tags: ['Autenticacao'],
    summary: 'Consulta o usuário autenticado',
    responses: [
        new OA\Response(response: 200, description: 'Usuário autenticado.', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/AuthenticatedUser')], type: 'object')),
        new OA\Response(response: 401, description: 'Token ausente ou inválido.', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
    ],
)]
#[OA\Delete(
    path: '/auth/logout',
    security: [['sanctum' => []]],
    tags: ['Autenticacao'],
    summary: 'Revoga o token utilizado na requisição',
    responses: [
        new OA\Response(response: 204, description: 'Token revogado.'),
        new OA\Response(response: 401, description: 'Token ausente ou inválido.', content: new OA\JsonContent(ref: '#/components/schemas/MessageError')),
    ],
)]
class AuthOperations {}
