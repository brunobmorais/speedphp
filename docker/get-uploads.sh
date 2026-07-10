#!/bin/bash

# Configurações do servidor remoto
REMOTE_HOST=""
REMOTE_USER=""
REMOTE_PATH="/home/usuario/app/public/assets/upload/"
LOCAL_PATH="public/assets/upload/"
echo "Pasta local (realpath): $(realpath "$LOCAL_PATH")"

# Verifica se a pasta local existe, senão cria
mkdir -p "$LOCAL_PATH"

# Baixar os arquivos via rsync (somente os novos/modificados)
rsync -avz --progress -e "ssh " ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}/ ${LOCAL_PATH}/

# Verificar sucesso
if [ $? -eq 0 ]; then
    echo "Transferência de uploads concluída com sucesso!"
else
    echo "Erro ao transferir os arquivos de uploads."
    exit 1
fi
