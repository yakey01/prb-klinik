// PM2 ecosystem config — jalankan: pm2 start ecosystem.config.js
// Dari wa-service/: pm2 start ecosystem.config.js
// Dari root:        pm2 start wa-service/ecosystem.config.js
const path = require('path');
const APP_ROOT = path.resolve(__dirname, '..');

module.exports = {
    apps: [
        {
            name: 'wa-klinik',
            script: './server.js',
            cwd: __dirname,
            instances: 1,
            autorestart: true,
            watch: false,
            max_memory_restart: '256M',
            restart_delay: 5000,
            env: {
                NODE_ENV: 'production',
                PORT: 3001,
                WA_SECRET: process.env.WA_SECRET || 'prb-klinik-secret-2024',
            },
            error_file: path.join(APP_ROOT, 'storage/logs/wa-service-error.log'),
            out_file:   path.join(APP_ROOT, 'storage/logs/wa-service-out.log'),
            log_date_format: 'YYYY-MM-DD HH:mm:ss',
        },
        {
            name: 'ws-deploy',
            script: path.join(APP_ROOT, 'ws-deploy/server.js'),
            cwd: path.join(APP_ROOT, 'ws-deploy'),
            instances: 1,
            autorestart: true,
            watch: false,
            max_memory_restart: '128M',
            restart_delay: 3000,
            env: {
                NODE_ENV: 'production',
                WS_DEPLOY_PORT: 3002,
                DEPLOY_SECRET: process.env.DEPLOY_SECRET || 'prb-deploy-secret-2024',
                APP_ROOT: APP_ROOT,
                SSH_HOST:    '153.92.8.132',
                SSH_PORT:    '65002',
                SSH_USER:    'u454362045',
                SSH_KEY:     path.join(process.env.HOME || '/root', '.ssh/id_ed25519'),
                REMOTE_ROOT: '/home/u454362045/domains/dokterkuklinik.com/public_html/apotik',
            },
            error_file: path.join(APP_ROOT, 'storage/logs/ws-deploy-error.log'),
            out_file:   path.join(APP_ROOT, 'storage/logs/ws-deploy-out.log'),
            log_date_format: 'YYYY-MM-DD HH:mm:ss',
        }
    ]
};
