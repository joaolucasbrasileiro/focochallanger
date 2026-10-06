<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Foco Hotel API',
    description: 'API REST para consulta de hoteis, disponibilidade, gerenciamento de acomodacoes, criacao de reservas e auditoria de importacoes XML.',
)]
#[OA\Server(
    url: '/api/v1',
    description: 'API version 1',
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum',
    description: 'Token retornado pelo endpoint de login.',
)]
#[OA\Tag(
    name: 'Autenticacao',
    description: 'Login, consulta do usuário autenticado e encerramento da sessão.',
)]
#[OA\Tag(
    name: 'Hoteis',
    description: 'Consulta de hoteis e disponibilidade de acomodacoes.',
)]
#[OA\Tag(
    name: 'Quartos',
    description: 'Gerenciamento de quartos e acomodacoes.',
)]
#[OA\Tag(
    name: 'Reservas',
    description: 'Criacao de reservas mediante disponibilidade.',
)]
#[OA\Tag(
    name: 'Importacoes',
    description: 'Consulta de execucoes e pendencias geradas na importacao XML.',
)]
#[OA\Tag(
    name: 'Usuarios',
    description: 'Gestão dos usuários e papéis de cada hotel.',
)]
class OpenApiDefinition {}
