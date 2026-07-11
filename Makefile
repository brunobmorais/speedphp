# Makefile melhorado
# Para Windows, baixe make: https://iweb.dl.sourceforge.net/project/gnuwin32/make/3.81/make-3.81.exe
URL := http://localhost/config

TAG := $(shell git rev-parse --short HEAD)
CURL := curl -sS
GIT := git
DOCKER_COMPOSE := docker-compose
DOCKER := docker

# Define help como target padrão (quando executar apenas 'make')
.DEFAULT_GOAL := help

.PHONY: help push createmodel createcontroller build backup download start docker update up

help:
	@echo ""
	@echo "════════════════════════════════════════════════════════════════"
	@echo "                           MAKEFILE"
	@echo "════════════════════════════════════════════════════════════════"
	@echo ""
	@echo "📋 TARGETS DISPONÍVEIS:"
	@echo ""
	@echo "  make up                       - Para todos os containers e sobe com --force-recreate"
	@echo "  make docker                   - Gerencia containers Docker (start/stop/restart)"
	@echo "  make start                    - Menu interativo: Docker, config, composer, backup, uploads"
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
	@echo "🐳 Serviços disponíveis no docker-compose:"
	@echo "────────────────────────────────────────────────────────────────"
	@$(DOCKER_COMPOSE) ps --services
	@echo "────────────────────────────────────────────────────────────────"
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
	if ! $(DOCKER_COMPOSE) ps --services | grep -q "^$$service$$"; then \
		echo ""; \
		echo "❌ Serviço '$$service' não encontrado!"; \
		echo "💡 Use exatamente um dos nomes listados acima."; \
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
	@while true; do \
		echo ""; \
		echo "════════════════════════════════════════════════════════════════"; \
		echo "⚙️  SETUP DO PROJETO"; \
		echo "════════════════════════════════════════════════════════════════"; \
		echo ""; \
		echo "  1. 🐳  Docker — parar tudo e subir com --force-recreate"; \
		echo "  2. ⚙️  Configuração — copiar developerConfig.php"; \
		echo "  3. 📦  Composer update (dentro do container)"; \
		echo "  4. 💾  Backup/import do banco de dados"; \
		echo "  5. ⬇️  Download de uploads do servidor"; \
		echo "  6. 🔨  Build — minificar JS e CSS"; \
		echo "  0. ❌  Sair"; \
		echo ""; \
		read -p "Escolha uma opção: " opt; \
		echo ""; \
		case $$opt in \
		1) \
			echo "════════════════════════════════════════════════════════════════"; \
			echo "🐳 DOCKER"; \
			echo "════════════════════════════════════════════════════════════════"; \
			echo ""; \
			echo "⏹️  Parando todos os containers em execução..."; \
			if [ -n "$$(docker ps -q)" ]; then \
				docker stop $$(docker ps -q) && echo "✅ Containers parados."; \
			else \
				echo "ℹ️  Nenhum container em execução."; \
			fi; \
			echo ""; \
			echo "▶️  Subindo containers (--force-recreate)..."; \
			echo "────────────────────────────────────────────────────────────────"; \
			$(DOCKER_COMPOSE) up -d --force-recreate && { \
				echo ""; \
				echo "📊 Status dos containers:"; \
				echo "────────────────────────────────────────────────────────────────"; \
				$(DOCKER_COMPOSE) ps; \
				echo ""; \
				echo "✅ Containers prontos!"; \
			} || echo "❌ Erro ao subir containers!"; \
			;; \
		2) \
			echo "════════════════════════════════════════════════════════════════"; \
			echo "⚙️  CONFIGURAÇÃO DE DESENVOLVIMENTO"; \
			echo "════════════════════════════════════════════════════════════════"; \
			echo ""; \
			if [ -f "config/developerConfig.php" ]; then \
				echo "ℹ️  Arquivo config/developerConfig.php já existe."; \
				echo ""; \
				read -p "⚠️  Deseja sobrescrever com o arquivo de exemplo? (s/N): " overwrite; \
				if [ "$$overwrite" = "s" ] || [ "$$overwrite" = "S" ]; then \
					if [ -f "config/developerConfig.example.php" ]; then \
						cp config/developerConfig.php config/developerConfig.php.bak && \
						echo "📋 Backup criado: config/developerConfig.php.bak"; \
						cp config/developerConfig.example.php config/developerConfig.php && \
						echo "✅ Arquivo config/developerConfig.php criado!"; \
					else \
						echo "❌ config/developerConfig.example.php não encontrado!"; \
					fi; \
				else \
					echo "⏭️  Mantendo arquivo existente."; \
				fi; \
			else \
				if [ -f "config/developerConfig.example.php" ]; then \
					cp config/developerConfig.example.php config/developerConfig.php && \
					echo "✅ Arquivo config/developerConfig.php criado!"; \
				else \
					echo "❌ config/developerConfig.example.php não encontrado!"; \
				fi; \
			fi; \
			;; \
		3) \
			echo "════════════════════════════════════════════════════════════════"; \
			echo "📦 COMPOSER UPDATE"; \
			echo "════════════════════════════════════════════════════════════════"; \
			echo ""; \
			echo "Serviços disponíveis:"; \
			echo "────────────────────────────────────────────────────────────────"; \
			$(DOCKER_COMPOSE) ps --services; \
			echo "────────────────────────────────────────────────────────────────"; \
			echo ""; \
			read -p "Digite o nome do serviço PHP: " service; \
			if [ -z "$$service" ]; then \
				echo "❌ Nome do serviço não pode ser vazio!"; \
			elif ! $(DOCKER_COMPOSE) ps --services | grep -q "^$$service$$"; then \
				echo "❌ Serviço '$$service' não encontrado!"; \
			else \
				echo ""; \
				echo "📦 Executando composer update em '$$service'..."; \
				echo "────────────────────────────────────────────────────────────────"; \
				$(DOCKER_COMPOSE) exec $$service composer update && \
					echo "" && echo "✅ Composer update concluído!" || \
					echo "❌ Erro ao executar composer update!"; \
			fi; \
			;; \
		4) \
			echo "════════════════════════════════════════════════════════════════"; \
			echo "💾 BACKUP DO BANCO DE DADOS"; \
			echo "════════════════════════════════════════════════════════════════"; \
			echo ""; \
			echo "🔄 Executando backup/import..."; \
			echo "────────────────────────────────────────────────────────────────"; \
			bash docker/import-database.sh && \
				echo "" && echo "✅ Backup concluído!" || \
				echo "❌ Erro ao executar backup!"; \
			;; \
		5) \
			echo "════════════════════════════════════════════════════════════════"; \
			echo "⬇️  DOWNLOAD DE UPLOADS"; \
			echo "════════════════════════════════════════════════════════════════"; \
			echo ""; \
			echo "🔄 Baixando arquivos do servidor..."; \
			echo "────────────────────────────────────────────────────────────────"; \
			bash docker/get-uploads.sh && \
				echo "" && echo "✅ Download concluído!" || \
				echo "❌ Erro ao executar download!"; \
			;; \
		6) \
			echo "════════════════════════════════════════════════════════════════"; \
			echo "🔨 BUILD — MINIFICAR JS E CSS"; \
			echo "════════════════════════════════════════════════════════════════"; \
			echo ""; \
			echo "🔄 Minificando JS e CSS..."; \
			echo "────────────────────────────────────────────────────────────────"; \
			$(CURL) "$(URL)/build" && \
				echo "" && echo "✅ Build executado com sucesso!" || \
				echo "❌ Erro ao executar build!"; \
			;; \
		0) \
			echo "Saindo."; \
			echo ""; \
			break; \
			;; \
		*) \
			echo "❌ Opção inválida!"; \
			;; \
		esac; \
	done

up:
	@echo ""
	@echo "════════════════════════════════════════════════════════════════"
	@echo "🐳 SUBINDO DOCKER"
	@echo "════════════════════════════════════════════════════════════════"
	@echo ""
	@echo "⏹️  Parando todos os containers em execução..."
	@if [ -n "$$(docker ps -q)" ]; then \
		docker stop $$(docker ps -q) && echo "✅ Containers parados."; \
	else \
		echo "ℹ️  Nenhum container em execução."; \
	fi
	@echo ""
	@echo "▶️  Subindo containers (--force-recreate)..."
	@echo "────────────────────────────────────────────────────────────────"
	@$(DOCKER_COMPOSE) up -d --force-recreate || { \
		echo ""; \
		echo "❌ Erro ao subir containers!"; \
		echo ""; \
		exit 1; \
	}
	@echo ""
	@echo "📊 Status dos containers:"
	@echo "────────────────────────────────────────────────────────────────"
	@$(DOCKER_COMPOSE) ps
	@echo ""
	@echo "✅ Containers prontos!"
	@echo ""

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