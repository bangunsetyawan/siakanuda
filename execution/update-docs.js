#!/usr/bin/env node
/**
 * update-docs.js — Auto-sinkronisasi SEMUA dokumentasi SIAKANUDA
 * 
 * Cara pakai: npm run docs   (atau: node execution/update-docs.js)
 * 
 * Yang disinkronkan:
 *   1. STATUS_FITUR.md  → header, commit stats, changelog 25 terakhir, footer
 *   2. AI_CONTEXT.md    → versi, tanggal, jumlah controller/file/tabel
 *   3. package.json     → validasi konsistensi versi
 *   4. server.js        → validasi konsistensi versi API
 * 
 * Yang TIDAK diubah (manual oleh developer/AI agent):
 *   - Checklist fitur di STATUS_FITUR.md
 *   - Isi konten AI_CONTEXT.md (kecuali header & angka)
 *   - AGENTS.md (aturan kerja)
 */

import { execSync } from 'child_process';
import { readFileSync, writeFileSync, existsSync, readdirSync, statSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
const ROOT = join(__dirname, '..');
const STATUS_FILE = join(ROOT, 'docs', 'STATUS_FITUR.md');
const AI_CONTEXT_FILE = join(ROOT, 'docs', 'AI_CONTEXT.md');
const PKG_FILE = join(ROOT, 'package.json');
const SERVER_FILE = join(ROOT, 'execution', 'server.js');

// ============================================================
// Git helpers
// ============================================================

let _gitPath = null;

function findGit() {
  if (_gitPath) return _gitPath;

  // 1. Coba git dari PATH
  try {
    execSync('git --version', { cwd: ROOT, stdio: 'pipe' });
    _gitPath = 'git';
    return _gitPath;
  } catch { /* not in PATH */ }

  // 2. Cari dari GitHub Desktop (Windows)
  const localAppData = process.env.LOCALAPPDATA || '';
  if (localAppData) {
    const ghDesktop = join(localAppData, 'GitHubDesktop');
    if (existsSync(ghDesktop)) {
      try {
        const apps = readdirSync(ghDesktop).filter(d => d.startsWith('app-')).sort().reverse();
        for (const app of apps) {
          const gitPath = join(ghDesktop, app, 'resources', 'app', 'git', 'cmd', 'git.exe');
          if (existsSync(gitPath)) { _gitPath = gitPath; return _gitPath; }
        }
      } catch { /* ignore */ }
    }
  }

  // 3. Coba path umum Windows
  for (const p of [
    join(process.env.ProgramFiles || '', 'Git', 'cmd', 'git.exe'),
    join(process.env['ProgramFiles(x86)'] || '', 'Git', 'cmd', 'git.exe'),
  ]) {
    if (existsSync(p)) { _gitPath = p; return _gitPath; }
  }

  throw new Error(
    'Git tidak ditemukan! Pastikan git terinstal atau GitHub Desktop aktif.\n' +
    'Download: https://git-scm.com/download/win'
  );
}

function git(args) {
  const gitCmd = findGit();
  const fullCmd = gitCmd === 'git' ? `git ${args}` : `"${gitCmd}" ${args}`;
  return execSync(fullCmd, { cwd: ROOT, encoding: 'utf8', maxBuffer: 10 * 1024 * 1024 }).trim();
}

// ============================================================
// Counting helpers (auto-detect actual file counts)
// ============================================================

function countFiles(dir, ext = null) {
  if (!existsSync(dir)) return 0;
  let count = 0;
  for (const entry of readdirSync(dir)) {
    const full = join(dir, entry);
    try {
      const stat = statSync(full);
      if (stat.isFile() && (!ext || entry.endsWith(ext))) count++;
    } catch { /* skip */ }
  }
  return count;
}

function countFilesRecursive(dir, ext = null) {
  if (!existsSync(dir)) return 0;
  let count = 0;
  for (const entry of readdirSync(dir)) {
    const full = join(dir, entry);
    try {
      const stat = statSync(full);
      if (stat.isFile() && (!ext || entry.endsWith(ext))) count++;
      else if (stat.isDirectory()) count += countFilesRecursive(full, ext);
    } catch { /* skip */ }
  }
  return count;
}

function countDirsRecursive(dir) {
  if (!existsSync(dir)) return 0;
  let count = 0;
  for (const entry of readdirSync(dir)) {
    const full = join(dir, entry);
    try {
      if (statSync(full).isDirectory()) {
        count++;
        count += countDirsRecursive(full);
      }
    } catch { /* skip */ }
  }
  return count;
}

// ============================================================
// Git data extraction
// ============================================================

function getGitStats() {
  const totalCommits = parseInt(git('rev-list --count HEAD'), 10);
  const branch = git('rev-parse --abbrev-ref HEAD');

  let tags = [];
  try { tags = git('tag --sort=creatordate').split('\n').filter(Boolean); } catch { /* */ }

  const lastCommitMsg = git('log -1 --format=%s');
  const lastCommitDate = git('log -1 --format=%ci');
  const firstCommitMsg = git('log --reverse --format=%s -1');

  let remote = 'Tidak ada (lokal only)';
  try {
    const r = git('remote -v');
    if (r.trim()) remote = r.split('\n')[0];
  } catch { /* */ }

  let gitUser = 'Unknown';
  try { gitUser = `${git('config user.name')} <${git('config user.email')}>`; } catch { /* */ }

  return { totalCommits, branch, tags, remote, gitUser, lastCommitMsg, lastCommitDate, firstCommitMsg };
}

function getCommitTypeCounts() {
  const lines = git('log --format=%s').split('\n');
  const counts = { feat: 0, fix: 0, docs: 0, chore: 0, refactor: 0, clean: 0, other: 0 };
  for (const line of lines) {
    const l = line.toLowerCase();
    if (l.startsWith('feat')) counts.feat++;
    else if (l.startsWith('fix')) counts.fix++;
    else if (l.startsWith('docs')) counts.docs++;
    else if (l.startsWith('chore')) counts.chore++;
    else if (l.startsWith('refactor')) counts.refactor++;
    else if (l.startsWith('clean')) counts.clean++;
    else counts.other++;
  }
  return counts;
}

function getRecentCommits(n = 25) {
  const SEP = '~SEP~';
  const DELIM = '~COMMIT~';
  const log = git(`log --format=${DELIM}%H${SEP}%s${SEP}%ci -${n}`);
  return log.split(DELIM).filter(Boolean).map(entry => {
    const parts = entry.split(SEP);
    return {
      hash: (parts[0] || '').trim().substring(0, 7),
      message: (parts[1] || '').trim(),
      date: (parts[2] || '').trim().substring(0, 10)
    };
  });
}

function getLatestVersion() {
  const commits = getRecentCommits(30);
  for (const c of commits) {
    const match = c.message.match(/v(\d+\.\d+\.\d+)/i);
    if (match) return `v${match[1]}`;
  }
  try {
    const pkg = JSON.parse(readFileSync(PKG_FILE, 'utf8'));
    return `v${pkg.version}`;
  } catch { return 'v?.?.?'; }
}

// ============================================================
// Formatting helpers
// ============================================================

const BULAN_PANJANG = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const BULAN_PENDEK = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];

function todayIndo() {
  const d = new Date();
  return `${d.getDate()} ${BULAN_PANJANG[d.getMonth()]} ${d.getFullYear()}`;
}

function dateShort(dateStr) {
  const d = new Date(dateStr);
  return `${d.getDate()} ${BULAN_PENDEK[d.getMonth()]}`;
}

// ============================================================
// Update STATUS_FITUR.md
// ============================================================

function updateStatusFile(stats, typeCounts, recentCommits, version) {
  if (!existsSync(STATUS_FILE)) {
    console.log('   ⏭️  STATUS_FITUR.md tidak ditemukan, skip.');
    return { updated: false };
  }

  let content = readFileSync(STATUS_FILE, 'utf8');
  const today = todayIndo();
  const changes = [];

  // 1. Update header
  const headerOld = content.match(/\*\*Versi:\*\* v[\d.]+/)?.[0];
  content = content.replace(
    /^> \*\*Versi:\*\* v[\d.]+ \| \*\*Tanggal:\*\* .+? \| \*\*Total Commit:\*\* \d+/m,
    `> **Versi:** ${version} | **Tanggal:** ${today} | **Total Commit:** ${stats.totalCommits}`
  );
  if (headerOld && !headerOld.includes(version)) changes.push(`Versi: ${headerOld.replace('**Versi:** ', '')} → ${version}`);

  // 2. Update Git summary table
  content = content.replace(/(\| \*\*Total Commit\*\* \|) .+?\|/, `$1 ${stats.totalCommits} |`);
  const tagsStr = stats.tags.length > 0 ? stats.tags.map(t => '`' + t + '`').join(', ') : 'Tidak ada';
  content = content.replace(/(\| \*\*Tags\*\* \|) .+?\|/, `$1 ${tagsStr} |`);
  const cleanMsg = stats.lastCommitMsg.replace(/^[^:]+:\s*/, '');
  content = content.replace(/(\| \*\*Commit Terakhir\*\* \|) .+?\|/, `$1 ${version} — ${cleanMsg} |`);

  // 3. Update commit type counts
  for (const [type, count] of [['feat', typeCounts.feat], ['fix', typeCounts.fix], ['docs', typeCounts.docs], ['chore', typeCounts.chore]]) {
    content = content.replace(new RegExp(`(\\| \`${type}\` \\|) ~?\\d+ (\\|)`), `$1 ~${count} $2`);
  }
  const miscCount = typeCounts.clean + typeCounts.refactor + typeCounts.other;
  content = content.replace(/(\| `clean` \/ `refactor` \|) ~?\d+ (\|)/, `$1 ~${miscCount} $2`);

  // 4. Update changelog section
  const MARKER_START = '<!-- AUTO_CHANGELOG_START -->';
  const MARKER_END = '<!-- AUTO_CHANGELOG_END -->';
  const changelogRows = recentCommits.map((c, i) =>
    `| ${i + 1} | \`${c.hash}\` | ${dateShort(c.date)} | ${c.message} |`
  );
  const changelogBlock = [MARKER_START, '', '| # | Hash | Tanggal | Pesan Commit |', '|---|------|---------|-------------|', ...changelogRows, '', MARKER_END].join('\n');

  if (content.includes(MARKER_START)) {
    const startIdx = content.indexOf(MARKER_START);
    const endIdx = content.indexOf(MARKER_END) + MARKER_END.length;
    content = content.substring(0, startIdx) + changelogBlock + content.substring(endIdx);
  } else {
    const insertPoint = content.indexOf('## 🔮 Rencana Selanjutnya');
    if (insertPoint !== -1) {
      const section = ['## 📝 Changelog — 25 Commit Terakhir (Auto-Generated)', '', '> ⚙️ Bagian ini di-generate otomatis oleh `npm run docs`. Jangan edit manual.', '', changelogBlock, '', '---', ''].join('\n');
      content = content.substring(0, insertPoint) + section + content.substring(insertPoint);
    }
  }

  // 5. Update footer
  content = content.replace(/Terakhir diperbarui: \*\*.+?\*\*/, `Terakhir diperbarui: **${today}**`);

  writeFileSync(STATUS_FILE, content, 'utf8');
  return { updated: true, changes };
}

// ============================================================
// Update AI_CONTEXT.md (header & counts only)
// ============================================================

function updateAiContext(version, actualCounts) {
  if (!existsSync(AI_CONTEXT_FILE)) {
    console.log('   ⏭️  AI_CONTEXT.md tidak ditemukan, skip.');
    return { updated: false };
  }

  let content = readFileSync(AI_CONTEXT_FILE, 'utf8');
  const today = todayIndo();
  const changes = [];

  // 1. Update header version & date
  // Pattern: "Terakhir diupdate: DD Bulan YYYY — vX.Y.Z"
  const headerMatch = content.match(/Terakhir diupdate: .+ — v[\d.]+/);
  if (headerMatch) {
    const old = headerMatch[0];
    const replacement = `Terakhir diupdate: ${today} — ${version}`;
    if (old !== replacement) {
      content = content.replace(old, replacement);
      changes.push(`Header: ${old.match(/v[\d.]+/)?.[0]} → ${version}`);
    }
  }

  // 2. Update counts (if pattern exists)
  // Controller count
  const ctrlPatterns = [/(\d+) controller/gi, /(\d+) Controller/g];
  for (const pattern of ctrlPatterns) {
    content = content.replace(pattern, (match, num) => {
      if (parseInt(num) !== actualCounts.controllers && parseInt(num) > 5) {
        changes.push(`Controllers: ${num} → ${actualCounts.controllers}`);
        return match.replace(num, String(actualCounts.controllers));
      }
      return match;
    });
  }

  // Execution file count
  content = content.replace(/(\d+) file utama/gi, (match, num) => {
    if (parseInt(num) !== actualCounts.executionFiles && parseInt(num) > 5) {
      changes.push(`Execution files: ${num} → ${actualCounts.executionFiles}`);
      return match.replace(num, String(actualCounts.executionFiles));
    }
    return match;
  });

  // Table count
  content = content.replace(/(\d+) tabel/gi, (match, num) => {
    const n = parseInt(num);
    if (n >= 14 && n <= 20 && n !== actualCounts.tables) {
      changes.push(`Tables: ${num} → ${actualCounts.tables}`);
      return match.replace(num, String(actualCounts.tables));
    }
    return match;
  });

  // 3. Update package.json version reference if mentioned
  content = content.replace(/version[:\s]+"?\d+\.\d+\.\d+"?/gi, (match) => {
    const verMatch = match.match(/\d+\.\d+\.\d+/);
    if (verMatch) {
      const cleanVer = version.replace('v', '');
      if (verMatch[0] !== cleanVer) {
        changes.push(`Package version ref: ${verMatch[0]} → ${cleanVer}`);
        return match.replace(verMatch[0], cleanVer);
      }
    }
    return match;
  });

  if (changes.length > 0) {
    writeFileSync(AI_CONTEXT_FILE, content, 'utf8');
  }

  return { updated: changes.length > 0, changes };
}

// ============================================================
// Validation — cross-check consistency
// ============================================================

function validateConsistency(version) {
  const warnings = [];

  // Check package.json version
  try {
    const pkg = JSON.parse(readFileSync(PKG_FILE, 'utf8'));
    const pkgVer = `v${pkg.version}`;
    if (pkgVer !== version) {
      warnings.push(`package.json: ${pkgVer} ≠ ${version}`);
    }
  } catch { /* skip */ }

  // Check server.js API version
  try {
    const serverContent = readFileSync(SERVER_FILE, 'utf8');
    const apiVerMatch = serverContent.match(/version:\s*'([\d.]+)'/);
    if (apiVerMatch) {
      const apiVer = `v${apiVerMatch[1]}`;
      if (apiVer !== version) {
        warnings.push(`server.js API: ${apiVer} ≠ ${version}`);
      }
    }
  } catch { /* skip */ }

  // Check for hardcoded drive letters in active docs
  const activeFiles = ['AI_CONTEXT.md', 'AGENTS.md', 'STATUS_FITUR.md'];
  for (const file of activeFiles) {
    try {
      const content = readFileSync(join(ROOT, file), 'utf8');
      const driveMatches = content.match(/[A-Z]:\\Antigravity/gi);
      if (driveMatches) {
        warnings.push(`${file}: ${driveMatches.length}× hardcoded drive letter ditemukan`);
      }
    } catch { /* skip */ }
  }

  return warnings;
}

// ============================================================
// Main
// ============================================================

console.log('');
console.log('📝 SIAKANUDA — Sinkronisasi Dokumentasi');
console.log('═══════════════════════════════════════\n');

try {
  // 1. Find git
  process.stdout.write('🔍 Mencari git... ');
  const gitPath = findGit();
  console.log(`✅ (${gitPath === 'git' ? 'PATH' : 'GitHub Desktop'})\n`);

  // 2. Gather data
  console.log('📊 Mengambil data...');
  const stats = getGitStats();
  const typeCounts = getCommitTypeCounts();
  const recentCommits = getRecentCommits(25);
  const version = getLatestVersion();

  // Count actual files
  const controllersDir = join(ROOT, 'dashboard', 'app', 'Controllers');
  const executionDir = join(ROOT, 'execution');
  const viewsDir = join(ROOT, 'dashboard', 'app', 'Views');
  
  const actualCounts = {
    controllers: countFiles(controllersDir, '.php'),
    executionFiles: countFiles(executionDir, '.js'),
    views: countFilesRecursive(viewsDir, '.php'),
    viewDirs: countDirsRecursive(viewsDir),
    tables: 16, // SQLite tables (hardcoded, auto-count would require DB access)
  };

  console.log(`   Root        : ${ROOT}`);
  console.log(`   Branch      : ${stats.branch} | ${stats.totalCommits} commits | ${version}`);
  console.log(`   Tags        : ${stats.tags.length} (${stats.tags.join(', ') || '-'})`);
  console.log(`   Controllers : ${actualCounts.controllers} | Execution: ${actualCounts.executionFiles} | Views: ${actualCounts.views}`);
  console.log(`   Commit tipe : feat=${typeCounts.feat} fix=${typeCounts.fix} docs=${typeCounts.docs} chore=${typeCounts.chore}`);
  console.log('');

  // 3. Update STATUS_FITUR.md
  process.stdout.write('📄 STATUS_FITUR.md... ');
  const statusResult = updateStatusFile(stats, typeCounts, recentCommits, version);
  if (statusResult.updated) {
    console.log('✅');
    for (const c of statusResult.changes || []) console.log(`   ↳ ${c}`);
  } else {
    console.log('⏭️ skip');
  }

  // 4. Update AI_CONTEXT.md
  process.stdout.write('📄 AI_CONTEXT.md... ');
  const aiResult = updateAiContext(version, actualCounts);
  if (aiResult.updated) {
    console.log('✅');
    for (const c of aiResult.changes) console.log(`   ↳ ${c}`);
  } else {
    console.log(aiResult.updated === false && existsSync(AI_CONTEXT_FILE) ? '✅ sudah sinkron' : '⏭️ skip');
  }

  // 5. Validate consistency
  console.log('');
  const warnings = validateConsistency(version);
  if (warnings.length > 0) {
    console.log('⚠️  Peringatan konsistensi:');
    for (const w of warnings) console.log(`   ⚠️  ${w}`);
  } else {
    console.log('✅ Semua dokumen sinkron!');
  }

  // 6. Summary
  console.log('');
  console.log('═══════════════════════════════════════');
  console.log('💡 Langkah selanjutnya:');
  console.log(`   git add AI_CONTEXT.md STATUS_FITUR.md`);
  console.log(`   git commit -m "docs(${version}): auto-sync dokumentasi"\n`);

} catch (err) {
  console.error(`\n❌ Error: ${err.message}`);
  process.exit(1);
}
