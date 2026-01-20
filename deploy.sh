#!/bin/bash

echo "🚀 Iniciando deploy do Asterisk..."

echo "📥 Atualizando código do Git..."
git pull origin main || exit 1

echo "🔁 Recarregando dialplan..."
asterisk -rx "dialplan reload"

echo "🔁 Recarregando PJSIP..."
asterisk -rx "pjsip reload"

echo "🔁 Recarregando filas..."
asterisk -rx "queue reload all"

echo "✅ Deploy finalizado com sucesso!"
