# Regras de validação para JSON Schema

`App\Mcp\Mappers\ValidationToSchema` transforma as regras dos inputs em
propriedades do JSON Schema usado pelas ferramentas MCP. A interface continua
retornando `array<string, Type>`, aceito pelo método `schema()` das tools.

O adapter normaliza as regras, determina o tipo e aplica as restrições. Usa
`ValidationRuleParser` do Laravel para interpretar nomes, aliases (`int`, `bool`)
e parâmetros. Aceita regras em strings separadas por `|` ou em arrays, como o
Validator; regras contendo `|` dentro dos parâmetros devem ser passadas em arrays.

| Regras | Schema gerado |
| --- | --- |
| `string`, `integer`, `numeric`, `boolean` | Tipo JSON correspondente |
| `required`, `present` | Campo em `required` no objeto pai |
| `sometimes` | Campo opcional, mesmo quando acompanhado de `required` |
| `nullable` | União do tipo com `null`, exceto quando há `required` |
| `min`, `max`, `between` | Limites numéricos ou de comprimento, conforme o tipo |
| `email` | String com `format: email` |
| `TitleAttribute` | `description` |

Inteiros também satisfazem `numeric`; a combinação mantém o tipo `integer`.
Limites repetidos são intersectados, preservando a restrição mais forte.
Strings com `required` recebem comprimento mínimo de um caractere.

`date`, `exists` e `unique` continuam sendo verificações executadas pelo
Validator. `date` sugere uma string, mas não impõe `format: date-time`, pois o
Laravel aceita outras representações. `exists` e `unique` não determinam o tipo
do campo e não consultam o banco durante a geração do schema. `bail` afeta apenas
a execução da validação.

O schema orienta a representação JSON enviada pelo modelo. Ele não substitui
`UseCaseInput::fromArray()`: o Laravel pode aceitar coerções adicionais, como
strings numéricas, e verifica condições que não estão expressas no schema.

## Regras sem tradução

Regras desconhecidas, closures, tipos ambíguos e campos sem uma regra de tipo
produzem `InvalidArgumentException`, identificando o campo e a regra quando
aplicável. Não há fallback implícito para `string`.

O segundo argumento de `map()` permite substituir o schema completo de um campo:

```php
return $mapper->map(MyUseCaseInput::rules(), [
    'token' => $schema->string()->required()->min(10),
]);
```

Um override deve corresponder a um campo declarado nas regras. Ele substitui
também obrigatoriedade, nulabilidade e descrição; essas informações devem estar
no schema fornecido. A validação original do input permanece ativa.

Arrays, objetos aninhados e wildcards ainda não têm tradução automática. Campos
com notação pontuada ou `*` são rejeitados para evitar gerar propriedades literais
incorretas. Quando necessários, seus schemas devem ser definidos diretamente
na ferramenta.
