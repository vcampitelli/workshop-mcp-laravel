#!/bin/sh
set -e

if [ ! -f "./vendor/autoload.php" ]; then
    printf "\e[35mInstalando dependências do composer...\e[0m\n"
    composer run setup
fi

exec "$@"
