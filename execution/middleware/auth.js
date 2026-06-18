/**
 * execution/middleware/auth.js
 * Tier 3 — Middleware autentikasi JWT.
 * Verifikasi token di setiap request ke endpoint yang dilindungi.
 * Jika token valid, inject req.user = { phone, role, name, isSuperAdmin }
 */

import jwt from 'jsonwebtoken';
import { useSupabase } from '../db.js';

const JWT_SECRET = process.env.JWT_SECRET || 'siakanuda_secret_key_smk_nu_darussalam';

/**
 * Middleware utama — wajib dipasang di semua endpoint yang butuh login.
 * Jika token tidak ada atau tidak valid → 401 Unauthorized.
 */
export function requireAuth(req, res, next) {
  // Jika dalam mode SQLite lokal (Supabase tidak terkonfigurasi), bypass otentikasi untuk testing lokal
  if (!useSupabase) {
    req.user = {
      phone: 'admin',
      role: 'admin',
      name: 'Local Admin',
      isSuperAdmin: true
    };
    return next();
  }

  const authHeader = req.headers['authorization'];
  const token = authHeader && authHeader.startsWith('Bearer ')
    ? authHeader.slice(7)
    : req.cookies?.siakanuda_token;

  if (!token) {
    return res.status(401).json({ error: 'Akses ditolak. Silakan login terlebih dahulu.' });
  }

  try {
    const decoded = jwt.verify(token, JWT_SECRET);
    req.user = decoded; // { phone, role, name, isSuperAdmin, iat, exp }
    next();
  } catch (err) {
    if (err.name === 'TokenExpiredError') {
      return res.status(401).json({ error: 'Sesi login sudah habis. Silakan login ulang.', expired: true });
    }
    return res.status(401).json({ error: 'Token tidak valid. Silakan login ulang.' });
  }
}

/**
 * Generate JWT token baru setelah login berhasil.
 * Berlaku selama 8 jam (satu hari kerja).
 */
export function generateToken(payload) {
  return jwt.sign(payload, JWT_SECRET, { expiresIn: '8h' });
}

/**
 * Middleware opsional — cek token jika ada, tapi tidak wajib.
 * Digunakan untuk endpoint semi-publik (misal: /api/status).
 */
export function optionalAuth(req, res, next) {
  const authHeader = req.headers['authorization'];
  const token = authHeader && authHeader.startsWith('Bearer ')
    ? authHeader.slice(7)
    : null;

  if (token) {
    try {
      req.user = jwt.verify(token, JWT_SECRET);
    } catch (_) {
      req.user = null;
    }
  }
  next();
}
