#!/usr/bin/env node

const apiBaseUrl = (process.env.MERCADO_PAGO_API_BASE_URL || "https://api.mercadopago.com").replace(/\/$/, "");
const accessToken = process.env.MERCADO_PAGO_ACCESS_TOKEN;

if (!accessToken) {
  process.stderr.write("MERCADO_PAGO_ACCESS_TOKEN não configurado.\n");
  process.exit(1);
}

const tools = [
  {
    name: "mercado_pago_get_subscription",
    description: "Consulta uma assinatura Mercado Pago pelo ID (somente leitura).",
    inputSchema: { type: "object", properties: { subscription_id: { type: "string" } }, required: ["subscription_id"] }
  },
  {
    name: "mercado_pago_get_payment",
    description: "Consulta um pagamento Mercado Pago pelo ID (somente leitura).",
    inputSchema: { type: "object", properties: { payment_id: { type: "string" } }, required: ["payment_id"] }
  },
  {
    name: "mercado_pago_search_subscriptions",
    description: "Pesquisa assinaturas por e-mail do pagador ou outros filtros suportados pela API (somente leitura).",
    inputSchema: { type: "object", properties: { payer_email: { type: "string" }, payer_id: { type: "string" }, q: { type: "string" }, limit: { type: "integer", minimum: 1, maximum: 100 } } }
  },
  {
    name: "mercado_pago_get_authorized_payments",
    description: "Consulta pagamentos autorizados associados a uma assinatura (somente leitura).",
    inputSchema: { type: "object", properties: { subscription_id: { type: "string" }, limit: { type: "integer", minimum: 1, maximum: 100 } }, required: ["subscription_id"] }
  }
];

function jsonRpc(result) {
  const body = JSON.stringify(result);
  process.stdout.write(`Content-Length: ${Buffer.byteLength(body)}\r\n\r\n${body}`);
}

async function mercadoPago(path, query = {}) {
  const url = new URL(`${apiBaseUrl}${path}`);
  for (const [key, value] of Object.entries(query)) {
    if (value !== undefined && value !== null && value !== "") url.searchParams.set(key, String(value));
  }
  const response = await fetch(url, { headers: { Authorization: `Bearer ${accessToken}`, Accept: "application/json" } });
  const text = await response.text();
  let data;
  try { data = text ? JSON.parse(text) : {}; } catch { data = { raw: text }; }
  if (!response.ok) throw new Error(`Mercado Pago HTTP ${response.status}: ${JSON.stringify(data)}`);
  return data;
}

async function callTool(name, args = {}) {
  if (name === "mercado_pago_get_subscription") return mercadoPago(`/preapproval/${encodeURIComponent(args.subscription_id)}`);
  if (name === "mercado_pago_get_payment") return mercadoPago(`/v1/payments/${encodeURIComponent(args.payment_id)}`);
  if (name === "mercado_pago_search_subscriptions") return mercadoPago("/preapproval/search", args);
  if (name === "mercado_pago_get_authorized_payments") return mercadoPago("/authorized_payments/search", { preapproval_id: args.subscription_id, limit: args.limit });
  throw new Error(`Ferramenta desconhecida: ${name}`);
}

let buffer = Buffer.alloc(0);
process.stdin.on("data", async (chunk) => {
  buffer = Buffer.concat([buffer, chunk]);
  while (true) {
    const separator = buffer.indexOf("\r\n\r\n");
    if (separator < 0) return;
    const header = buffer.subarray(0, separator).toString();
    const match = header.match(/Content-Length:\s*(\d+)/i);
    if (!match) { buffer = buffer.subarray(separator + 4); continue; }
    const length = Number(match[1]);
    const start = separator + 4;
    if (buffer.length < start + length) return;
    const message = JSON.parse(buffer.subarray(start, start + length).toString());
    buffer = buffer.subarray(start + length);
    if (message.method === "notifications/initialized") continue;
    if (message.method === "initialize") {
      jsonRpc({ jsonrpc: "2.0", id: message.id, result: { protocolVersion: message.params?.protocolVersion || "2024-11-05", capabilities: { tools: {} }, serverInfo: { name: "fokuscloud-mercado-pago", version: "1.0.0" } } });
      continue;
    }
    if (message.method === "tools/list") {
      jsonRpc({ jsonrpc: "2.0", id: message.id, result: { tools } });
      continue;
    }
    if (message.method === "tools/call") {
      try {
        const result = await callTool(message.params?.name, message.params?.arguments);
        jsonRpc({ jsonrpc: "2.0", id: message.id, result: { content: [{ type: "text", text: JSON.stringify(result, null, 2) }] } });
      } catch (error) {
        jsonRpc({ jsonrpc: "2.0", id: message.id, result: { isError: true, content: [{ type: "text", text: error.message }] } });
      }
      continue;
    }
    if (message.id !== undefined) jsonRpc({ jsonrpc: "2.0", id: message.id, error: { code: -32601, message: `Método não suportado: ${message.method}` } });
  }
});
