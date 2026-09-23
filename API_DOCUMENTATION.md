# Documentação da API - Localflow

## 📋 Introdução

APIs REST para integração com n8n, WhatsApp e outras plataformas externas.

**Base URL:** `http://seu-dominio.com/api`

**Autenticação:** Bearer Token via header `X-API-Key`

## 🔐 Autenticação

Todas as rotas da API requerem a chave de API. Configure no `.env`:

```env
API_KEY=localflow_sk_6a8e9f3b2c1d4e5f7g8h9i0j1k2l3m4n
```

Use em cada requisição:

```bash
curl -H "X-API-Key: localflow_sk_6a8e9f3b2c1d4e5f7g8h9i0j1k2l3m4n" \
  http://seu-dominio.com/api/contratos
```

## 📦 Endpoints

### CONTRATOS

#### 1. Listar todos os contratos
```http
GET /api/contratos
X-API-Key: seu-token
```

**Resposta:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "cliente_id": 5,
      "cliente": {
        "id": 5,
        "nome": "João Silva",
        "telefone": "(11) 99999-9999"
      },
      "endereco": "Rua A, 123",
      "status": "ATIVO",
      "total": 1500.00,
      "total_pago": 500.00,
      "pecas_atuais": 10,
      "proxima_cobranca": "2026-10-01"
    }
  ]
}
```

#### 2. Detalhes de um contrato
```http
GET /api/contratos/{id}
X-API-Key: seu-token
```

**Exemplo:**
```bash
curl -H "X-API-Key: seu-token" http://seu-dominio.com/api/contratos/1
```

#### 3. Contratos de um cliente
```http
GET /api/contratos/cliente/{clienteId}
X-API-Key: seu-token
```

#### 4. Buscar contratos
```http
GET /api/contratos/search/{termo}
X-API-Key: seu-token
```

**Exemplo:**
```bash
curl -H "X-API-Key: seu-token" http://seu-dominio.com/api/contratos/search/João
```

---

### CLIENTES

#### 1. Listar todos os clientes
```http
GET /api/clientes
X-API-Key: seu-token
```

#### 2. Detalhes de um cliente
```http
GET /api/clientes/{id}
X-API-Key: seu-token
```

#### 3. Buscar cliente por telefone
```http
GET /api/clientes/telefone/{telefone}
X-API-Key: seu-token
```

**Exemplo:**
```bash
curl -H "X-API-Key: seu-token" "http://seu-dominio.com/api/clientes/telefone/11999999999"
```

#### 4. Buscar clientes
```http
GET /api/clientes/search/{termo}
X-API-Key: seu-token
```

**Exemplo:**
```bash
curl -H "X-API-Key: seu-token" http://seu-dominio.com/api/clientes/search/João
```

---

### PAGAMENTOS

#### 1. Pagamentos de um contrato
```http
GET /api/pagamentos/{contratoId}
X-API-Key: seu-token
```

**Resposta:**
```json
{
  "status": "success",
  "data": {
    "contrato_id": 1,
    "cliente": "João Silva",
    "total_contrato": 1500.00,
    "total_pago": 500.00,
    "saldo": 1000.00,
    "pagamentos": [
      {
        "id": 1,
        "data": "2026-09-21",
        "valor": 500.00
      }
    ]
  }
}
```

#### 2. Registrar novo pagamento
```http
POST /api/pagamentos
X-API-Key: seu-token
Content-Type: application/json

{
  "contrato_id": 1,
  "valor": 250.00,
  "data": "2026-09-21"
}
```

**Resposta (201 Created):**
```json
{
  "status": "success",
  "message": "Pagamento registrado com sucesso",
  "data": {
    "contrato_id": 1,
    "valor": 250.00,
    "data": "2026-09-21"
  }
}
```

---

## 🔗 Exemplos com n8n

### Fluxo: Consultar saldo de contrato via WhatsApp

1. **Trigger:** Webhook do WhatsApp (entrada de mensagem)
2. **Node:** HTTP Request
   - Method: `GET`
   - URL: `{{env.API_URL}}/api/contratos/1`
   - Headers: `X-API-Key: {{env.API_KEY}}`
3. **Node:** Responder no WhatsApp com o saldo

### Fluxo: Registrar pagamento via n8n

1. **Trigger:** Webhook POST (recebe dados do pagamento)
2. **Node:** HTTP Request
   - Method: `POST`
   - URL: `{{env.API_URL}}/api/pagamentos`
   - Headers: `X-API-Key: {{env.API_KEY}}`
   - Body:
     ```json
     {
       "contrato_id": "{{$json.contrato_id}}",
       "valor": "{{$json.valor}}",
       "data": "{{new Date().toISOString().split('T')[0]}}"
     }
     ```

---

## 📝 Códigos de Resposta

| Código | Significado |
|--------|-------------|
| `200` | Sucesso |
| `201` | Criado com sucesso |
| `400` | Erro de validação |
| `401` | Não autorizado (API key inválida) |
| `404` | Não encontrado |
| `500` | Erro do servidor |

---

## 🛠️ Variáveis de Ambiente

Configure no `.env`:

```env
# Chave de API para integração externa
API_KEY=localflow_sk_6a8e9f3b2c1d4e5f7g8h9i0j1k2l3m4n

# Timeout para requisições externas
API_TIMEOUT=30
```

---

## 💡 Dicas de Segurança

1. ✅ Altere a `API_KEY` padrão imediatamente em produção
2. ✅ Use HTTPS (não HTTP) em produção
3. ✅ Implemente rate limiting se necessário
4. ✅ Monitore os acessos via logs
5. ✅ Rotacione a API key periodicamente

---

## 📞 Suporte

Para dúvidas sobre integração, consulte a documentação do n8n:
- [n8n HTTP Request Node](https://docs.n8n.io/nodes/n8n-nodes-base.httpRequest/)
- [n8n Webhook Trigger](https://docs.n8n.io/nodes/n8n-nodes-base.webhook/)
