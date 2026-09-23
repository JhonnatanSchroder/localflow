# Guia de Integração: WhatsApp → n8n → Agente IA → Laravel API

## 🏗️ Arquitetura do Sistema

```
┌─────────────────┐
│   Cliente       │
│  (WhatsApp)     │
└────────┬────────┘
         │ Envia mensagem
         ▼
┌─────────────────────────────────────────────────────────────┐
│                    WAHA (WhatsApp API)                      │
│  (Faz parte da sua aplicação Laravel)                       │
│  - Recebe mensagens                                         │
│  - Armazena em banco (whatsapp_messages)                    │
│  - Envia respostas                                          │
└────────┬────────────────────────────────────────────────────┘
         │ Webhook POST /api/whatsapp/waha
         ▼
┌─────────────────────────────────────────────────────────────┐
│                         n8n                                  │
│  - Lê mensagens do banco                                    │
│  - Chama API Laravel para obter dados                       │
│  - Trata lógica de negócio                                  │
│  - Envia para agente IA ou responde diretamente             │
└────────┬────────────────────────────────────────────────────┘
         │ HTTP Requests
         ▼
┌─────────────────────────────────────────────────────────────┐
│              Laravel API (seu sistema)                       │
│  - GET /api/contratos                                       │
│  - GET /api/clientes/telefone/{tel}                         │
│  - GET /api/pagamentos/{contratoId}                         │
│  - POST /api/pagamentos                                     │
└────────┬────────────────────────────────────────────────────┘
         │ Agente de IA (AgentService)
         │ - Processa linguagem natural
         │ - Executa operações
         ▼
┌─────────────────────────────────────────────────────────────┐
│                    Banco de Dados                            │
│  - Contratos                                                │
│  - Clientes                                                 │
│  - Pagamentos                                               │
│  - WhatsApp Messages                                        │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔄 Fluxo Passo-a-Passo

### **Cenário: Cliente pergunta "Quanto devo?"**

#### 1️⃣ Cliente envia mensagem (WhatsApp)
```
Cliente: "Quanto devo pelo contrato 1?"
```

#### 2️⃣ WAHA recebe e armazena
```
POST /api/whatsapp/waha (webhook)
↓
WhatsappMessage criada no banco
↓
ProcessWhatsappMessage job disparado (fila)
```

#### 3️⃣ n8n monitora mudanças (Opção A - Polling)
```
n8n Trigger: "Intervalo fixo" (a cada 1 min)
↓
GET http://seu-app/api/whatsapp/messages?not_processed=true
↓
Retorna novas mensagens
```

#### 4️⃣ n8n busca dados via API
```
HTTP Request: GET /api/contratos/1
Headers: X-API-Key: seu-token
↓
Resposta:
{
  "id": 1,
  "cliente": "João Silva",
  "total": 1500.00,
  "total_pago": 500.00,
  "saldo": 1000.00
}
```

#### 5️⃣ n8n processa e formata resposta
```javascript
// Node JavaScript em n8n
return {
  mensagem: `Olá ${data.cliente}! Você deve R$ ${data.saldo.toFixed(2)}`,
  tipo: "texto"
};
```

#### 6️⃣ Envia resposta via WAHA
```
HTTP POST /api/whatsapp/send
Body: {
  chatId: "5511999999999@c.us",
  message: "Olá João Silva! Você deve R$ 1000.00"
}
↓
WAHA envia para WhatsApp
↓
Cliente recebe resposta
```

---

## 🛠️ Configuração Prática no n8n

### **Fluxo 1: Consultar Saldo de Contrato**

#### Node 1: Trigger (Polling)
```
Type: Schedule / Interval
Interval: 1 minute
```

#### Node 2: Buscar Novas Mensagens
```
Type: HTTP Request
Method: GET
URL: http://seu-app/api/whatsapp/messages?not_processed=true
Headers:
  X-API-Key: seu-token
  Authorization: Bearer seu-token-opcional
```

Response:
```json
[
  {
    "id": 1,
    "from": "5511999999999",
    "body": "Quanto devo pelo contrato 1?",
    "contrato_id": 1
  }
]
```

#### Node 3: Extrair Número do Contrato
```
Type: Code / JavaScript
Script:
```javascript
const msg = $('Buscar Novas Mensagens').json();
const contratoMatch = msg.body?.match(/#?(\d+)/);
return {
  contratoId: contratoMatch ? contratoMatch[1] : null,
  mensagem: msg.body,
  telefone: msg.from
};
```

#### Node 4: Buscar Dados do Contrato
```
Type: HTTP Request
Method: GET
URL: http://seu-app/api/contratos/{{ $('Extrair Número').json().contratoId }}
Headers:
  X-API-Key: seu-token
```

Response esperada:
```json
{
  "id": 1,
  "cliente": { "nome": "João Silva" },
  "total": 1500.00,
  "total_pago": 500.00,
  "saldo": 1000.00,
  "proxima_cobranca": "2026-10-01"
}
```

#### Node 5: Formatar Resposta
```
Type: Code / JavaScript
Script:
```javascript
const contrato = $('Buscar Dados do Contrato').json().data;
const saldo = (contrato.total - contrato.total_pago).toFixed(2);

return {
  resposta: `Olá ${contrato.cliente.nome}! 

Seu contrato #${contrato.id}:
💰 Valor total: R$ ${contrato.total.toFixed(2)}
✅ Já pagou: R$ ${contrato.total_pago.toFixed(2)}
⏳ Deve: R$ ${saldo}

📅 Próxima cobrança: ${contrato.proxima_cobranca}`
};
```

#### Node 6: Enviar Resposta via WAHA
```
Type: HTTP Request
Method: POST
URL: http://seu-app/api/whatsapp/send
Headers:
  X-API-Key: seu-token
Body (JSON):
```javascript
{
  "chatId": "$('Extrair Número').json().telefone@c.us",
  "message": "$('Formatar Resposta').json().resposta"
}
```

---

## 🤖 Fluxo 2: Usar Agente de IA (Mais Inteligente)

Se quer usar o agente de IA existente para respostas mais naturais:

#### Node 1-3: (Igual ao anterior - Extrair dados)

#### Node 4: Chamar Agente de IA
```
Type: HTTP Request
Method: POST
URL: http://seu-app/api/agent/reply
Headers:
  X-API-Key: seu-token
Body (JSON):
```javascript
{
  "phone": "$('Extrair Número').json().telefone",
  "message": "$('Extrair Número').json().mensagem"
}
```

Response:
```json
{
  "resposta": "Olá João! Você deve R$ 1000.00 no contrato #1..."
}
```

#### Node 5: Enviar Resposta via WAHA
```
(Mesmo que o fluxo anterior)
```

---

## 📡 Criar Endpoint para n8n Buscar Mensagens

Adicione ao seu `PagamentoController` ou crie um novo:

```php
// routes/api.php
Route::middleware('api.auth')->group(function () {
    Route::get('/whatsapp/messages', [WhatsappMessageController::class, 'unprocessed']);
});

// app/Http/Controllers/Api/WhatsappMessageController.php
<?php

namespace App\Http\Controllers\Api;

use App\Models\WhatsappMessage;
use Illuminate\Http\JsonResponse;

class WhatsappMessageController extends Controller
{
    public function unprocessed(): JsonResponse
    {
        $messages = WhatsappMessage::query()
            ->where('direction', 'in')
            ->where('processed', false)
            ->orWhereNull('processed')
            ->latest('id')
            ->limit(10)
            ->get([
                'id',
                'message_id',
                'from',
                'chat_id',
                'body',
                'created_at'
            ]);

        return response()->json([
            'status' => 'success',
            'data' => $messages
        ]);
    }
}
```

---

## 🔐 Endpoint para o Agente de IA

Crie um endpoint para n8n chamar o agente:

```php
// routes/api.php
Route::post('/agent/reply', [AgentController::class, 'reply']);

// app/Http/Controllers/Api/AgentController.php
<?php

namespace App\Http\Controllers\Api;

use App\Services\WhatsApp\AgentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function reply(Request $request, AgentService $agentService): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string'
        ]);

        try {
            $resposta = $agentService->reply(
                $validated['phone'],
                $validated['message']
            );

            return response()->json([
                'status' => 'success',
                'resposta' => $resposta
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
```

---

## 📋 Fluxos Prontos para n8n

### **Fluxo 3: Registrar Pagamento**

```
Trigger: Webhook (recebe dados de pagamento)
  ↓
Validar dados (contrato_id, valor, data)
  ↓
HTTP POST /api/pagamentos
  ├─ Headers: X-API-Key
  ├─ Body: { contrato_id, valor, data }
  ↓
IF sucesso:
  ├─ Buscar detalhes do contrato
  ├─ Formatar mensagem
  └─ Enviar confirmação via WAHA
ELSE:
  └─ Enviar erro ao cliente
```

### **Fluxo 4: Buscar Cliente por Telefone**

```
Trigger: Mensagem contém "quem é" ou "dados de"
  ↓
Extrair telefone ou nome
  ↓
HTTP GET /api/clientes/search/{termo}
  ├─ Headers: X-API-Key
  ↓
Formatar e enviar detalhes
```

### **Fluxo 5: Listar Contratos Ativos**

```
Trigger: Mensagem = "meus contratos" ou "contratos ativos"
  ↓
HTTP GET /api/contratos?status=ATIVO
  ├─ Headers: X-API-Key
  ↓
Loop em cada contrato:
  ├─ Formatar item
  ├─ Calcular saldo
  ↓
Enviar lista formatada
```

---

## 🎯 Template JSON para n8n

Copie para importar como base em n8n:

```json
{
  "name": "WhatsApp - Consultar Saldo",
  "nodes": [
    {
      "parameters": {
        "interval": [1, "minutes"]
      },
      "name": "Schedule Trigger",
      "type": "n8n-nodes-base.schedule",
      "position": [250, 300]
    },
    {
      "parameters": {
        "method": "GET",
        "url": "http://seu-app/api/whatsapp/messages",
        "authentication": "genericCredentialType",
        "genericCredentialType": {
          "generic": {
            "headers": {
              "X-API-Key": "{{ env.API_KEY }}"
            }
          }
        }
      },
      "name": "Buscar Mensagens",
      "type": "n8n-nodes-base.httpRequest",
      "position": [450, 300]
    }
  ],
  "connections": {
    "Schedule Trigger": {
      "main": [[{ "node": "Buscar Mensagens", "type": "main", "index": 0 }]]
    }
  }
}
```

---

## 🔑 Variáveis de Ambiente no n8n

Configure no painel de n8n:

```
API_KEY = localflow_sk_6a8e9f3b2c1d4e5f7g8h9i0j1k2l3m4n
API_URL = http://seu-app (ou https://seu-dominio.com)
WAHA_KEY = sua-chave-waha
```

Use em qualquer URL:
```
{{ env.API_URL }}/api/contratos
{{ env.API_KEY }}
```

---

## 📊 Monitoramento

### Verificar fluxos no Laravel:

```php
// Ver mensagens processadas
WhatsappMessage::where('direction', 'in')->count();

// Ver último agente call
AgentAudit::latest()->first();

// Ver pagamentos recentes
Pagamento::latest()->limit(5)->get();
```

---

## ✅ Checklist de Configuração

- [ ] API_KEY gerada e configurada no .env
- [ ] n8n acessando sua API (teste com curl)
- [ ] WAHA webhook enviando mensagens ao banco
- [ ] n8n conseguindo ler mensagens do banco
- [ ] Agente de IA respondendo corretamente
- [ ] WAHA enviando respostas de volta
- [ ] Teste completo: Enviar msg → Receber resposta
- [ ] Fluxos documentados e versionados no n8n

---

## 🚀 Próximos Passos

1. **Teste local** → Configure tudo localmente e teste
2. **Deploy** → Mude para produção com domínio real
3. **Monitoramento** → Crie dashboards no n8n
4. **Escalabilidade** → Otimize queries se tiver muito volume
5. **Segurança** → Use HTTPS e rotacione API key

Pronto para integrar! 🎯
