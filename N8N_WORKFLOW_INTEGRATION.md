# Integração: Seu Workflow n8n + Laravel APIs

## 📊 Seu Workflow Atual (Análise)

```
WhatsApp (WAHA)
    ↓ [webhook]
Webhook Trigger
    ↓ [extrai dados]
Edit Fields (session, chatId, message, etc)
    ↓ [valida evento e chat]
If (event == "message" && chatId == seu-grupo)
    ↓ [trata tipos de mensagem]
Switch (text vs media)
    ↓
AI Agent (GPT-4)
    ├── Tools: buscar_cliente, buscar_contrato, etc
    └── Redis Chat Memory (histórico)
    ↓ [formata resposta]
WAHA: Send Seen + Start Typing + Wait + Send Message
```

**Status:** ✅ Funcionando bem!
**Próximo passo:** Conectar as APIs do Laravel que criamos

---

## 🔗 Como Conectar às APIs do Laravel

Você tem **duas abordagens**:

### **Opção A: Tools atuais chamam workflows que chamam a API (Atual)**
```
AI Agent
  ↓ (chama tool)
Tool: buscar_cliente (workflow)
  ↓ (HTTP Request)
GET /api/clientes/search/{termo}
  ↓
Retorna cliente
```

### **Opção B: Tools chamam diretamente a API (Mais simples)**
```
AI Agent
  ↓ (chama tool)
Tool: buscar_cliente (HTTP Node direto)
  ↓
GET /api/clientes/search/{termo}
  ↓
Retorna cliente
```

---

## 🎯 Passo a Passo: Adicionar uma Tool que Chama a API

### **1. Criar um Tool Node no seu workflow**

Dentro do seu workflow, adicione um novo node:

**Type:** HTTP Request  
**Name:** `API - Buscar Cliente`

**Configuração:**

```
Method: GET
URL: http://seu-app/api/clientes/search/{{ $fromAI('termo', 'Nome do cliente a buscar', 'string') }}
Headers:
  X-API-Key: {{ env.API_KEY }}
  Content-Type: application/json
Authentication: None
```

### **2. Converter para Tool do AI Agent**

Depois, envolva esse node com um **Tool Workflow**:

**Type:** Tool Workflow  
**Name:** `buscar_cliente_via_api`

**Configuração do Tool:**

```json
{
  "name": "buscar_cliente_via_api",
  "description": "Busca clientes no banco via API do Laravel",
  "inputs": {
    "termo": {
      "type": "string",
      "description": "Nome ou parte do nome do cliente"
    }
  }
}
```

**Workflow interno:**

```
Input (termo)
  ↓
HTTP Request
  ├─ URL: GET /api/clientes/search/{{ $json.termo }}
  ├─ Headers: X-API-Key
  ↓
Return: resultado da API
```

### **3. Conectar ao AI Agent**

No seu **AI Agent**, adicione a tool na seção **Built-in Tools** ou como referência ao workflow que acabou de criar.

---

## 🚀 Exemplo Completo: Ferramenta de Busca Direta

Se você quer simplificar (sem workflows aninhados), crie um tool node assim:

```javascript
// Em um Code node antes do AI Agent
return {
  tools: [
    {
      name: "buscar_cliente_direto",
      description: "Busca cliente via API Laravel",
      schema: {
        parameters: {
          type: "object",
          properties: {
            termo: {
              type: "string",
              description: "Nome do cliente"
            }
          },
          required: ["termo"]
        }
      }
    }
  ]
}
```

Depois no AI Agent, configure a tool para fazer um **HTTP Request**:

```
POST /seu-endpoint-que-dispara-a-busca
Body: { termo: "{{ $json.termo }}" }
Headers: X-API-Key
```

---

## 📡 Fluxo Proposto: Sua Arquitetura Final

```
┌─────────────────────────────────────────────┐
│         Cliente (WhatsApp)                  │
│  "Quanto devo pelo contrato do João?"       │
└──────────────┬──────────────────────────────┘
               │ WAHA Webhook
               ▼
┌─────────────────────────────────────────────┐
│    Seu Workflow n8n Atual                   │
│  ├─ Webhook Trigger                         │
│  ├─ Edit Fields (extrai dados)              │
│  ├─ If (valida)                             │
│  └─ AI Agent com Tools                      │
│     ├─ buscar_cliente                       │
│     │  └─ HTTP: GET /api/clientes/search    │
│     ├─ buscar_contrato                      │
│     │  └─ HTTP: GET /api/contratos/cliente  │
│     └─ registrar_pagamento                  │
│        └─ HTTP: POST /api/pagamentos        │
└──────────────┬──────────────────────────────┘
               │ HTTP Requests
               ▼
┌─────────────────────────────────────────────┐
│   Laravel API (suas rotas)                  │
│  ├─ GET /api/clientes/search/{termo}        │
│  ├─ GET /api/contratos/cliente/{id}         │
│  ├─ GET /api/pagamentos/{contratoId}        │
│  └─ POST /api/pagamentos                    │
└──────────────┬──────────────────────────────┘
               │ Query Database
               ▼
┌─────────────────────────────────────────────┐
│   Seu Banco de Dados                        │
│  ├─ Clientes                                │
│  ├─ Contratos                               │
│  └─ Pagamentos                              │
└─────────────────────────────────────────────┘
```

---

## ⚙️ Configurar Variáveis de Ambiente no n8n

No painel do n8n, vá em **Settings → Environment Variables** e configure:

```
API_URL = http://seu-app
API_KEY = localflow_sk_6a8e9f3b2c1d4e5f7g8h9i0j1k2l3m4n
```

Use em qualquer lugar:
```
{{ env.API_URL }}/api/contratos
{{ env.API_KEY }}
```

---

## 🔄 Fluxo Completo de Exemplo

### **Entrada:**
```
Cliente: "Quanto devo no contrato do João?"
```

### **Dentro do n8n:**

1. **Webhook** recebe a mensagem
2. **Edit Fields** extrai:
   - `chatId`: "13293393600518@lid"
   - `message`: "Quanto devo no contrato do João?"
3. **If** valida (é mensagem? é do grupo autorizado?)
4. **AI Agent** processa:
   ```
   Usuário disse: "Quanto devo no contrato do João?"
   
   Ferramenta: buscar_cliente
   Input: "João"
   HTTP: GET /api/clientes/search/João
   Resposta: { "id": 5, "nome": "João Silva" }
   
   Ferramenta: buscar_contrato
   Input: Cliente_ID = 5
   HTTP: GET /api/contratos/cliente/5
   Resposta: [{ "id": 1, "total": 1500, "total_pago": 500, "saldo": 1000 }]
   
   AI gera resposta: "João, você deve R$ 1.000,00"
   ```
5. **WAHA nodes** enviam:
   - ✓ (seen)
   - ⌛ (typing)
   - 💬 "João, você deve R$ 1.000,00"

### **Saída:**
```
Resposta na conversa: "João, você deve R$ 1.000,00"
```

---

## 📝 Checklist de Integração

- [ ] Variáveis de ambiente configuradas (`API_URL`, `API_KEY`)
- [ ] HTTP Nodes criados para cada endpoint
- [ ] Tools conectadas ao AI Agent
- [ ] Teste local: enviar mensagem e verificar resposta
- [ ] Teste de API: verificar se as chamadas chegam ao Laravel
- [ ] Logs: ver traces no n8n para debug

---

## 🐛 Troubleshooting

### **"401 Unauthorized" nas chamadas à API**
```
✓ Verifique se X-API-Key está correta
✓ Verifique se a variável {{ env.API_KEY }} está definida
✓ Teste com curl: 
  curl -H "X-API-Key: seu-token" http://seu-app/api/contratos
```

### **"404 Not Found"**
```
✓ Verifique a URL da API
✓ Verifique se as rotas estão registradas: php artisan route:list --path=api
✓ Verifique o prefixo /api/ na URL
```

### **AI Agent não reconhece a tool**
```
✓ Verifique se o Tool Workflow está conectado ao AI Agent
✓ Verifique a descrição da tool (deve ser clara)
✓ Verifique os inputs esperados
```

### **Resposta lenta**
```
✓ Aumente o timeout do HTTP Request (padrão 30s)
✓ Adicione indexes nas queries do Laravel
✓ Use Redis Chat Memory para cache de consultas frequentes
```

---

## 📚 Documentação Referência

- 📖 [N8N_INTEGRATION_GUIDE.md](N8N_INTEGRATION_GUIDE.md) — Fluxos prontos para importar
- 📋 [API_DOCUMENTATION.md](API_DOCUMENTATION.md) — Endpoints disponíveis
- 🔐 [Autenticação com X-API-Key](API_DOCUMENTATION.md#-autenticação)

---

## 💡 Dicas Avançadas

### **1. Cachear Resultados Frequentes**

Se a mesma busca é feita várias vezes, cache no Redis:

```javascript
// Antes do HTTP Request
const cacheKey = `cliente_${termo}`;
const cached = await $execution.getVariable(cacheKey);
if (cached) return cached;

// Depois do HTTP Request
await $execution.setVariable(cacheKey, result);
```

### **2. Tratamento de Erros Robusto**

```javascript
try {
  const response = await fetch(url, options);
  if (!response.ok) throw new Error(`API returned ${response.status}`);
  return response.json();
} catch (error) {
  return {
    erro: true,
    mensagem: `Não consegui consultar o sistema: ${error.message}`
  };
}
```

### **3. Rate Limiting**

Se a API tiver limite de requisições:

```javascript
// Add delay entre requisições
await new Promise(resolve => setTimeout(resolve, 500));
```

---

## ✅ Próximos Passos

1. **Teste a API** fora do n8n:
   ```bash
   curl -H "X-API-Key: seu-token" http://seu-app/api/contratos
   ```

2. **Crie as Tools** no seu workflow
   - Comece com `buscar_cliente`
   - Depois `buscar_contrato`
   - Depois `registrar_pagamento`

3. **Teste integrado**:
   - Envie uma mensagem no WhatsApp
   - Monitore o n8n (Execution)
   - Verifique os logs das requisições

4. **Otimize**
   - Adicione cache se necessário
   - Implemente retry automático
   - Monitore performance

Pronto para ter seu WhatsApp inteligente em produção! 🚀
