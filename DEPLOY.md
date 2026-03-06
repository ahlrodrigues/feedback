# Publicar no FTP/SFTP

1. Instale o `lftp` (uma vez):
```bash
sudo apt update && sudo apt install -y lftp
```

2. Crie sua configuracao local:
```bash
cd /home/ahlr/Dropbox/Projetos/Feedback
cp deploy.env.example deploy.env
```

3. Edite o arquivo `deploy.env` com host, usuario, senha e pasta remota.

4. Teste sem enviar (dry run):
```bash
./deploy.sh --dry-run
```

5. Envie de fato:
```bash
./deploy.sh
```

## Notas
- Recomendado: `DEPLOY_PROTOCOL=sftp` e `DEPLOY_PORT=22`.
- O arquivo `deploy.env` esta no `.gitignore` para nao versionar senha.
- O script envia somente os arquivos definidos em `DEPLOY_FILES` (padrao: `formulario_contato.php,processar_formulario.php`).
- Para o painel protegido, configure `PAINEL_EMAIL` e `PAINEL_SENHA_HASH` no `deploy.env`.
