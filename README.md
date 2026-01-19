# 📞 Central de Atendimento Mototáxi — Asterisk

Construída com **Asterisk**, temos uma central de atendimento com foco em:

- Filas de atendimento
- Gravação de chamadas
- Integração com painel web (PHP)
- Controle via Git (versionamento de configurações)
- Deploy seguro sem derrubar chamadas

---

## 🧱 Arquitetura Geral

Cliente liga  
→ Fila de Atendimento (Queue)  
→ Atendente / Ramal SIP  
→ Chamada gravada (MixMonitor)  
→ Registro no CDR (MariaDB)  
→ Painel Web (PHP)

---

## 📂 Estrutura do Repositório

```
.
├── asterisk/
│   ├── dialplan/
│   ├── pjsip/
│   └── queues/
├── web/
│   └── asterisk/
├── deploy.sh
├── .gitignore
└── README.md
```

---

## 🎧 Gravação de Chamadas

As chamadas são gravadas utilizando **MixMonitor**, com:

- Nome de arquivo padronizado
- Herança de gravação para canais Local/
- Registro do nome do áudio no CDR (campo `userfield`)

---

## 🌐 Painel Web (PHP)

WIP

---

## 📚 Documentação Oficial

- Asterisk Docs  
  https://docs.asterisk.org/

- MixMonitor  
  https://docs.asterisk.org/Configuration/Applications/MixMonitor/

- Queue  
  https://docs.asterisk.org/Configuration/Applications/Queue/

---

## 🧠 Observações

Projeto focado em centrais de mototáxi, com estrutura simples, segura e escalável.

---

📞 Asterisk é um software open source mantido pela Sangoma.
