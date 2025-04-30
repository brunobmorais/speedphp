# Download make para windows
# https://iweb.dl.sourceforge.net/project/gnuwin32/make/3.81/make-3.81.exe
# Setar como variável de ambiente
# Executar comando: make build | login | tag | push

TAG=$(shell git log -1 --format=%h)
URL="http://viaesporte.localhost/config"

# PEGAR ARGUMENTOS QUANDO PASSAR GIT
ifeq (push,$(firstword $(MAKECMDGOALS)))
  # use the rest as arguments for "run"
  RUN_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
  # ...and turn them into do-nothing targets
  $(eval $(RUN_ARGS):;@:)
endif

push:
	curl ${URL}/build
	git add .
	git commit -m "$(RUN_ARGS)"
	git pull origin main
	git push origin main
#curl http://viaesporte.localhost/config/createmodel/$(filter-out $@,$(MAKECMDGOALS))

createmodel:
	@read -p "Digite o nome da tabela do banco (Ex: PESSOA): " servico; \
	curl ${URL}/createmodel/$$servico
	echo "Executado"

createpage:
	@read -p "Digite o nome do módulo e serviço (Ex: teste/beta): " servico; \
	curl ${URL}/createpage/$$servico
	echo "Executado"

build:
	curl ${URL}/build
	echo "Executado"

backup:
	@echo "Iniciando o processo de backup e importação..."
	@bash docker/import-database.sh
	echo "Executado"
