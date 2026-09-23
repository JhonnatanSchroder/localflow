# 🚀 Guia: Como Importar o Workflow n8n Pronto

Aqui está tudo que você precisa para ter seu **WhatsApp inteligente com AI Agent** funcionando! ✨

---

## 📦 Arquivos Criados

```
✓ n8n-workflow-completo.json          ← Workflow principal (importe PRIMEIRO)
✓ n8n-tool-buscar-cliente.json        ← Sub-workflow: Busca Cliente
✓ n8n-tool-buscar-contrato.json       ← Sub-workflow: Busca Contrato
✓ n8n-tool-registrar-pagamento.json   ← Sub-workflow: Registra Pagamento
✓ n8n-tool-registrar-movimentacao.json ← Sub-workflow: Movimentação (retirada/devolução)
```

---

## ⚙️ Passo 1: Configurar Variáveis de Ambiente no n8n

1. Acesse seu dashboard do n8n
2. Vá em **Settings** → **Environment Variables**
3. Clique em **Add Variable**
4. Adicione as seguintes variáveis:

```
Variable: API_URL
Value: http://localhost:8000  (ou seu domínio, ex: https://app.com)

Variable: API_KEY
Value: localflow_sk_6a8e9f3b2c1d4e5f7g8h9i0j1k2l3m4n
```

> 💡 Se não souber a chave, execute: `php artisan tinker` e depois `echo config('api.key');`

---

## 📥 Passo 2: Importar os Sub-Workflows (Tools)

Estes são os mini-workflows que as Tools usam. Importe **nesta ordem**:

### **1. Importar: API - Buscar Cliente**

1. Clique em **+** ou **New**
2. Selecione **Import from file**
3. Escolha: `n8n-tool-buscar-cliente.json`
4. Clique em **Save** (não ative ainda)

### **2. Importar: API - Buscar Contrato**

Repita o processo com: `n8n-tool-buscar-contrato.json`

### **3. Importar: API - Registrar Pagamento**

Repita o processo com: `n8n-tool-registrar-pagamento.json`

### **4. Importar: API - Registrar Movimentação**

Repita o processo com: `n8n-tool-registrar-movimentacao.json`

---

## 🔗 Passo 3: Importar o Workflow Principal

1. Clique em **+** ou **New**
2. Selecione **Import from file**
3. Escolha: `n8n-workflow-completo.json`
4. **IMPORTANTE:** Você verá erros sobre credenciais faltando. Isso é normal!

---

## 🔑 Passo 4: Conectar Credenciais

No workflow principal que você acabou de importar, vá até o node **🤖 OpenAI GPT-4o-mini**:

1. Clique no node
2. Na aba **Credentials**, clique em **Create new**
3. Escolha **OpenAI** (ou use uma existente)
4. Insira sua **API Key do OpenAI**
5. Clique em **Save**

Faça o mesmo para **💾 Redis Chat Memory**:

1. Clique no node
2. Na aba **Credentials**, clique em **Create new**
3. Escolha **Redis**
4. Configure com seus dados:
   - **Host:** localhost (ou seu host Redis)
   - **Port:** 6379
   - **Password:** (deixe em branco se não tiver)
5. Clique em **Save**

---

## 🧠 Passo 5: Conectar as Tools ao AI Agent

Esta é a parte mais importante!

1. Abra o workflow principal
2. Selecione o node **🧠 AI Agent**
3. Role para baixo até **Tools**
4. Clique em **Add Tool**
5. Selecione **Workflow** (não HTTP)
6. Para cada ferramenta, configure:

### **Tool 1: Buscar Cliente**

```
Name: buscar_cliente_via_api
Description: Busca clientes no banco usando nome ou telefone
Workflow: API - Buscar Cliente (select)
Input Mapping:
  termo → $fromAI('termo', 'Nome, CPF ou telefone do cliente', 'string')
```

### **Tool 2: Buscar Contrato**

```
Name: buscar_contrato_via_api
Description: Busca detalhes de um contrato pelo ID ou cliente
Workflow: API - Buscar Contrato (select)
Input Mapping:
  referencia → $fromAI('referencia', 'ID do cliente ou contrato', 'string')
```

### **Tool 3: Registrar Pagamento**

```
Name: registrar_pagamento
Description: Registra um pagamento em um contrato
Workflow: API - Registrar Pagamento (select)
Input Mapping:
  contrato_id → $fromAI('contrato_id', 'ID do contrato', 'string')
  valor → $fromAI('valor', 'Valor em reais', 'number')
  data → $fromAI('data', 'Data em YYYY-MM-DD', 'string')
```

### **Tool 4: Registrar Movimentação**

```
Name: registrar_movimentacao
Description: Registra retirada ou devolução de peças
Workflow: API - Registrar Movimentação (select)
Input Mapping:
  contrato_id → $fromAI('contrato_id', 'ID do contrato', 'string')
  tipo → $fromAI('tipo', 'RETIRADA ou DEVOLUCAO', 'string')
  quantidade → $fromAI('quantidade', 'Número de peças', 'number')
  data → $fromAI('data', 'Data em YYYY-MM-DD', 'string')
```

---

## 🔌 Passo 6: Configurar o Webhook WAHA

O workflow está pronto para receber webhooks do WAHA. Você tem 2 opções:

### **Opção A: Webhook Direto** (Recomendado)

1. No node **🔔 Webhook WAHA**, clique nele
2. Copie a URL (algo como `https://seu-n8n.com/webhook/webhook`)
3. Configure no seu WAHA:
   - Vá em `config/session.default.json` ou envie webhook para:
   ```json
   {
     "webhook": {
       "url": "https://seu-n8n.com/webhook/webhook",
       "events": ["message", "message.ack"]
     }
   }
   ```

### **Opção B: Polling** (Alternativa)

Se preferir polling em vez de webhook:

1. Crie um novo workflow com:
   - **Trigger:** Cron (a cada 10 segundos)
   - **Node:** HTTP Request → `GET http://seu-api/api/whatsapp/messages/unprocessed`
   - Processe as mensagens e chame o workflow principal

---

## ✅ Passo 7: Testar!

1. **Ative o workflow** principal (toggle no canto superior direito)
2. **Envie uma mensagem de WhatsApp** para seu bot
3. **Veja o que acontece:**
   - ✅ Mensagem chega no webhook
   - ✅ AI Agent processa
   - ✅ Tools chamam APIs do Laravel
   - ✅ Resposta é enviada de volta

### **Teste as 4 operações:**

```
"Qual é o telefone do João?"
→ Tool: buscar_cliente_via_api
→ API: GET /api/clientes/search/João
→ Resposta: "O telefone do João é..."

"Quais são os contratos do CLI-1?"
→ Tool: buscar_contrato_via_api
→ API: GET /api/contratos/CLI-1
→ Resposta: "Os contratos são..."

"Registrar pagamento de 500 hoje no CRT-1"
→ Tool: registrar_pagamento
→ API: POST /api/pagamentos
→ Resposta: "Pagamento registrado!"

"Retirada de 2 peças no CRT-1"
→ Tool: registrar_movimentacao
→ API: POST /api/contratos/CRT-1/movimentacoes
→ Resposta: "Retirada registrada!"
```

---

## 🐛 Troubleshooting

### **"X-API-Key Inválida" (401 Unauthorized)**

```bash
# No seu Laravel, verifique a chave:
php artisan tinker
echo config('api.key');

# Depois copie para: Settings → Environment Variables → API_KEY
```

### **"Contrato não encontrado" (404)**

```bash
# Verifique se as rotas existem:
php artisan route:list --path=api

# Deve ter:
# GET /api/clientes/search/{termo}
# GET /api/contratos/{id}
# POST /api/pagamentos
# POST /api/contratos/{id}/movimentacoes
```

### **"Redis connection failed"**

```bash
# Verifique se Redis está rodando:
redis-cli ping
# Deve retornar: PONG

# Se não estiver:
# Windows: redis-server (no WSL) ou Docker
# Linux: systemctl start redis-server
```

### **"OpenAI API key is invalid"**

```
Acesse: https://platform.openai.com/api-keys
Copie sua chave
Coloque no n8n → Credentials → OpenAI
```

### **Workflow lento ou travado**

```
✓ Verifique logs: Executions → view logs
✓ Verifique timeout HTTP (padrão 30s)
✓ Verifique Redis está rápido: redis-cli PING
✓ Verifique OpenAI API status: https://status.openai.com
```

---

## 📊 Fluxo Visual Completo

```
Cliente WhatsApp
      ↓
WAHA Webhook
      ↓
n8n Webhook Trigger (🔔)
      ↓
Extrai dados (📋)
      ↓
Valida evento (✅)
      ↓
AI Agent (🧠) ← Conecta com:
  ├─ OpenAI GPT-4o-mini (modelo)
  ├─ Redis Chat Memory (histórico)
  └─ 4 Tools (chamam workflows)
      ├─ Tool 1: buscar_cliente_via_api
      │   └─ HTTP GET /api/clientes/search/{termo}
      ├─ Tool 2: buscar_contrato_via_api
      │   └─ HTTP GET /api/contratos/{id}
      ├─ Tool 3: registrar_pagamento
      │   └─ HTTP POST /api/pagamentos
      └─ Tool 4: registrar_movimentacao
          └─ HTTP POST /api/contratos/{id}/movimentacoes
      ↓
Laravel API
      ↓
Banco de dados
      ↓
Resposta formatada
      ↓
Marca como lido (✅)
      ↓
Digitando... (⌨️)
      ↓
Aguarda 2s (⏳)
      ↓
Envia mensagem (💬)
      ↓
Cliente recebe resposta
```

---

## 🎯 Checklist Final

- [ ] Variáveis de ambiente criadas (API_URL, API_KEY)
- [ ] 4 sub-workflows importados
- [ ] Workflow principal importado
- [ ] Credenciais do OpenAI conectadas
- [ ] Credenciais do Redis conectadas
- [ ] 4 Tools conectadas ao AI Agent
- [ ] Webhook WAHA configurado
- [ ] Teste básico enviado ("Olá")
- [ ] Teste de busca enviado ("Qual cliente é o João?")
- [ ] Logs verificados (Executions)

---

## 🚀 Está Funcionando?

Se tudo está certo, você verá:

✅ Mensagem chega no WhatsApp  
✅ Bot marca como lido  
✅ Bot mostra "digitando..."  
✅ Bot responde com dados do banco  
✅ AI Agent usa as Tools automaticamente  

**Parabéns! Seu WhatsApp inteligente está VIVO!** 🎉

---

## 📝 Próximos Passos (Opcional)

1. **Customizar System Message** do AI Agent
   - Edite o node 🧠 AI Agent
   - Mude a system message conforme sua necessidade

2. **Adicionar mais Tools**
   - Crie novos sub-workflows
   - Adicione ao AI Agent

3. **Implementar Rate Limiting**
   - Adicione validação de limite de chamadas/user

4. **Monitorar Performance**
   - Use o painel de Executions do n8n
   - Adicione alertas de erro

5. **Fazer Backup**
   - Exporte seus workflows periodicamente
   - Salve em Git/GitHub

---

## 🆘 Ainda com Dúvidas?

Se algo não funcionar:

1. **Veja os logs:** Clique em uma execução → "view logs"
2. **Teste a API direto:**
   ```bash
   curl -H "X-API-Key: sua-chave" http://localhost:8000/api/clientes/search/teste
   ```
3. **Verifique o WhatsApp WAHA:**
   ```bash
   curl -X POST http://localhost:3000/api/sendText \
     -H "Content-Type: application/json" \
     -d '{"session":"default", "chatId":"...@c.us", "text":"teste"}'
   ```

---

**Criado em:** 2026-09-21  
**Status:** ✅ Pronto para produção  
**Última atualização:** Workflow completo com 4 Tools integradas

