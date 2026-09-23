# ⚡ Quick Start - Importar Workflow em 5 Minutos

## 📁 Você tem 5 arquivos:

```
1. n8n-workflow-completo.json          ← IMPORTE PRIMEIRO
2. n8n-tool-buscar-cliente.json        ← Depois importe estes 4
3. n8n-tool-buscar-contrato.json
4. n8n-tool-registrar-pagamento.json
5. n8n-tool-registrar-movimentacao.json
```

---

## ⚡ 5 Passos Rápidos:

### **1️⃣ Configure as Variáveis (1 min)**

No n8n → **Settings → Environment Variables**

```
API_URL = http://localhost:8000
API_KEY = localflow_sk_6a8e9f3b2c1d4e5f7g8h9i0j1k2l3m4n
```

### **2️⃣ Importe os 4 Sub-Workflows (2 min)**

**Importe nesta ordem:**

1. n8n-tool-buscar-cliente.json
2. n8n-tool-buscar-contrato.json
3. n8n-tool-registrar-pagamento.json
4. n8n-tool-registrar-movimentacao.json

**Como importar:** Clique em **+** → **Import from file** → Selecione o arquivo → **Save**

### **3️⃣ Importe o Workflow Principal (1 min)**

Importe: `n8n-workflow-completo.json`

### **4️⃣ Conecte as Credenciais (1 min)**

No workflow principal:

- **🤖 OpenAI GPT-4o-mini:** Clique → Credentials → Create new → OpenAI → Sua chave
- **💾 Redis Chat Memory:** Clique → Credentials → Create new → Redis → Seus dados

### **5️⃣ Ative e Teste! (0 min)**

- Toggle superior direito: **Ative o workflow**
- Envie uma mensagem no WhatsApp
- Pronto! 🎉

---

## 🧪 Teste Rápido:

```
Você no WhatsApp:  "Olá"
Bot responde:      "Olá! Sou seu assistente de contratos"

Você:              "Busca o cliente João"
Bot:               "Buscando no banco de dados..."
                   [usa buscar_cliente_via_api → GET /api/clientes/search/João]
                   "Encontrei João Silva (CLI-5)"

Você:              "Qual é o saldo do contrato CRT-1?"
Bot:               "Consultando..."
                   [usa buscar_contrato_via_api → GET /api/contratos/CRT-1]
                   "O saldo é R$ 1.500,00"
```

---

## 🔴 Se não funcionar:

**Erro: "401 Unauthorized"**
```bash
# Verifique a API_KEY:
php artisan tinker
echo config('api.key');
# Copie para: Settings → Environment Variables → API_KEY
```

**Erro: "Redis connection refused"**
```bash
# Redis não está rodando. Inicie:
redis-server  (ou no Docker/WSL)
```

**Erro: "OpenAI API key invalid"**
```bash
# Vá em: https://platform.openai.com/api-keys
# Copie sua chave para: Credentials → OpenAI
```

**Erro: "Workflow não encontrado"**
```bash
# As Tools precisam dos 4 sub-workflows já importados
# Importe os 4 primeiro, depois o workflow principal
```

---

## 📱 Como Funciona:

```
WhatsApp → Webhook WAHA → n8n Workflow
                          ↓
                      AI Agent (GPT-4)
                          ↓
        ┌──────────────────┼──────────────────┐
        ↓                  ↓                   ↓
    Buscar Cliente    Buscar Contrato    Registrar Pagamento
        ↓                  ↓                   ↓
    API Laravel      API Laravel        API Laravel
        ↓                  ↓                   ↓
    Banco de Dados
        ↓
    Resposta → WhatsApp
```

---

## ✅ Pronto!

Você tem um **WhatsApp inteligente com IA** conectado ao seu banco de dados Laravel!

**O que você pode fazer:**
- 🔍 Buscar clientes por nome ou telefone
- 📋 Consultar contratos e saldos
- 💳 Registrar pagamentos
- 📦 Registrar retiradas/devoluções
- 🧠 Tudo automaticamente com IA!

---

**Dúvidas? Veja:** `SETUP_N8N_WORKFLOW.md` (guia completo)

