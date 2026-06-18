/**
 * execution/middleware/roles.js
 * Tier 3 — Middleware otorisasi berbasis role.
 * Cek apakah role user boleh mengakses endpoint tertentu.
 * Selalu dipasang SETELAH requireAuth.
 *
 * Hierarki role (dari tertinggi ke terendah):
 *   super_admin > admin > kepsek > guru_bk > guru_mapel > siswa/ketua_pkl
 */

// Urutan hierarki — role dengan indeks lebih rendah punya akses lebih tinggi
const ROLE_HIERARCHY = [
  'super_admin',
  'admin',
  'kepsek',
  'guru_bk',
  'guru_mapel',
  'ketua_pkl',
  'siswa',
];

/**
 * Cek apakah role user memiliki level >= role yang dibutuhkan.
 * Contoh: requireRole('guru_bk') → admin, kepsek, guru_bk boleh masuk, guru_mapel/siswa tidak.
 */
export function requireRole(...allowedRoles) {
  return (req, res, next) => {
    if (!req.user) {
      return res.status(401).json({ error: 'Belum login.' });
    }

    const userRole = req.user.role;
    const isSuperAdmin = req.user.isSuperAdmin === true;

    // Super admin selalu lolos apapun
    if (isSuperAdmin || userRole === 'super_admin') {
      return next();
    }

    if (allowedRoles.includes(userRole)) {
      return next();
    }

    return res.status(403).json({
      error: `Akses ditolak. Halaman ini hanya untuk: ${allowedRoles.join(', ')}.`,
      yourRole: userRole,
    });
  };
}

/**
 * Shortcut: hanya super_admin
 */
export const onlySuperAdmin = requireRole('super_admin');

/**
 * Shortcut: admin ke atas (super_admin + admin)
 */
export const onlyAdmin = requireRole('super_admin', 'admin');

/**
 * Shortcut: semua guru dan admin (bukan siswa)
 */
export const onlyStaff = requireRole('super_admin', 'admin', 'kepsek', 'guru_bk', 'guru_mapel');

/**
 * Shortcut: guru BK, admin, dan atas (yang boleh input pelanggaran)
 */
export const onlyBK = requireRole('super_admin', 'admin', 'kepsek', 'guru_bk');

/**
 * Shortcut: semua yang login (termasuk siswa)
 */
export const anyLoggedIn = (req, res, next) => {
  if (!req.user) {
    return res.status(401).json({ error: 'Belum login.' });
  }
  next();
};

/**
 * Helper: cek apakah user adalah pemilik data (phone match) atau staff.
 * Digunakan di endpoint yang data-nya bisa diakses pemilik ATAU staff.
 */
export function isSelfOrStaff(req, ownerPhone) {
  if (!req.user) return false;
  if (req.user.isSuperAdmin) return true;
  const staffRoles = ['super_admin', 'admin', 'kepsek', 'guru_bk', 'guru_mapel'];
  if (staffRoles.includes(req.user.role)) return true;
  return req.user.phone === ownerPhone;
}
