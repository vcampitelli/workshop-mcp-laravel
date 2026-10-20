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
- Scope: `orders:list orders:show`

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

Nas rotas de pedidos, sem token válido a resposta é 401; sem o scope exigido, é 403.
As consultas de produtos, categorias e avaliações são públicas e não exigem token nem scopes.
As rotas da aplicação conservam suas URLs e usam o grupo de middleware de API,
sem sessão ou CSRF. Login e consentimento usam sessão e CSRF.

## Permissões

| Entidade | Scopes |
| --- | --- |
| Pedidos | `orders:list`, `orders:show`, `orders:create`, `orders:update`, `orders:delete`, `orders:update-status` |

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

## Demonstração pelo Artisan

Com a aplicação rodando em `APP_URL` (por padrão, `http://localhost:8000`), execute:

```sh
php artisan oauth:demo
# Se a aplicação estiver no Docker:
docker compose exec api php artisan oauth:demo
```

O comando cria ou reutiliza o cliente público **Assistente de Compras (demo)** e
imprime um link assinado, de uso único, válido por dez minutos. Abra esse link
no navegador. O fluxo passa pelo login existente e pelo consentimento real do
Passport, solicitando `orders:list` e `orders:show`. O parâmetro
`prompt=consent` mantém a tela de consentimento visível em novas demonstrações.

Para escolher outras permissões, repita a opção `--scope`:

```sh
php artisan oauth:demo --scope=orders:list --scope=orders:show
```

O início do fluxo guarda `state` e o verificador PKCE na sessão do navegador.
O callback `/demo/oauth/callback` valida o estado e a expiração e troca o código
por um token real usando o endpoint do Passport internamente, sem fazer uma
requisição HTTP de volta ao servidor de desenvolvimento. A página exibe o
access token e um exemplo de chamada à API; o refresh token não é exibido.
Ao recusar, a página informa a decisão e nenhum token é emitido.

O comando e as rotas da demonstração estão disponíveis somente nos ambientes
`local` e `testing`. As chaves e migrations do Passport devem ter sido preparadas
conforme a seção Preparação. Execute o comando no mesmo ambiente/container da
aplicação, com o mesmo banco, cache e `APP_URL`.


## Organização das ações

Cada ação está em `app/Domains/<Domínio>/<Ação>/`, com o use case e seu
`Output`. Ações com três ou mais parâmetros ou com regras de validação usam um `Input` tipado. O método
`__invoke` recebe apenas os dados necessários; não recebe requests, sessões ou
respostas HTTP. Outputs de erro usam códigos de domínio, convertidos em
respostas HTTP pelas controllers.

Os inputs estendem `UseCaseInput` e expõem `rules()` e `fromArray()`. A fábrica
valida os dados com o Validator do Laravel e delega a conversão dos tipos ao
input concreto. Dados inválidos lançam `ValidationException`: o Laravel retorna
422 para requisições JSON, redireciona formulários com os erros na sessão e o
MCP converte a exceção em erro da ferramenta.

As controllers passam os dados aos inputs e formatam a resposta. A
ferramenta MCP de pedidos chama `ListOrdersUseCase` diretamente, usando o mesmo
filtro da API. As sessões e os redirecionamentos de login/OAuth continuam nos
adaptadores HTTP; a troca de tokens do Passport usa o contrato `TokenIssuer`
e sua implementação em `app/Infrastructure/OAuth`.
