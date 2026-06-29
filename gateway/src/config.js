import 'dotenv/config';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');

export const config = {
  port: parseInt(process.env.PORT ?? '3001', 10),
  // Shared secret: both directions (panel→gateway and gateway→panel) present it.
  token: process.env.GATEWAY_TOKEN ?? 'change-me',
  // Where the Laravel panel receives inbound webhooks.
  panelWebhookUrl: (process.env.PANEL_WEBHOOK_URL ?? 'http://127.0.0.1:8080/api/wa/inbound'),
  // Single-session creds live under the LARAVEL app's storage/wa so a persisted volume keeps the link.
  authDir: process.env.AUTH_DIR ?? path.resolve(root, '..', 'storage', 'wa'),
  logLevel: process.env.LOG_LEVEL ?? 'info',
  // Reconnect backoff ceiling (ms).
  maxReconnectDelay: 30_000,
  // Give up auto-reconnecting after this many consecutive failed attempts (avoids silent infinite loops).
  maxReconnectAttempts: parseInt(process.env.MAX_RECONNECT_ATTEMPTS ?? '20', 10),
};
