#!/bin/bash

# Variáveis de conexão e nomes dos bancos de dados
REMOTE_HOST="193.203.175.34"
REMOTE_USER="u576166589_speedphp_user"
REMOTE_PASS="H[4sZ@U#]a"
DB_NAMES=("u576166589_speedphp") # Nomes dos bancos de dados para backup
TEST_DB_NAMES=("speedphp") # Bancos de teste no Docker

# Função para verificar a conexão com o banco de dados remoto
check_db_connection() {
    mysql -h "$REMOTE_HOST" -u "$REMOTE_USER" -p"$REMOTE_PASS" -e "QUIT" &> /dev/null
    if [ $? -ne 0 ]; then
        echo "Erro: Não foi possível conectar ao banco de dados remoto em $REMOTE_HOST."
        exit 1
    fi
}


echo "Contêineres Docker em execução:"
docker ps --format "table {{.Names}}\t{{.Image}}\t{{.Status}}"

# Adicionar uma linha de espaço
echo ""

# Perguntar ao usuário o nome do contêiner
read -p "Digite o nome do contêiner Docker onde o MySQL está rodando: " CONTAINER_NAME

# Verificar se o nome do contêiner foi inserido
if [ -z "$CONTAINER_NAME" ]; then
    echo "Erro: Nome do contêiner não pode ser vazio!"
    exit 1
fi

# Verificar se o contêiner existe e está em execução
if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
    echo "Erro: O contêiner '$CONTAINER_NAME' não existe ou não está em execução."
    exit 1
fi

# Executar o backup e importação diretamente dentro do contêiner Docker
for i in "${!DB_NAMES[@]}"; do
    DB="${DB_NAMES[$i]}"
    TEST_DB="${TEST_DB_NAMES[$i]}"
    
    echo "Criando backup do banco de dados $DB e importando para o banco de teste $TEST_DB dentro do contêiner Docker $CONTAINER_NAME..."

    # Comandos para executar dentro do contêiner
    docker exec -i "$CONTAINER_NAME" bash -c "
        # Realizar o backup do banco remoto
        mysqldump -h $REMOTE_HOST -u $REMOTE_USER -p'$REMOTE_PASS' --routines --triggers $DB > /$DB.sql

        # Recriar o banco de teste e importar os dados do backup
        mysql -u root -proot -e 'DROP DATABASE IF EXISTS $TEST_DB; CREATE DATABASE $TEST_DB;'
        mysql -u root -proot $TEST_DB < /$DB.sql

        # Remover o arquivo de backup do contêiner após a importação
        rm /$DB.sql
    "

    if [ $? -ne 0 ]; then
        echo "Erro ao importar o banco de dados $DB para o contêiner $CONTAINER_NAME!"
        exit 1
    fi
done

echo "Processo de backup e importação concluído com sucesso!"

