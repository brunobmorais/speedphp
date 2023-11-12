# Download make para windows
# https://iweb.dl.sourceforge.net/project/gnuwin32/make/3.81/make-3.81.exe
# Setar como variável de ambiente
# Executar comando: make build | login | tag | push

TAG=$(shell git log -1 --format=%h)

# PEGAR ARGUMENTOS QUANDO PASSAR GIT
ifeq (push,$(firstword $(MAKECMDGOALS)))
  # use the rest as arguments for "run"
  RUN_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
  # ...and turn them into do-nothing targets
  $(eval $(RUN_ARGS):;@:)
endif

gitadd:
	git add .
gitcommit:
	git commit -m "$(RUN_ARGS)"

gitpull:
	git pull origin master

push: gitadd gitcommit gitpull
	git push origin master