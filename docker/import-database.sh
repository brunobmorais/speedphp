#!/bin/bash

# Variáveis do banco remoto
REMOTE_HOST=""
REMOTE_USER=""
REMOTE_PASS=''
DB_NAMES=("")
TEST_DB_NAMES=("speedphp")

echo "Contêineres Docker em execução:"
echo ""

CONTAINERS=()
while IFS= read -r line; do
    CONTAINERS+=("$line")
done < <(docker ps --format '{{.Names}}')

if [ ${#CONTAINERS[@]} -eq 0 ]; then
    echo "Nenhum container em execução!"
    exit 1
fi

for i in "${!CONTAINERS[@]}"; do
    IMAGE=$(docker ps --format '{{.Names}}\t{{.Image}}' | grep "^${CONTAINERS[$i]}" | awk -F'\t' '{print $2}')
    echo "  $((i+1)). ${CONTAINERS[$i]}  ($IMAGE)"
done
echo "  0. Sair"
echo ""

read -p "Escolha o número do container MySQL: " opt

if [ "$opt" = "0" ] || [ -z "$opt" ]; then
    echo "Saindo."
    exit 0
fi

if ! [[ "$opt" =~ ^[0-9]+$ ]] || [ "$opt" -lt 1 ] || [ "$opt" -gt "${#CONTAINERS[@]}" ]; then
    echo "Opção inválida!"
    exit 1
fi

CONTAINER_NAME="${CONTAINERS[$((opt-1))]}"
echo "Container selecionado: $CONTAINER_NAME"
echo ""

for i in "${!DB_NAMES[@]}"; do
    DB="${DB_NAMES[$i]}"
    TEST_DB="${TEST_DB_NAMES[$i]}"

    echo ""
    echo "======================================================="
    echo " Processando: $DB → $TEST_DB"
    echo "======================================================="

    docker exec -i "$CONTAINER_NAME" bash -c "
        # -------------------------------------------------------
        # Função para diagnosticar erros no dump
        # -------------------------------------------------------
        diagnosticar_erro() {
            local SQL_FILE=\$1
            local ERRO_MSG=\$2

            echo ''
            echo '======================================================='
            echo ' DIAGNÓSTICO DE ERRO'
            echo '======================================================='

            if [ ! -f \"\$SQL_FILE\" ]; then
                echo 'Arquivo de dump não encontrado: '\$SQL_FILE
                return
            fi

            echo 'Tamanho do arquivo de dump:'
            ls -lh \"\$SQL_FILE\"
            echo ''

            echo 'Primeiras 5 linhas do arquivo (verificando integridade):'
            echo '-------------------------------------------------------'
            head -5 \"\$SQL_FILE\"
            echo '-------------------------------------------------------'
            echo ''

            LINHA_ERRO=\$(echo \"\$ERRO_MSG\" | grep -oE 'at line [0-9]+' | grep -oE '[0-9]+' | head -1)

            if [ ! -z \"\$LINHA_ERRO\" ] && [ \"\$LINHA_ERRO\" -gt 1 ] 2>/dev/null; then
                echo \">> Erro encontrado na linha: \$LINHA_ERRO\"
                echo ''
                echo 'Contexto ao redor da linha com erro (±10 linhas):'
                echo '-------------------------------------------------------'
                awk \"NR>=\$((\$LINHA_ERRO - 10)) && NR<=\$((\$LINHA_ERRO + 10)) {print NR\\\": \\\"\$0}\" \"\$SQL_FILE\"
                echo '-------------------------------------------------------'
                echo ''
            fi

            echo 'Buscando collations incompatíveis (uca1400)...'
            TOTAL_UCA=\$(grep -c 'uca1400' \"\$SQL_FILE\" 2>/dev/null || echo 0)
            echo \"Total de ocorrências encontradas: \$TOTAL_UCA\"
            echo ''

            if [ \"\$TOTAL_UCA\" -gt 0 ]; then
                echo 'Linhas com collation incompatível:'
                echo '-------------------------------------------------------'
                grep -n 'uca1400' \"\$SQL_FILE\"
                echo '-------------------------------------------------------'
                echo ''

                echo 'Tabelas afetadas pela collation incompatível:'
                echo '-------------------------------------------------------'
                grep -n -B5 'uca1400' \"\$SQL_FILE\" | grep -E '(CREATE TABLE|uca1400|^[0-9]+--)'
                echo '-------------------------------------------------------'
                echo ''
            fi

            echo 'Todas as collations encontradas no dump:'
            echo '-------------------------------------------------------'
            grep -oP \"COLLATE=\K\S+|COLLATE \K\S+\" \"\$SQL_FILE\" | sort | uniq -c | sort -rn
            echo '-------------------------------------------------------'
        }

        # -------------------------------------------------------
        # ETAPA 1: Dump direto do host remoto
        # -------------------------------------------------------
        echo ''
        echo '[1/4] Iniciando dump do banco remoto...'

        MYCNF=\$(mktemp)
        cat > \"\$MYCNF\" <<EOF
[client]
password=$REMOTE_PASS
EOF
        chmod 600 \"\$MYCNF\"

        mysqldump --defaults-extra-file=\"\$MYCNF\" \
            -h $REMOTE_HOST \
            -u $REMOTE_USER \
            --default-character-set=utf8mb4 \
            --column-statistics=0 \
            --skip-routines \
            --triggers \
            --single-transaction \
            --quick \
            --lock-tables=false \
            --max_allowed_packet=1G \
            --complete-insert \
            --skip-add-locks \
            --skip-disable-keys \
            $DB > /$DB.sql 2>/tmp/dump_err.log

        DUMP_EXIT=\$?
        rm -f \"\$MYCNF\"

        if [ -s /tmp/dump_err.log ]; then
            echo 'Avisos durante o dump:'
            cat /tmp/dump_err.log
        fi

        if [ \$DUMP_EXIT -ne 0 ]; then
            echo ''
            echo '======================================================='
            echo 'ERRO: Falha ao criar o dump do banco remoto!'
            echo 'Mensagem de erro:'
            cat /tmp/dump_err.log
            echo '======================================================='
            rm -f /tmp/dump_err.log
            exit 1
        fi

        PRIMEIRA_LINHA=\$(head -1 /$DB.sql)
        if echo \"\$PRIMEIRA_LINHA\" | grep -qi 'warning\|error'; then
            echo ''
            echo '======================================================='
            echo 'ERRO: O arquivo .sql foi contaminado com mensagens de erro/warning!'
            echo \"Primeira linha do arquivo: \$PRIMEIRA_LINHA\"
            echo '======================================================='
            exit 1
        fi

        echo 'Dump criado com sucesso!'
        echo 'Tamanho do arquivo:'
        ls -lh /$DB.sql
        rm -f /tmp/dump_err.log

        # -------------------------------------------------------
        # ETAPA 2: Converter collations incompatíveis
        # -------------------------------------------------------
        echo ''
        echo '[2/4] Verificando e convertendo collations incompatíveis...'

        TOTAL_UCA=\$(grep -c 'uca1400' /$DB.sql 2>/dev/null || echo 0)

        if [ \"\$TOTAL_UCA\" -gt 0 ]; then
            echo \"Encontradas \$TOTAL_UCA ocorrências de collation incompatível. Convertendo...\"

            echo 'Tabelas/contextos afetados antes da conversão:'
            grep -n -B5 'uca1400' /$DB.sql | grep -E '(CREATE TABLE|uca1400)'

            sed -i \
                -e 's/utf8mb4_uca1400_ai_ci/utf8mb4_unicode_ci/g' \
                -e 's/utf8mb4_uca1400_as_ci/utf8mb4_unicode_ci/g' \
                -e 's/utf8mb4_uca1400_as_cs/utf8mb4_unicode_ci/g' \
                -e 's/utf8mb4_uca1400_ai_cs/utf8mb4_unicode_ci/g' \
                -e 's/utf8mb3_uca1400_ai_ci/utf8_unicode_ci/g' \
                -e 's/utf8mb3_uca1400_as_ci/utf8_unicode_ci/g' \
                -e 's/utf8mb4_0900_ai_ci/utf8mb4_unicode_ci/g' \
                -e 's/utf8mb4_0900_as_ci/utf8mb4_unicode_ci/g' \
                /$DB.sql

            echo 'Conversão concluída!'
            RESTANTES=\$(grep -c 'uca1400' /$DB.sql 2>/dev/null || echo 0)
            echo \"Ocorrências restantes após conversão: \$RESTANTES\"
        else
            echo 'Nenhuma collation incompatível encontrada. Prosseguindo...'
        fi

        # -------------------------------------------------------
        # ETAPA 3: Recriar banco de teste
        # -------------------------------------------------------
        echo ''
        echo '[3/4] Recriando banco de dados local...'

        mysql -u root -proot 2>/dev/null -e 'DROP DATABASE IF EXISTS $TEST_DB; CREATE DATABASE $TEST_DB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'

        if [ \$? -ne 0 ]; then
            echo 'ERRO: Falha ao recriar o banco de dados $TEST_DB!'
            exit 1
        fi

        echo 'Banco recriado com sucesso!'

        # -------------------------------------------------------
        # ETAPA 4: Importar dump
        # -------------------------------------------------------
        echo ''
        echo '[4/4] Importando dump para o banco local...'

        mysql -u root -proot 2>/tmp/import_err.log \
            --max_allowed_packet=1G \
            $TEST_DB < /$DB.sql

        IMPORT_EXIT=\$?

        if [ \$IMPORT_EXIT -ne 0 ]; then
            IMPORT_OUTPUT=\$(cat /tmp/import_err.log)
            echo ''
            echo '======================================================='
            echo 'ERRO: Falha ao importar o dump!'
            echo 'Mensagem de erro:'
            echo \"\$IMPORT_OUTPUT\"
            echo '======================================================='

            diagnosticar_erro /$DB.sql \"\$IMPORT_OUTPUT\"

            rm -f /tmp/import_err.log /$DB.sql
            exit 1
        fi

        rm -f /tmp/import_err.log

        echo 'Importação concluída com sucesso!'
        echo ''
        echo 'Tabelas importadas:'
        mysql -u root -proot 2>/dev/null -e 'USE $TEST_DB; SHOW TABLES;'

        rm -f /$DB.sql
        echo ''
        echo 'Arquivo temporário removido.'
    "

    if [ $? -ne 0 ]; then
        echo ""
        echo "======================================================="
        echo "ERRO: Falha no processo para o banco $DB!"
        echo "======================================================="
        exit 1
    fi

done

echo ""
echo "======================================================="
echo " Processo de backup e importação concluído com sucesso!"
echo "======================================================="
