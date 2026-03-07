Crie um commit com as alterações atuais seguindo as regras abaixo.

## Passos

1. Rode `git status` e `git diff` para entender o que mudou
2. Escolha os arquivos relevantes e faça o stage (`git add <arquivos>`)
3. Crie o commit

## Regras obrigatórias

- Mensagem em **uma única linha**, sem body e sem rodapé
- Mensagem **sempre em inglês**
- Formato conventional commits: `type(scope?): description`
- Sem `Co-Authored-By` ou qualquer outra linha extra
- Nunca use `git add -A` ou `git add .` — adicione arquivos específicos pelo nome

## Conventional Commits

Formato completo: `type(scope)!: description`
- `scope` é opcional — use quando ajuda a localizar a mudança (ex: `feat(auth)`, `fix(transactions)`)
- `!` indica breaking change (use com cautela)
- `description`: imperativo, minúsculas, sem ponto final (ex: `add`, `fix`, `remove`, não `added`, `fixes`)

| Tipo | Quando usar |
|---|---|
| `feat` | new feature |
| `fix` | bug fix |
| `chore` | config, dependencies, project files |
| `refactor` | refactor without behavior change |
| `style` | formatting, lint |
| `docs` | documentation |
| `test` | tests |
| `perf` | performance improvement |
| `ci` | CI/CD changes |

## Exemplos

```
feat(transactions): add recurring scope dialog for expense editing
fix(auth): redirect to login on 401 response
chore: add eslint cache to gitignore
refactor(categories): extract form modal to domain component
```
