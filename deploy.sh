#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="${DEPLOY_ENV_FILE:-$SCRIPT_DIR/deploy.env}"

if [[ -f "$ENV_FILE" ]]; then
  while IFS= read -r line || [[ -n "$line" ]]; do
    [[ -z "$line" ]] && continue
    [[ "$line" =~ ^[[:space:]]*# ]] && continue

    if [[ "$line" =~ ^[[:space:]]*([A-Za-z_][A-Za-z0-9_]*)=(.*)$ ]]; then
      key="${BASH_REMATCH[1]}"
      value="${BASH_REMATCH[2]}"

      # Remove espacos ao redor e aspas opcionais.
      value="${value#"${value%%[![:space:]]*}"}"
      value="${value%"${value##*[![:space:]]}"}"
      if [[ "$value" =~ ^\".*\"$ ]] || [[ "$value" =~ ^\'.*\'$ ]]; then
        value="${value:1:${#value}-2}"
      fi

      printf -v "$key" '%s' "$value"
    fi
  done < "$ENV_FILE"
fi

required_vars=(DEPLOY_PROTOCOL DEPLOY_HOST DEPLOY_USER DEPLOY_PASSWORD DEPLOY_REMOTE_DIR)
for var in "${required_vars[@]}"; do
  if [[ -z "${!var:-}" ]]; then
    echo "Erro: variavel obrigatoria ausente: $var"
    echo "Preencha o arquivo deploy.env (baseado em deploy.env.example)."
    exit 1
  fi
done

DEPLOY_PORT="${DEPLOY_PORT:-}"
DEPLOY_LOCAL_DIR="${DEPLOY_LOCAL_DIR:-$SCRIPT_DIR}"
DEPLOY_FILES="${DEPLOY_FILES:-formulario_contato.php,processar_formulario.php}"
DEPLOY_PROTOCOL_LOWER="$(printf '%s' "$DEPLOY_PROTOCOL" | tr '[:upper:]' '[:lower:]')"

if [[ ! -d "$DEPLOY_LOCAL_DIR" ]]; then
  echo "Erro: diretorio local nao existe: $DEPLOY_LOCAL_DIR"
  exit 1
fi

DEPLOY_HOST_CLEAN="${DEPLOY_HOST#*://}"
URL="${DEPLOY_PROTOCOL_LOWER}://${DEPLOY_HOST_CLEAN}"
if [[ -n "$DEPLOY_PORT" ]]; then
  URL="${URL}:$DEPLOY_PORT"
fi

SECURITY_CMDS=""
case "$DEPLOY_PROTOCOL_LOWER" in
  sftp)
    SECURITY_CMDS="set sftp:auto-confirm yes;"
    ;;
  ftps)
    SECURITY_CMDS="set ftp:ssl-force true; set ftp:ssl-protect-data true;"
    if [[ "${DEPLOY_VERIFY_CERT:-false}" == "false" ]]; then
      SECURITY_CMDS+=" set ssl:verify-certificate no;"
    fi
    ;;
  ftp)
    SECURITY_CMDS="set ftp:ssl-allow no; set ftp:ssl-force false;"
    ;;
  *)
    echo "Erro: DEPLOY_PROTOCOL invalido ($DEPLOY_PROTOCOL). Use: sftp, ftp ou ftps."
    exit 1
    ;;
esac

DRY_RUN_CMD=""
if [[ "${1:-}" == "--dry-run" ]]; then
  DRY_RUN_CMD="true"
fi

echo "Publicando de: $DEPLOY_LOCAL_DIR"
echo "Para: $URL$DEPLOY_REMOTE_DIR"

auth_user=$(printf '%s' "$DEPLOY_USER" | sed 's/"/\\"/g')
auth_pass=$(printf '%s' "$DEPLOY_PASSWORD" | sed 's/"/\\"/g')

IFS=',' read -r -a files_to_send <<<"$DEPLOY_FILES"
if [[ "${#files_to_send[@]}" -eq 0 ]]; then
  echo "Erro: nenhum arquivo definido em DEPLOY_FILES."
  exit 1
fi

lftp_put_cmds=""
for file_name in "${files_to_send[@]}"; do
  trimmed_file="$(echo "$file_name" | xargs)"
  local_path="$DEPLOY_LOCAL_DIR/$trimmed_file"

  if [[ ! -f "$local_path" ]]; then
    echo "Erro: arquivo nao encontrado: $local_path"
    exit 1
  fi

  if [[ "$DRY_RUN_CMD" == "true" ]]; then
    echo "DRY RUN: enviaria $local_path -> $DEPLOY_REMOTE_DIR/$trimmed_file"
  else
    lftp_put_cmds+="put \"$local_path\" -o \"$trimmed_file\";"
  fi
done

if [[ "$DRY_RUN_CMD" == "true" ]]; then
  echo "Dry run concluido."
  exit 0
fi

if ! command -v lftp >/dev/null 2>&1; then
  echo "Erro: lftp nao encontrado. Instale com: sudo apt install lftp"
  exit 1
fi

lftp -u "$auth_user","$auth_pass" "$URL" -e "
  set cmd:fail-exit yes;
  $SECURITY_CMDS
  mkdir -p \"$DEPLOY_REMOTE_DIR\";
  cd \"$DEPLOY_REMOTE_DIR\";
  $lftp_put_cmds
  bye
"

echo "Deploy concluido."
