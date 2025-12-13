# Workshop: Conversando com sua API em Laravel direto do prompt

## Slides

Clone este repositório e acesse o arquivo [`docs/index.html`](./docs/index.html) em seu navegador.

```shell
git clone --recursive git@github.com:vcampitelli/workshop-mcp-laravel.git
```

## Demo

A demo é baseada no repositório [LaravelDaily/Laravel-Examples-Ecommerce-DB](https://github.com/LaravelDaily/Laravel-Examples-Ecommerce-DB).

Passo-a-passo:

```shell
cd demo
docker compose up -d
cp .env.example .env
docker compose exec api php artisan key:generate
docker compose exec api php artisan migrate --seed
```
