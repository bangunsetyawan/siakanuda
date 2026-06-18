import http from 'http';
import fs from 'fs';
import path from 'path';

const PORT = 8080;
const PUBLIC_DIR = path.join(process.cwd(), 'public');

const MIME_TYPES = {
    '.html': 'text/html',
    '.css': 'text/css',
    '.js': 'text/javascript',
    '.png': 'image/png',
    '.jpg': 'image/jpeg',
    '.gif': 'image/gif',
    '.svg': 'image/svg+xml',
    '.json': 'application/json',
    '.ico': 'image/x-icon'
};

const server = http.createServer((req, res) => {
    let rawUrl = req.url.split('?')[0];
    
    // Redirect / to /bkk/index.html
    if (rawUrl === '/' || rawUrl === '/bkk' || rawUrl === '/bkk/') {
        res.writeHead(302, { 'Location': '/bkk/index.html' });
        res.end();
        return;
    }

    let filePath = path.join(PUBLIC_DIR, rawUrl);
    
    // Check path traversal
    if (!filePath.startsWith(PUBLIC_DIR)) {
        res.statusCode = 403;
        res.end('Access Denied');
        return;
    }

    fs.stat(filePath, (err, stats) => {
        if (err || !stats.isFile()) {
            res.statusCode = 404;
            res.setHeader('Content-Type', 'text/plain');
            res.end('404 Not Found');
            return;
        }

        fs.readFile(filePath, (err, data) => {
            if (err) {
                res.statusCode = 500;
                res.end('Server Error');
                return;
            }
            const ext = path.extname(filePath);
            res.setHeader('Content-Type', MIME_TYPES[ext] || 'application/octet-stream');
            res.end(data);
        });
    });
});

server.listen(PORT, () => {
    console.log(`Server BKK running at http://localhost:${PORT}/bkk/index.html`);
});
