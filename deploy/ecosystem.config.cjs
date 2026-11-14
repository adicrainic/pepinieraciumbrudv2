// ==============================================================================
// PM2 Ecosystem Configuration for Pepiniera Ciumbrud
// Start with: pm2 start /var/www/pepinieraciumbrudV2/deploy/ecosystem.config.cjs
// Reload zero-downtime: pm2 reload ecosystem.config.cjs
// Save state: pm2 save
// ==============================================================================

module.exports = {
  apps: [
    {
      name: 'pepiniera-storefront',
      cwd: '/var/www/pepinieraciumbrudV2/pepinieraciumbrud-storefront',
      script: '.output/server/index.mjs',
      instances: 'max',       // Scales to all available CPU cores (cluster mode)
      exec_mode: 'cluster',
      autorestart: true,
      watch: false,
      max_memory_restart: '500M',
      env: {
        PORT: 3000,
        NITRO_PORT: 3000,
        NITRO_HOST: '127.0.0.1',
        NODE_ENV: 'production'
      }
    }
  ]
};

