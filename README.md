# Foco Hotel API

API para importacao de hoteis, quartos e reservas a partir de arquivos XML. A aplicacao roda em Docker e possui um servico `cron` que executa periodicamente o comando Laravel `imports:run`.

## Importacao XML

Os arquivos de origem ficam na raiz do projeto e sao montados como somente leitura nos containers `app` e `cron`:

| Arquivo local | Caminho no container |
| --- | --- |
| `hotels.xml` | `/var/www/imports/hotels.xml` |
| `rooms.xml` | `/var/www/imports/rooms.xml` |
| `reserves.xml` | `/var/www/imports/reserves.xml` |

Para executar uma importacao manualmente, com os containers ativos, execute a partir da raiz do projeto:

```bash
docker compose exec -T app php artisan imports:run
```

O comando retorna codigo `0` quando a importacao termina com sucesso e codigo diferente de zero quando ocorre uma falha. Cada tentativa cria um registro em `import_runs`, com status, horarios, contagens e, quando houver erro, a mensagem registrada.

## Execucao via CRON

O agendamento e executado pelo servico Docker `cron`. Nenhuma configuracao de CRON e necessaria na maquina host; basta possuir Docker Compose.

Suba todos os servicos, incluindo API, banco e agendador:

```bash
docker compose up -d --build
```

Confirme que o agendador esta em execucao:

```bash
docker compose ps
```

Crie o arquivo `.env` da raiz a partir do exemplo versionado:

```bash
cp .env.example .env
```

Defina a frequencia na variavel `IMPORT_CRON_SCHEDULE`, usando uma expressao CRON de cinco campos:

```dotenv
IMPORT_CRON_SCHEDULE="*/5 * * * *"
```

| Frequencia | Valor |
| --- | --- |
| A cada 5 minutos | `*/5 * * * *` |
| A cada 30 minutos | `*/30 * * * *` |
| A cada hora | `0 * * * *` |
| Todos os dias as 02:00 | `0 2 * * *` |
| Toda segunda-feira as 08:30 | `30 8 * * 1` |

Depois de alterar a variavel, recrie somente o container do agendador:

```bash
docker compose up -d --force-recreate cron
```

O container `cron` inicia com `cron -f`: o daemon permanece em primeiro plano, permitindo que o Docker o acompanhe. Antes disso, `api/docker/cron/entrypoint.sh` le a variavel, valida seus cinco campos e gera a regra em `/etc/cron.d/foco-imports` dentro do container.

- `*/5 * * * *`: frequencia de cinco em cinco minutos.
- `www-data`: executa o comando com o mesmo usuario usado pela aplicacao PHP.
- `/usr/bin/flock -n /tmp/foco-import.lock`: impede duas importacoes simultaneas. Se uma execucao anterior ainda estiver ativa, a nova tentativa e ignorada.
- `cd /var/www/html`: entra na pasta Laravel dentro do container.
- `/usr/local/bin/php artisan imports:run`: executa o comando de importacao.
- `>> ... 2>&1`: grava a saida normal e os erros em `storage/logs/import-cron.log`.

O `.env` da raiz pertence ao Docker Compose e controla a porta da API e o agendamento. Ele e separado de `api/.env`, que pertence ao Laravel e concentra configuracoes da aplicacao, como banco de dados e ambiente. A frequencia e uma configuracao administrativa do deploy, portanto nao existe endpoint publico para altera-la.

O pacote `util-linux`, que fornece `flock`, e instalado na imagem durante o build. O container `cron` nao possui portas publicadas e nao atende requisicoes HTTP.

## Monitoramento e diagnostico

Verifique a saida das execucoes agendadas:

```bash
tail -f api/storage/logs/import-cron.log
```

Verifique se o daemon de CRON permanece em execucao:

```bash
docker compose logs --tail=100 cron
```

Consulte as ultimas execucoes registradas pela aplicacao:

```bash
docker compose exec -T db mysql -ufoco -pfoco foco_hotel -e 'SELECT id, status, started_at, finished_at, hotels_imported, rooms_imported, reservations_imported, error_message FROM import_runs ORDER BY id DESC LIMIT 10;'
```

Em caso de falha:

1. Confirme que os containers estao ativos com `docker compose ps`.
2. Execute manualmente `docker compose exec -T app php artisan imports:run`.
3. Consulte `import_runs`, `import-cron.log` e `docker compose logs cron` para identificar a causa.
4. Corrija o arquivo XML ou a configuracao indicada pelo erro antes da proxima execucao.
