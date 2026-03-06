# Feedback - Curso de Formação de Dirigentes 2026

Aplicação PHP simples para coleta de feedback anônimo, envio por e-mail e painel com gráficos.

## Estrutura
- `formulario_contato.php`: formulário público.
- `processar_formulario.php`: valida, salva no SQLite e envia e-mail.
- `painel.php`: painel protegido por login/senha.
- `sucesso.php`: página de confirmação após envio.
- `db.php`: conexão e schema do banco SQLite.

## Configuração local
1. Copie o arquivo de exemplo:
```bash
cp deploy.env.example deploy.env
```
2. Ajuste as variáveis em `deploy.env`.
3. Gere hash de senha do painel (se necessário):
```bash
php -r 'echo password_hash("SUA_SENHA", PASSWORD_DEFAULT), PHP_EOL;'
```

## Git (primeiro commit)
```bash
git add .
git commit -m "feat: setup inicial do formulário, processamento e painel"
```

## Publicação
Use o fluxo documentado em `DEPLOY.md`.
# feedback
