# Foco Hotel API

API REST desenvolvida em Laravel para importar dados de hotelaria por XML, gerenciar quartos, consultar disponibilidade e criar reservas.

O ambiente utiliza Docker para executar a API, o MySQL e o CRON. Os endpoints sao versionados em `/api/v1`, respondem em JSON e possuem documentacao OpenAPI 3.0.0.

## Tecnologias

- PHP 8.4 com Apache;
- Laravel 13 e Laravel Sanctum;
- MySQL 8.4;
- PHPUnit;
- Swagger/OpenAPI 3.0.0;
- Docker Compose.

## Pre-requisitos

- Git;
- Docker Desktop ou Docker Engine;
- Docker Compose v2.

Nao e necessario instalar PHP, Composer, Apache, MySQL ou CRON diretamente na maquina.

## Configuracao inicial

Execute os comandos a partir da raiz do projeto.

### 1. Crie os arquivos de ambiente

```bash
cp api/.env.example api/.env
cp .env.example .env
```

O `api/.env` pertence ao Laravel e e obrigatorio. O `.env` da raiz pertence ao Docker Compose e e opcional, pois possui valores padrao:

```dotenv
APP_PORT=8080
IMPORT_CRON_SCHEDULE="*/5 * * * *"
```

### 2. Suba o banco e a API

```bash
docker compose up -d --build db app
```

### 3. Gere a chave do Laravel

Execute somente na primeira configuracao:

```bash
docker compose exec -T app php artisan key:generate --force
```

### 4. Execute as migrations

```bash
docker compose exec -T app php artisan migrate --force --no-interaction
```

As migrations sao executadas manualmente. O comando aplica somente migrations pendentes e nao apaga os dados existentes.

### 5. Prepare e importe os XMLs iniciais

Os XMLs oficiais sao versionados na raiz do projeto. Antes da importacao, copie-os para `imports/incoming`, que funciona como caixa de entrada operacional:

```bash
cp hotels.xml imports/incoming/hotels.xml
cp rooms.xml imports/incoming/rooms.xml
cp reserves.xml imports/incoming/reserves.xml
```

Depois, execute a importacao:

```bash
docker compose exec -T app php artisan imports:run
```

A reserva externa `6` possui uma diaria inconsistente e sera registrada em `import_issues`; os demais dados validos serao importados.

### 6. Crie a primeira conta administradora

O cadastro publico nao permite escolher o papel `admin`. Depois de importar os hoteis, crie a primeira conta administradora pelo comando abaixo. O numero `1` representa o ID interno do hotel ao qual o administrador sera vinculado:

```bash
docker compose exec app php artisan users:create-admin 1 admin@foco.test --name="Administrador"
```

A senha sera solicitada de forma segura no terminal.

### 7. Inicie o CRON

```bash
docker compose up -d cron
```

### 8. Verifique o ambiente

```bash
docker compose ps
```

Os servicos `app`, `db` e `cron` devem aparecer em execucao, e o banco deve aparecer como `healthy`.

Depois, abra no navegador:

```text
http://localhost:8080/api/v1/hotels
```

O endereco testa o endpoint publico de hoteis e deve retornar uma resposta JSON. Se `APP_PORT` foi alterada no `.env`, substitua `8080` pela porta configurada.

Opcionalmente, o mesmo teste pode ser feito pelo terminal:

```bash
# macOS ou Linux
curl -H 'Accept: application/json' http://localhost:8080/api/v1/hotels

# Windows PowerShell
curl.exe -H "Accept: application/json" http://localhost:8080/api/v1/hotels
```

Servicos executados:

| Servico | Finalidade |
| --- | --- |
| `app` | API Laravel executada pelo Apache. |
| `db` | Banco MySQL persistido no volume `db_data`. |
| `cron` | Importacao XML agendada. |

## Importacao XML

Cada lote deve possuir os arquivos:

```text
imports/incoming/hotels.xml
imports/incoming/rooms.xml
imports/incoming/reserves.xml
```

A pasta `imports` e compartilhada com os containers. Novos arquivos podem ser adicionados sem reiniciar a aplicacao.

Executar manualmente:

```bash
docker compose exec -T app php artisan imports:run
```

O importador atualiza registros pelo `external_id`, persiste as reservas validas e registra reservas rejeitadas em `import_issues`. Uma reserva invalida nao entra em `reservations` e nao afeta a disponibilidade.

Status possiveis de `import_runs`:

| Status | Significado |
| --- | --- |
| `completed` | Lote importado sem pendencias. |
| `completed_with_issues` | Lote concluido com reservas rejeitadas. |
| `failed` | Falha estrutural ou lote incompleto. |

Depois do processamento, o lote concluido e movido para:

```text
imports/archive/YYYY-MM-DD/run-{import_run_id}/
```

## Configuracao do CRON

O CRON roda dentro do container `cron`; nao e necessario configurar o sistema operacional da maquina.

A frequencia e definida por `IMPORT_CRON_SCHEDULE` no `.env` da raiz. Sem essa variavel, o sistema utiliza o padrao de uma execucao a cada cinco minutos:

```dotenv
IMPORT_CRON_SCHEDULE="*/5 * * * *"
```

Uma expressao CRON possui cinco campos, nesta ordem:

```text
minuto hora dia-do-mes mes dia-da-semana
```

| Frequencia | Expressao |
| --- | --- |
| A cada 5 minutos | `*/5 * * * *` |
| A cada 30 minutos | `*/30 * * * *` |
| A cada hora | `0 * * * *` |
| Todos os dias as 02:00 | `0 2 * * *` |
| Toda segunda-feira as 08:30 | `30 8 * * 1` |
| No primeiro dia do mes a meia-noite | `0 0 1 * *` |

Depois de salvar a nova expressao no `.env`, recrie somente o container do CRON para aplicar a configuracao:

```bash
docker compose up -d --force-recreate cron
```

O agendador utiliza `flock` para impedir duas importacoes simultaneas.

## Autenticacao e permissoes

A API utiliza tokens Bearer do Laravel Sanctum.

### Primeiro administrador

A primeira conta administradora deve ser criada pelo comando Artisan apresentado na [configuracao inicial](#6-crie-a-primeira-conta-administradora). Depois disso, esse administrador pode vincular outros usuarios aos hoteis por meio da API.

Login:

```bash
curl -X POST http://localhost:8080/api/v1/auth/login \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"email":"admin@foco.test","password":"sua-senha","device_name":"Postman"}'
```

Utilize `data.access_token` nas rotas protegidas:

```bash
export TOKEN='SEU_TOKEN'
```

Papeis disponiveis por hotel:

| Papel | Acesso principal |
| --- | --- |
| `admin` | Todas as permissoes e gestao de usuarios. |
| `manager` | Quartos, reservas, importacoes e relatorios. |
| `receptionist` | Consulta de quartos, reservas e pagamentos. |

`POST /api/v1/register` cria um usuario sem vinculo com hotel. Um administrador deve posteriormente definir seu hotel e papel.

## Utilizacao da API

### Rotas publicas

```text
POST /api/v1/register
POST /api/v1/auth/login
GET  /api/v1/hotels
GET  /api/v1/hotels/{hotel}/availability
```

### Consultar disponibilidade

```bash
curl 'http://localhost:8080/api/v1/hotels/1/availability?check_in=2026-11-10&check_out=2026-11-12' \
  -H 'Accept: application/json'
```

A resposta agrupa quartos ativos pelo nome e informa `total_units` e `available_units`.

### CRUD de quartos

| Metodo | Endpoint | Operacao |
| --- | --- | --- |
| `GET` | `/api/v1/rooms` | Listar quartos. |
| `POST` | `/api/v1/rooms` | Cadastrar quarto. |
| `GET` | `/api/v1/rooms/{room}` | Consultar quarto. |
| `PUT/PATCH` | `/api/v1/rooms/{room}` | Atualizar quarto. |
| `DELETE` | `/api/v1/rooms/{room}` | Excluir quarto sem reservas. |

Cadastrar um quarto:

```bash
curl -X POST http://localhost:8080/api/v1/rooms \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"hotel_id":1,"name":"Standard Casal","is_active":true}'
```

As URLs utilizam o ID interno do banco. O `external_id` identifica dados originados dos XMLs.

### Criar uma reserva

```bash
curl -X POST http://localhost:8080/api/v1/reservations \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "hotel_id": 1,
    "room_name": "Standard Casal",
    "check_in": "2026-11-10",
    "check_out": "2026-11-12",
    "guests": [
      {"first_name":"Maria","last_name":"Souza","phone":"5571999999999"}
    ],
    "dailies": [
      {"daily_date":"2026-11-10","amount":"200.00"},
      {"daily_date":"2026-11-11","amount":"200.00"}
    ]
  }'
```

As diarias devem cobrir todo o periodo. O total e calculado pela soma das diarias, e a API seleciona uma unidade disponivel.

### Outros endpoints

| Metodo | Endpoint | Finalidade |
| --- | --- | --- |
| `GET` | `/api/v1/reservations/{reservation}/payments` | Pagamentos e saldo da reserva. |
| `POST` | `/api/v1/reservations/{reservation}/payments` | Registrar pagamento manual. |
| `GET` | `/api/v1/hotels/{hotel}/revenue-reports` | Relatorio financeiro. |
| `GET` | `/api/v1/import-runs` | Execucoes de importacao. |
| `GET` | `/api/v1/import-issues` | Pendencias de importacao. |
| Varios | `/api/v1/hotels/{hotel}/users` | Usuarios e papeis do hotel. |

Os parametros e respostas completos estao no Swagger.

Registrar pagamento manual:

```bash
curl -X POST http://localhost:8080/api/v1/reservations/1/payments \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"method_code":"1","amount":"150.00"}'
```

A resposta retorna os pagamentos da reserva e o resumo financeiro atualizado.

## Swagger/OpenAPI

```text
http://localhost:8080/api/documentation
```

Para testar rotas protegidas, clique em `Authorize` e informe o token retornado pelo login.

Regenerar a documentacao:

```bash
docker compose exec -T app php artisan l5-swagger:generate
```

## Banco de dados

As migrations versionam a estrutura do banco. Relacionamentos principais:

```text
hotels -> rooms -> reservations
reservations -> guests, dailies e payments
import_runs -> import_issues
users <-> hotels por hotel_memberships
```

Conexao local pelo MySQL Workbench:

| Campo | Valor |
| --- | --- |
| Host | `127.0.0.1` |
| Porta | `3306` |
| Database | `foco_hotel` |
| Usuario | `foco` |
| Senha | `foco` |

## Testes

```bash
docker compose exec -T app php artisan test
docker compose exec -T app ./vendor/bin/pint --test
```

## Logs e comandos uteis

```bash
# Logs da aplicacao e do CRON
tail -f api/storage/logs/laravel.log
tail -f api/storage/logs/import-cron.log
docker compose logs --tail=100 cron

# Status das migrations
docker compose exec -T app php artisan migrate:status

# Subir ou parar o ambiente
docker compose up -d --build
docker compose down
```

Ao atualizar para uma versao com migrations novas, pare o CRON, atualize a API, aplique `php artisan migrate` e inicie o CRON novamente. Em ambientes com dados importantes, realize backup antes da migracao.

`docker compose down` preserva os volumes. `docker compose down -v` tambem remove os volumes e apaga o banco de dados.
