# OAuth2 no workshop

As permissões são scopes de tokens OAuth2, definidos em `config/permissions.php`.
O usuário autoriza o aplicativo na tela de consentimento. Cada rota verifica o
scope correspondente. Para esta demonstração, os scopes autorizam operações
sobre todos os registros; não há papéis ou restrição por proprietário.

## Preparação

Dentro de `demo/`, com PHP 8.5 e Composer disponíveis:

```sh
composer update --with-all-dependencies
php artisan vendor:publish --tag=passport-migrations
php artisan migrate
php artisan passport:keys
php artisan passport:client --public
```

O update atualiza o lock existente e instala o Passport. Informe um nome para o
cliente e a URL de callback do aplicativo que será usado no workshop.
As migrations publicadas devem ser versionadas após a instalação.
Gere as chaves apenas na primeira instalação; elas ficam em `storage/` e são
ignoradas pelo Git. Use o usuário cadastrado pelos seeders para fazer login.

No Docker, execute os comandos com `docker compose exec api` após atualizar as
dependências e construir a imagem. A imagem precisa usar PHP 8.5, conforme
`composer.json`.

## Obter um token

No Postman ou outro cliente OAuth2, selecione **Authorization Code com PKCE**:

- Authorization URL: `http://localhost:8000/oauth/authorize`
- Access Token URL: `http://localhost:8000/oauth/token`
- Client ID: o ID retornado por `passport:client --public`
- Client Secret: vazio para esse cliente público
- Callback URL: a mesma URL cadastrada no cliente
- Code Challenge Method: `S256`
- Scope: `orders:list orders:show products:list reviews:list`

O cliente deve gerar e validar `state`, gerar um `code_verifier` e enviar seu
`code_challenge` S256 na autorização. Após login e consentimento, ele recebe
o código no callback e o troca por um token enviando `code_verifier`.
O Passport também oferece renovação em `/oauth/token` com
`grant_type=refresh_token`.

Use o token nas requisições:

```sh
curl http://localhost:8000/orders \
  -H "Accept: application/json" \
  -H "Authorization: Bearer SEU_ACCESS_TOKEN"
```

Sem token válido, a resposta é 401. Sem o scope exigido, é 403.
As rotas da aplicação conservam suas URLs e usam o grupo de middleware de API,
sem sessão ou CSRF. Login e consentimento usam sessão e CSRF.

## Permissões

| Entidade | Scopes |
| --- | --- |
| Pedidos | `orders:list`, `orders:show`, `orders:create`, `orders:update`, `orders:delete`, `orders:update-status` |
| Produtos | `products:list`, `products:show` |
| Categorias | `product-categories:list`, `product-categories:show` |
| Avaliações | `reviews:list` |

`GET /orders/create` exige `orders:create`; `GET /orders/{order}/edit` exige
`orders:update`. Alterar o status exige exclusivamente `orders:update-status`.
Não são concedidos scopes padrão. Solicite explicitamente os necessários.

A rota MCP `/mcp/orders` exige token com `orders:list`, pois suas ferramentas
consultam pedidos e os usuários e status associados. Ela usa o mesmo Bearer
token; descoberta automática de OAuth por clientes MCP não está configurada.

## Testes

```sh
php artisan test --filter=PublicControllersTest
```

Os testes usam SQLite em memória e tokens simulados pelo Passport. Cobrem 401,
403 e acesso com o scope correto para cada operação, além dos fluxos das
controllers. A emissão e renovação de tokens reais podem ser demonstradas no
cliente OAuth2 acima.
