/**
 * execution/bot.js
 * SIAKANUDA v1.20.9 — Sistem Informasi Akademik SMK NU Darussalam
 * Tier 3 — Baileys WhatsApp bot handler (Notification Only).
 * Manages connection, QR generation, and sending notifications.
 */

import makeWASocket, {
  useMultiFileAuthState,
  DisconnectReason,
  fetchLatestBaileysVersion,
  makeCacheableSignalKeyStore,
} from '@whiskeysockets/baileys';
import { Boom } from '@hapi/boom';
import pino from 'pino';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import * as db from './db.js';
import { useSupabaseAuthState } from './supabase-auth.js';
import QRCode from 'qrcode';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT      = path.join(__dirname, '..');
const SESSION_PATH = path.join(__dirname, '..', 'sessions');

// Suppress verbose Baileys logs
const logger = pino({ level: 'silent' });

let sock = null;
let io = null;
let isConnected = false;

// --- Message Queue System ---
const messageQueue = [];
let isProcessingQueue = false;

async function processMessageQueue() {
  if (isProcessingQueue || messageQueue.length === 0) return;
  isProcessingQueue = true;

  while (messageQueue.length > 0) {
    const task = messageQueue[0];
    
    // Check if bot is connected
    if (!sock || !isConnected) {
      console.log(`[BOT] Cannot process queue, bot disconnected. Retrying in 10s...`);
      break;
    }

    try {
      await sendMessage(task.phone, task.text);
      console.log(`[BOT] Queue processed for ${task.phone}`);
    } catch (err) {
      console.error(`[BOT] Failed to process queue message for ${task.phone}:`, err.message);
    }

    // Remove the processed task
    messageQueue.shift();

    // Delay between 12-20 seconds (anti-spam WA)
    const delay = Math.floor(Math.random() * (20000 - 12000 + 1) + 12000);
    await new Promise(resolve => setTimeout(resolve, delay));
  }

  isProcessingQueue = false;
}

// Check queue periodically if it was paused due to disconnect
setInterval(() => {
  if (messageQueue.length > 0 && !isProcessingQueue && isConnected) {
    processMessageQueue();
  }
}, 10000);

export function queueMessage(phone, text) {
  if (!phone || !text) return;
  messageQueue.push({ phone, text });
  console.log(`[BOT] Added message to queue for ${phone}. Queue length: ${messageQueue.length}`);
  
  if (!isProcessingQueue && isConnected) {
    processMessageQueue();
  }
}


// --- Message Retry Cache for E2E Group Decryption Fix ---
if (!global.messageRetryCache) {
  global.messageRetryCache = new Map();
}
function cacheMessage(keyId, message) {
  if (!global.messageRetryCache) global.messageRetryCache = new Map();
  global.messageRetryCache.set(keyId, message);
  if (global.messageRetryCache.size > 1000) {
    const firstKey = global.messageRetryCache.keys().next().value;
    global.messageRetryCache.delete(firstKey);
  }
}

export function setSocketIO(socketIo) {
  io = socketIo;
}

export function setSock(s) {
  sock = s;
}

/** Get current connection status */
export function getBotStatus() {
  return {
    connected: isConnected,
    phone: sock?.user?.id,
    name:  sock?.user?.name,
  };
}

export function getSockInstance() {
  return sock;
}

export async function startBot() {
  let authState;
  if (process.env.SUPABASE_URL && process.env.SUPABASE_KEY && process.env.USE_LOCAL_AUTH !== 'true') {
    console.log('[BOT] Menggunakan Supabase Auth State');
    const { createClient } = await import('@supabase/supabase-js');
    const supabase = createClient(process.env.SUPABASE_URL, process.env.SUPABASE_KEY);
    authState = await useSupabaseAuthState(supabase);
  } else {
    console.log('[BOT] Menggunakan Local File Auth State');
    if (!fs.existsSync(SESSION_PATH)) {
      fs.mkdirSync(SESSION_PATH, { recursive: true });
    }
    authState = await useMultiFileAuthState(SESSION_PATH);
  }
  const { state, saveCreds } = authState;
  const { version } = await fetchLatestBaileysVersion();

  console.log(`[BOT] Using WA version: ${version.join('.')}`);

  sock = makeWASocket({
    version,
    logger,
    printQRInTerminal: false,
    auth: {
      creds: state.creds,
      keys: makeCacheableSignalKeyStore(state.keys, logger),
    },
    browser: ['SIAKANUDA', 'Chrome', '120.0'],
    syncFullHistory: false,
    markOnlineOnConnect: true,
    keepAliveIntervalMs: 10000,
    connectTimeoutMs: 30000,
    getMessage: async (key) => {
      if (global.messageRetryCache) {
        const msg = global.messageRetryCache.get(key.id);
        if (msg) {
          console.log(`[BOT] 🔄 Retrying message decryption for id: ${key.id}`);
          return msg;
        }
      }
      return undefined;
    }
  });

  // ── Connection Updates ──────────────────────────────────────────────────────
  sock.ev.on('connection.update', async (update) => {
    const { connection, lastDisconnect, qr } = update;

    if (qr) {
      console.log('[BOT] QR Code received');
      QRCode.toDataURL(qr, (err, url) => {
        if (!err && io) {
          io.emit('bot:qr', url);
        } else if (err) {
          console.error('[BOT] Gagal men-generate QR Code DataURL:', err.message);
        }
      });
    }

    if (connection === 'close') {
      isConnected = false;
      const reason = new Boom(lastDisconnect?.error)?.output?.statusCode;
      const shouldReconnect = reason !== DisconnectReason.loggedOut;
      console.log(`[BOT] Disconnected. Reason: ${reason}. Reconnect: ${shouldReconnect}`);
      if (io) io.emit('bot:status', { connected: false, reason });
      if (shouldReconnect) {
        console.log('[BOT] Reconnecting in 5s...');
        setTimeout(startBot, 5000);
      }
    }

    if (connection === 'open') {
      isConnected = true;
      console.log('[BOT] ✅ WhatsApp Connected!');
      const me = sock.user;
      if (io) io.emit('bot:status', { connected: true, phone: me?.id, name: me?.name });

    }
  });

  sock.ev.on('messages.upsert', async (m) => {
    if (m.type === 'notify') {
      for (const msg of m.messages) {
        if (!msg.key.fromMe && msg.message) {
          const rawJid = msg.key.remoteJid;
          const phone = rawJid.split('@')[0];
          const text = msg.message.conversation || msg.message.extendedTextMessage?.text || '';
          if (text) {
            await db.logMessage({ phone: rawJid, direction: 'in', message: text });
            if (io) {
              io.emit('bot:log', { 
                phone: rawJid, 
                direction: 'in', 
                message: text, 
                created_at: new Date().toISOString() 
              });
            }
          }
        }
      }
    }
  });

  sock.ev.on('creds.update', saveCreds);

  return sock;
}

/**
 * Send a message to a specific number (for manual sends from dashboard).
 */
export async function sendMessage(phone, text) {
  if (!sock || !isConnected) throw new Error('Bot not connected');
  
  // Format Indonesian phone numbers correctly (08... -> 628...)
  let jid = phone;
  const isGroup = phone.endsWith('@g.us');
  
  if (!isGroup) {
    // Extract base number without domain
    let baseNumber = phone.includes('@') ? phone.split('@')[0] : phone;
    
    // Replace leading '0' with '62' (Indonesia country code)
    if (baseNumber.startsWith('0')) {
      baseNumber = '62' + baseNumber.substring(1);
    }
    
    // Ensure it has the correct domain
    jid = `${baseNumber}@s.whatsapp.net`;
  }

  // Filter out non-numeric base phone numbers (like "superadmin", "admin", "siswa-test")
  const baseCheck = jid.split('@')[0];
  const isNumeric = /^\d+$/.test(baseCheck);
  if (!isNumeric) {
    console.log(`[BOT] ⚠️ Skipping sendMessage to non-numeric phone: "${phone}"`);
    return;
  }
  const sentMsg = await sock.sendMessage(jid, { text });
  if (sentMsg && sentMsg.key && sentMsg.key.id) {
    cacheMessage(sentMsg.key.id, sentMsg.message);
  }
  await db.logMessage({ phone: jid, direction: 'out', message: text });
  if (io) {
    io.emit('bot:log', { 
      phone: jid, 
      direction: 'out', 
      message: text, 
      created_at: new Date().toISOString() 
    });
  }
}

/**
 * Disconnect the bot gracefully.
 */
export async function stopBot() {
  if (sock) {
    await sock.logout();
    sock = null;
  }
}

export async function logoutSession() {
  console.log('[BOT] Logging out WhatsApp session...');
  if (sock) {
    try {
      await sock.logout();
    } catch (err) {
      console.log('[BOT] Error during sock.logout(), proceeding with manual cleanup:', err.message);
    }
    sock = null;
  }
  
  isConnected = false;

  // Clear local session directory
  if (fs.existsSync(SESSION_PATH)) {
    try {
      const files = fs.readdirSync(SESSION_PATH);
      for (const file of files) {
        fs.rmSync(path.join(SESSION_PATH, file), { recursive: true, force: true });
      }
      console.log('[BOT] Local session directory cleaned.');
    } catch (err) {
      console.error('[BOT] Gagal membersihkan folder session:', err.message);
    }
  }

  // Clear Supabase session if configured
  if (process.env.SUPABASE_URL && process.env.SUPABASE_KEY && process.env.USE_LOCAL_AUTH !== 'true') {
    try {
      const { createClient } = await import('@supabase/supabase-js');
      const supabase = createClient(process.env.SUPABASE_URL, process.env.SUPABASE_KEY);
      const { error } = await supabase.from('whatsapp_sessions').delete().neq('id', '');
      if (error) {
        console.error('[BOT] Gagal menghapus session Supabase:', error.message);
      } else {
        console.log('[BOT] Supabase session table cleared.');
      }
    } catch (err) {
      console.error('[BOT] Gagal membersihkan session Supabase:', err.message);
    }
  }

  // Notify client that we are offline
  if (io) {
    io.emit('bot:status', { connected: false, reason: 'loggedOut' });
    io.emit('bot:qr', null);
  }

  // Restart bot connection to await new scan
  console.log('[BOT] Restarting bot connection...');
  setTimeout(startBot, 2000);
}

/**
 * Fetch all groups that the bot currently participates in.
 */
export async function getParticipatingGroups() {
  if (!sock) throw new Error('Bot not connected');
  const groups = await sock.groupFetchAllParticipating();
  return Object.values(groups).map(g => ({
    id: g.id,
    subject: g.subject
  }));
}

/**
 * Join group via invite code / link
 */
export async function joinGroupByInvite(inviteLink) {
  if (!sock) throw new Error('Bot tidak terhubung');
  
  // Extract invite code
  const code = inviteLink.split('chat.whatsapp.com/').pop().trim().split('?')[0];
  if (!code) throw new Error('Format invite link tidak valid.');

  console.log(`[BOT] Joining group with invite code: ${code}`);
  const groupInfo = await sock.groupGetInviteInfo(code);
  
  try {
    await sock.groupAcceptInvite(code);
    console.log(`[BOT] Berhasil gabung grup: ${groupInfo.subject} (${groupInfo.id})`);
  } catch (err) {
    console.log(`[BOT] groupAcceptInvite note: ${err.message}`);
  }
  
  return {
    id: groupInfo.id,
    subject: groupInfo.subject
  };
}
