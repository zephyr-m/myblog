set shell := ["bash", "-uc"]

default:
    @just --list

up:
    ./vendor/bin/sail up -d

down:
    ./vendor/bin/sail down

restart:
    ./vendor/bin/sail restart

ps:
    ./vendor/bin/sail ps

logs:
    ./vendor/bin/sail logs -f

shell:
    ./vendor/bin/sail shell

artisan *args:
    ./vendor/bin/sail artisan {{ args }}

composer *args:
    ./vendor/bin/sail composer {{ args }}

npm *args:
    ./vendor/bin/sail npm {{ args }}

migrate:
    ./vendor/bin/sail artisan migrate

build:
    ./vendor/bin/sail npm run build

test:
    ./vendor/bin/sail composer test

check:
    ./vendor/bin/sail composer ci:check
