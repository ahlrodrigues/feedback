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

## Publicação
```bash
Use o fluxo documentado em `DEPLOY.md`.
```

**GNU General Public License:**
-------------------------------
```
Estes scripts/programas são softwares livres, você pode redistribuí-los e/ou modifica-los
dentro dos termos da Licença Pública Geral GNU:
```
> [General Public License](https://pt.wikipedia.org/wiki/GNU_General_Public_License)
>
>Fundação do Software Livre (FSF) Inc. 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

