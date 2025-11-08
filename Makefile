# Download make para windows
# https://iweb.dl.sourceforge.net/project/gnuwin32/make/3.81/make-3.81.exe
URL="http://localhost/config"

TAG=$(shell git log -1 --format=%h)
CURL := curl -sS
GIT := git
DOCKER_COMPOSE := docker-compose
DOCKER := docker

# Define help como target padrão (quando executar apenas 'make')
.DEFAULT_GOAL := help

.PHONY: help push createmodel createcontroller build backup download start docker update

help:
	@echo ""
	@echo "════════════════════════════════════════════════════════════════"
	@echo "                           MAKEFILE"
	@echo "════════════════════════════════════════════════════════════════"
	@echo ""
	@echo "📋 TARGETS DISPONÍVEIS:"
	@echo ""
	@echo "  make docker                   - Gerencia containers Docker (start/stop/restart)"
	@echo "  make start                    - Executa docker compose + composer + backup + download"
	@echo "  make update                   - Executa composer update no container Docker"
	@echo "  make push MSG=\"mensagem\"    - Commit + pull --rebase + push"
	@echo "  make createmodel              - Cria model e dao baseado no nome da tabela"
	@echo "  make createcontroller         - Cria controller dentro de /src/modules"
	@echo "  make build                    - Executa minificação de JS e CSS do projeto"
	@echo "  make backup                   - Executa backup/import do banco de produção"
	@echo "  make download                 - Baixa arquivos de uploads do servidor remoto"
	@echo "  make help                     - Exibe esta mensagem de ajuda"
	@echo ""
	@echo "════════════════════════════════════════════════════════════════"
	@echo ""
	@echo "💡 EXEMPLOS DE USO:"
	@echo ""
	@echo "  make docker                          # Gerenciar containers"
	@echo "  make start                           # Configuração inicial completa"
	@echo "  make update                          # Atualizar dependências do Composer"
	@echo "  make push MSG=\"Corrigido bug no login\""
	@echo "  make createmodel"
	@echo "  make createcontroller"
	@echo ""
	@echo "════════════════════════════════════════════════════════════════"
	@echo ""

docker:
	@echo ""
	@echo "════════════════════════════════════════════════════════════════"
	@echo "🐳 GERENCIAMENTO DOCKER COMPOSE"
	@echo "════════════════════════════════════════════════════════════════"
	@echo ""
	@echo "Status atual dos containers:"
	@echo "────────────────────────────────────────────────────────────────"
	@docker ps --format "table {{.Names}}\t{{.Image}}\t{{.Status}}"
	@echo "────────────────────────────────────────────────────────────────"
	@echo ""
	@echo "Escolha uma ação:"
	@echo ""
	@echo "  1. ▶️  Iniciar containers (docker-compose up -d)"
	@echo "  2. ⏸️  Parar containers (docker-compose stop)"
	@echo "  3. 🔄  Reiniciar containers (docker-compose restart)"
	@echo "  4. ⏹️  Parar e remover containers (docker-compose down)"
	@echo "  5. 🔨  Rebuild e iniciar (docker-compose up -d --build)"
	@echo "  6. 📊  Ver logs (docker-compose logs -f)"
	@echo "  0. ❌  Cancelar"
	@echo ""
	@read -p "Digite o número da opção: " option; \
	case $$option in \
		1) \
			echo ""; \
			echo "▶️  Iniciando containers..."; \
			echo "────────────────────────────────────────────────────────────────"; \
			$(DOCKER_COMPOSE) up -d && { \
				echo ""; \
				echo "✅ Containers iniciados com sucesso!"; \
				echo ""; \
				echo "📊 Status dos containers:"; \
				echo "────────────────────────────────────────────────────────────────"; \
				$(DOCKER_COMPOSE) ps; \
			} || { \
				echo ""; \
				echo "❌ Erro ao iniciar containers!"; \
				echo ""; \
			} \
			;; \
		2) \
			echo ""; \
			read -p "⚠️  Tem certeza que deseja parar os containers? (s/N): " confirm; \
			if [ "$$confirm" = "s" ] || [ "$$confirm" = "S" ]; then \
				echo ""; \
				echo "⏸️  Parando containers..."; \
				echo "────────────────────────────────────────────────────────────────"; \
				$(DOCKER_COMPOSE) stop && { \
					echo ""; \
					echo "✅ Containers parados com sucesso!"; \
				} || { \
					echo ""; \
					echo "❌ Erro ao parar containers!"; \
				}; \
			else \
				echo ""; \
				echo "❌ Operação cancelada."; \
			fi \
			;; \
		3) \
			echo ""; \
			echo "🔄 Reiniciando containers..."; \
			echo "────────────────────────────────────────────────────────────────"; \
			$(DOCKER_COMPOSE) restart && { \
				echo ""; \
				echo "✅ Containers reiniciados com sucesso!"; \
				echo ""; \
				echo "📊 Status dos containers:"; \
				echo "────────────────────────────────────────────────────────────────"; \
				$(DOCKER_COMPOSE) ps; \
			} || { \
				echo ""; \
				echo "❌ Erro ao reiniciar containers!"; \
			} \
			;; \
		4) \
			echo ""; \
			read -p "⚠️  ATENÇÃO: Isso vai parar E REMOVER os containers! Continuar? (s/N): " confirm; \
			if [ "$$confirm" = "s" ] || [ "$$confirm" = "S" ]; then \
				echo ""; \
				echo "⏹️  Parando e removendo containers..."; \
				echo "────────────────────────────────────────────────────────────────"; \
				$(DOCKER_COMPOSE) down && { \
					echo ""; \
					echo "✅ Containers removidos com sucesso!"; \
				} || { \
					echo ""; \
					echo "❌ Erro ao remover containers!"; \
				}; \
			else \
				echo ""; \
				echo "❌ Operação cancelada."; \
			fi \
			;; \
		5) \
			echo ""; \
			read -p "⚠️  Isso vai rebuildar as imagens. Continuar? (s/N): " confirm; \
			if [ "$$confirm" = "s" ] || [ "$$confirm" = "S" ]; then \
				echo ""; \
				echo "🔨 Rebuildando e iniciando containers..."; \
				echo "────────────────────────────────────────────────────────────────"; \
				$(DOCKER_COMPOSE) up -d --build && { \
					echo ""; \
					echo "✅ Rebuild concluído e containers iniciados!"; \
					echo ""; \
					echo "📊 Status dos containers:"; \
					echo "────────────────────────────────────────────────────────────────"; \
					$(DOCKER_COMPOSE) ps; \
				} || { \
					echo ""; \
					echo "❌ Erro ao rebuildar containers!"; \
				}; \
			else \
				echo ""; \
				echo "❌ Operação cancelada."; \
			fi \
			;; \
		6) \
			echo ""; \
			echo "📊 Exibindo logs (Ctrl+C para sair)..."; \
			echo "────────────────────────────────────────────────────────────────"; \
			echo ""; \
			$(DOCKER_COMPOSE) logs -f \
			;; \
		0) \
			echo ""; \
			echo "❌ Operação cancelada."; \
			echo "" \
			;; \
		*) \
			echo ""; \
			echo "❌ Opção inválida!"; \
			echo "" \
			;; \
	esac; \
	echo ""

update:
	@echo ""
	@echo "════════════════════════════════════════════════════════════════"
	@echo "📦 COMPOSER UPDATE"
	@echo "════════════════════════════════════════════════════════════════"
	@echo ""
	@echo "🐳 Containers Docker disponíveis:"
	@echo "────────────────────────────────────────────────────────────────"
	@$(DOCKER_COMPOSE) ps
	@echo "────────────────────────────────────────────────────────────────"
	@echo ""
	@echo "💡 Dica: Use o nome do SERVICE (Ex: php, app, web)"
	@echo ""
	@read -p "Digite o nome do serviço PHP: " service; \
	if [ -z "$$service" ]; then \
		echo ""; \
		echo "❌ Nome do serviço não pode ser vazio!"; \
		echo ""; \
		exit 1; \
	fi; \
	echo ""; \
	echo "🔍 Verificando se o serviço '$$service' existe..."; \
	if ! $(DOCKER_COMPOSE) ps $$service | grep -q "$$service"; then \
		echo ""; \
		echo "❌ Serviço '$$service' não encontrado!"; \
		echo "💡 Use o nome da coluna SERVICE listado acima."; \
		echo ""; \
		exit 1; \
	fi; \
	echo "✅ Serviço encontrado!"; \
	echo ""; \
	echo "📦 Atualizando dependências do Composer..."; \
	echo "────────────────────────────────────────────────────────────────"; \
	$(DOCKER_COMPOSE) exec $$service composer update || { \
		echo ""; \
		echo "❌ Erro ao executar composer update!"; \
		echo ""; \
		echo "💡 Possíveis causas:"; \
		echo "   1. O container não está rodando"; \
		echo "   2. O Composer não está instalado no container"; \
		echo "   3. Problemas de permissão"; \
		echo "   4. Erro nas dependências do composer.json"; \
		echo ""; \
		echo "🔧 Comandos para debug:"; \
		echo "   docker-compose ps $$service"; \
		echo "   docker-compose exec $$service composer --version"; \
		echo "   docker-compose exec $$service php -v"; \
		echo "   docker-compose exec $$service composer diagnose"; \
		echo ""; \
		exit 1; \
	}; \
	echo ""; \
	echo "════════════════════════════════════════════════════════════════"; \
	echo "✅ Composer update executado com sucesso!"; \
	echo "════════════════════════════════════════════════════════════════"; \
	echo ""

start:
	@echo ""
	@echo "════════════════════════════════════════════════════════════════"
	@echo "⚙️  CONFIGURAÇÃO INICIAL DO PROJETO"
	@echo "════════════════════════════════════════════════════════════════"
	@echo ""
	@echo "Este comando irá executar as seguintes ações:"
	@echo ""
	@echo "  0. 🐳  Iniciar containers Docker (opcional)"
	@echo "  1. 📦  Composer update (dentro do container Docker)"
	@echo "  2. 💾  Backup/import do banco de dados"
	@echo "  3. ⬇️  Download de arquivos de uploads"
	@echo ""
	@echo "════════════════════════════════════════════════════════════════"
	@echo ""
	@read -p "⚠️  Deseja continuar? (s/N): " confirm; \
	if [ "$$confirm" != "s" ] && [ "$$confirm" != "S" ]; then \
		echo ""; \
		echo "❌ Setup cancelado pelo usuário."; \
		echo ""; \
		exit 0; \
	fi; \
	echo ""; \
	echo "════════════════════════════════════════════════════════════════"; \
	echo "🐳 ETAPA 0: DOCKER COMPOSE"; \
	echo "════════════════════════════════════════════════════════════════"; \
	echo ""; \
	echo "Status atual dos containers:"; \
	echo "────────────────────────────────────────────────────────────────"; \
	$(DOCKER_COMPOSE) ps; \
	echo "────────────────────────────────────────────────────────────────"; \
	echo ""; \
	read -p "🐳 Deseja iniciar os containers Docker? (s/N): " start_docker; \
	if [ "$$start_docker" = "s" ] || [ "$$start_docker" = "S" ]; then \
		echo ""; \
		echo "▶️  Iniciando containers Docker..."; \
		echo "────────────────────────────────────────────────────────────────"; \
		$(DOCKER_COMPOSE) up -d || { \
			echo ""; \
			echo "❌ Erro ao iniciar containers!"; \
			echo "💡 Verifique se o docker-compose.yml está correto."; \
			echo ""; \
			exit 1; \
		}; \
		echo ""; \
		echo "✅ Containers iniciados com sucesso!"; \
		echo ""; \
		echo "⏳ Aguardando containers ficarem prontos (5 segundos)..."; \
		sleep 5; \
		echo ""; \
		echo "📊 Status dos containers:"; \
		echo "────────────────────────────────────────────────────────────────"; \
		$(DOCKER_COMPOSE) ps; \
		echo "────────────────────────────────────────────────────────────────"; \
	else \
		echo ""; \
		echo "⏭️  Pulando inicialização dos containers..."; \
	fi; \
	echo ""; \
	echo "════════════════════════════════════════════════════════════════"; \
	echo "📦 ETAPA 1: COMPOSER UPDATE"; \
	echo "════════════════════════════════════════════════════════════════"; \
	echo ""; \
	echo "🐳 Containers Docker disponíveis:"; \
	echo "────────────────────────────────────────────────────────────────"; \
	$(DOCKER_COMPOSE) ps; \
	echo "────────────────────────────────────────────────────────────────"; \
	echo ""; \
	echo "💡 Dica: Use o nome do SERVICE (Ex: php, app, web)"; \
	echo ""; \
	read -p "Digite o nome do serviço PHP: " service; \
	if [ -z "$$service" ]; then \
		echo ""; \
		echo "❌ Nome do serviço não pode ser vazio!"; \
		echo ""; \
		exit 1; \
	fi; \
	echo ""; \
	echo "🔍 Verificando se o serviço '$$service' existe..."; \
	if ! $(DOCKER_COMPOSE) ps $$service | grep -q "$$service"; then \
		echo ""; \
		echo "❌ Serviço '$$service' não encontrado!"; \
		echo "💡 Use o nome da coluna SERVICE listado acima."; \
		echo ""; \
		exit 1; \
	fi; \
	echo "✅ Serviço encontrado!"; \
	echo ""; \
	echo "📦 Atualizando dependências do Composer..."; \
	echo "────────────────────────────────────────────────────────────────"; \
	$(DOCKER_COMPOSE) exec $$service composer update || { \
		echo ""; \
		echo "❌ Erro ao executar composer update!"; \
		echo "💡 Possíveis causas:"; \
		echo "   1. O container não está rodando"; \
		echo "   2. O Composer não está instalado no container"; \
		echo "   3. Problemas de permissão"; \
		echo ""; \
		echo "🔧 Comandos para debug:"; \
		echo "   docker-compose ps $$service"; \
		echo "   docker-compose exec $$service composer --version"; \
		echo "   docker-compose exec $$service php -v"; \
		echo ""; \
		exit 1; \
	}; \
	echo ""; \
	echo "✅ Composer update concluído!"; \
	echo ""; \
	echo "════════════════════════════════════════════════════════════════"; \
	echo "💾 ETAPA 2: BACKUP DO BANCO DE DADOS"; \
	echo "════════════════════════════════════════════════════════════════"; \
	echo ""; \
	echo "🔄 Executando backup/import do banco..."; \
	echo "────────────────────────────────────────────────────────────────"; \
	bash docker/import-database.sh || { \
		echo ""; \
		echo "❌ Erro ao executar backup!"; \
		echo ""; \
		exit 1; \
	}; \
	echo ""; \
	echo "✅ Backup concluído!"; \
	echo ""; \
	echo "════════════════════════════════════════════════════════════════"; \
	echo "⬇️  ETAPA 3: DOWNLOAD DE UPLOADS"; \
	echo "════════════════════════════════════════════════════════════════"; \
	echo ""; \
	echo "🔄 Baixando arquivos de uploads do servidor..."; \
	echo "────────────────────────────────────────────────────────────────"; \
	bash docker/get-uploads.sh || { \
		echo ""; \
		echo "❌ Erro ao executar download!"; \
		echo ""; \
		exit 1; \
	}; \
	echo ""; \
	echo "✅ Download concluído!"; \
	echo ""; \
	echo "════════════════════════════════════════════════════════════════"; \
	echo "🎉 SETUP COMPLETO! PROJETO PRONTO PARA USO"; \
	echo "════════════════════════════════════════════════════════════════"; \
	echo ""; \
	echo "📋 Resumo do que foi executado:"; \
	echo ""; \
	if [ "$$start_docker" = "s" ] || [ "$$start_docker" = "S" ]; then \
		echo "  ✅ Containers Docker iniciados"; \
	else \
		echo "  ⏭️  Containers Docker não foram iniciados"; \
	fi; \
	echo "  ✅ Dependências do Composer atualizadas"; \
	echo "  ✅ Banco de dados importado"; \
	echo "  ✅ Arquivos de upload baixados"; \
	echo ""; \
	echo "🌐 Acesse: $(URL)"; \
	echo ""; \
	echo "════════════════════════════════════════════════════════════════"; \
	echo ""

push:
	@echo "🚀 Executando push..."
	@if ! $(GIT) diff --quiet --ignore-submodules --; then \
		$(GIT) add -A && \
		$(GIT) commit -m "$${MSG:-Auto commit $(TAG)}" || { echo "❌ Commit falhou ou nada a commitar"; exit 1; } && \
		echo "⬇️  Pulling com rebase..." && \
		$(GIT) pull --rebase origin main && \
		echo "⬆️  Pushing para origin..." && \
		$(GIT) push origin main && \
		echo "✅ Push realizado com sucesso!"; \
	else \
		echo "ℹ️  Nada para commitar."; \
	fi

createmodel:
	@echo ""
	@echo "📝 CRIAR MODEL E DAO"
	@echo "════════════════════════════════════════════════════════════════"
	@read -p "Digite o nome da tabela do banco (Ex: PESSOA): " servico; \
	echo ""; \
	echo "🔄 Criando model para a tabela: $$servico"; \
	$(CURL) "$(URL)/createmodel/$$servico"; \
	echo ""; \
	echo "✅ Model criado com sucesso!"
	@echo ""

createcontroller:
	@echo ""
	@echo "🎮 CRIAR CONTROLLER"
	@echo "════════════════════════════════════════════════════════════════"
	@read -p "Digite o caminho do controller (Ex: sistemas/eventos/teste): " servico; \
	echo ""; \
	echo "🔄 Criando controller: $$servico"; \
	$(CURL) "$(URL)/createcontroller/$$servico"; \
	echo ""; \
	echo "✅ Controller criado com sucesso!"
	@echo ""

build:
	@echo ""
	@echo "🔨 EXECUTANDO BUILD"
	@echo "════════════════════════════════════════════════════════════════"
	@echo "🔄 Minificando JS e CSS..."
	@$(CURL) "$(URL)/build"
	@echo ""
	@echo "✅ Build executado com sucesso!"
	@echo ""

backup:
	@echo ""
	@echo "💾 BACKUP E IMPORTAÇÃO DO BANCO"
	@echo "════════════════════════════════════════════════════════════════"
	@echo "🔄 Iniciando processo de backup..."
	@bash docker/import-database.sh
	@echo ""
	@echo "✅ Backup executado com sucesso!"
	@echo ""

download:
	@echo ""
	@echo "⬇️  DOWNLOAD DE UPLOADS"
	@echo "════════════════════════════════════════════════════════════════"
	@echo "🔄 Baixando arquivos do servidor remoto..."
	@bash docker/get-uploads.sh
	@echo ""
	@echo "✅ Download executado com sucesso!"
	@echo ""

# Captura comandos não reconhecidos e exibe o help
%:
	@echo ""
	@echo "❌ Comando '$@' não reconhecido!"
	@echo ""
	@$(MAKE) help