# Foco Hotel API

API para importacao de hoteis, quartos e reservas a partir de arquivos XML. A aplicacao roda em Docker e possui um servico `cron` que executa periodicamente o comando Laravel `imports:run`.

## Importacao XML

Os XMLs aguardando processamento ficam em `imports/incoming`. O diretorio completo `imports` e montado com leitura e escrita nos containers `app` e `cron`; portanto, os arquivos adicionados no host ficam disponiveis no container sem reinicio.

| Arquivo local de entrada | Caminho no container |
| --- | --- |
| `imports/incoming/hotels.xml` | `/var/www/imports/incoming/hotels.xml` |
| `imports/incoming/rooms.xml` | `/var/www/imports/incoming/rooms.xml` |
| `imports/incoming/reserves.xml` | `/var/www/imports/incoming/reserves.xml` |

Cada lote deve conter os tres arquivos, com esses nomes. Eles precisam estar presentes antes da proxima janela do CRON. Um lote com apenas parte dos arquivos e tratado como erro e permanece em `incoming`, para que seja completado ou corrigido.

Para enviar o proximo lote, copie os tres arquivos para essa pasta:

```bash
cp /caminho/do/lote/hotels.xml imports/incoming/hotels.xml
cp /caminho/do/lote/rooms.xml imports/incoming/rooms.xml
cp /caminho/do/lote/reserves.xml imports/incoming/reserves.xml
```

Ao terminar uma importacao com sucesso, os arquivos de entrada sao movidos para um diretorio de auditoria:

```text
imports/archive/YYYY-MM-DD/run-{import_run_id}/
```

Exemplo:

```text
imports/archive/2026-10-05/run-9/
  hotels.xml
  rooms.xml
  reserves.xml
```

O identificador da execucao evita sobrescrever lotes processados no mesmo dia. O conteudo de `imports/archive` nao e versionado pelo Git, pois representa dados operacionais e pode conter dados de hospedes.

O fluxo da importacao e:

1. Sem arquivos em `incoming`: o comando termina com sucesso e nao cria um registro em `import_runs`.
2. Lote incompleto ou XML estruturalmente invalido: a execucao e marcada como `failed` e os arquivos permanecem em `incoming`.
3. Lote valido: a execucao fica `completed`, e os tres arquivos sao arquivados.
4. Reserva invalida em um lote valido: a execucao fica `completed_with_issues`; reservas validas sao persistidas, a reserva defeituosa e registrada em `import_issues`, e os tres arquivos tambem sao arquivados.

Para executar uma importacao manualmente, com os containers ativos, execute a partir da raiz do projeto:

```bash
docker compose exec -T app php artisan imports:run
```

O comando retorna codigo `0` quando nao existe lote pendente ou quando a importacao termina com sucesso, inclusive com pendencias de reservas. Retorna codigo diferente de zero em caso de falha estrutural. As execucoes processadas criam registros em `import_runs`, com status, horarios, contagens e, quando houver erro, a mensagem registrada.

## Pendencias de importacao

Uma reserva invalida nunca e inserida em `reservations` e, por isso, nao interfere na disponibilidade. Ela e guardada em `import_issues`, vinculada a execucao que a recebeu. O registro preserva a origem, o identificador externo, o codigo e a mensagem do erro, alem do trecho XML original.

As pendencias e execucoes tambem podem ser consultadas pela API:

| Metodo | Endpoint | Finalidade |
| --- | --- | --- |
| `GET` | `/api/v1/import-runs` | Lista execucoes e suas contagens de pendencias. |
| `GET` | `/api/v1/import-runs/{importRun}` | Consulta uma execucao especifica. |
| `GET` | `/api/v1/import-issues` | Lista pendencias; aceita filtros como `import_run_id`, `source` e `status`. |
| `GET` | `/api/v1/import-issues/{importIssue}` | Consulta uma pendencia, incluindo o XML original. |

A documentacao interativa esta disponivel em `http://localhost:8080/api/documentation` quando os containers estiverem em execucao.

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
- `/usr/bin/flock -n /tmp/foco-import.lock`: impede duas importacoes simultaneas. Se uma execucao anterior ainda estiver ativa, a nova tentativa e ignorada.
- `/usr/local/bin/run-imports`: carrega as variaveis necessarias, executa o Laravel como `www-data` e registra a saida.
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

Liste os lotes arquivados:

```bash
find imports/archive -maxdepth 3 -type f | sort
```

Em caso de falha:

1. Confirme que os containers estao ativos com `docker compose ps`.
2. Execute manualmente `docker compose exec -T app php artisan imports:run`.
3. Consulte `import_runs`, `import-cron.log` e `docker compose logs cron` para identificar a causa.
4. Corrija o arquivo XML ou a configuracao indicada pelo erro antes da proxima execucao. Os lotes com falha continuam em `imports/incoming`.
