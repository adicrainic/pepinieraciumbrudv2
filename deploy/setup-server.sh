#!/usr/bin/env bash
# ==============================================================================
# Initial Server Provisioning Script for Ubuntu 22.04 / 24.04 LTS
# Pepiniera Ciumbrud V2
# Run as: sudo bash deploy/setup-server.sh
# ==============================================================================

set -e

if [ "$EUID" -ne 0 ]; then
  echo "❌ Please run as root: sudo bash deploy/setup-server.sh"
  exit 1
fi

echo "=========================================================="
echo "   🚀 Setting up Ubuntu Server for Pepiniera Ciumbrud"
echo "=========================================================="

# 1. Update system packages
apt update && apt upgrade -y

# 2. Install essential tools
apt install -y curl wget git unzip zip software-properties-common ufw

# Configure 2GB Swap (essential for smooth npm build on light 1GB/2GB RAM VPS)
if [ $(swapon --show | wc -l) -eq 0 ]; then
    echo "Creating 2GB swap space for npm build reliability..."
    fallocate -l 2G /swapfile || dd if=/dev/zero of=/swapfile bs=1M count=2048
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    echo '/swapfile none swap sw 0 0' >> /etc/fstab
fi

# 3. Install Nginx & MySQL Server
apt install -y nginx mysql-server
systemctl enable --now mysql

# 4. Install PHP 8.2 / 8.3 & extensions for WordPress + WooCommerce
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-curl php8.2-gd php8.2-mbstring \
               php8.2-xml php8.2-xmlrpc php8.2-soap php8.2-intl php8.2-zip php8.2-bcmath

# Adjust PHP settings for uploads and performance
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 64M/' /etc/php/8.2/fpm/php.ini
sed -i 's/post_max_size = .*/post_max_size = 64M/' /etc/php/8.2/fpm/php.ini
sed -i 's/memory_limit = .*/memory_limit = 256M/' /etc/php/8.2/fpm/php.ini
sed -i 's/max_execution_time = .*/max_execution_time = 120/' /etc/php/8.2/fpm/php.ini
systemctl restart php8.2-fpm

# 5. Install Composer (required for Phinx database migrations)
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
    php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
    rm -f /tmp/composer-setup.php
fi

# 6. Install Node.js 20 LTS & PM2
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs
npm install -g pm2

# 7. Install Certbot (Let's Encrypt SSL)
apt install -y certbot python3-certbot-nginx

# 8. Install WP-CLI (essential for search-replace and maintenance)
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
mv wp-cli.phar /usr/local/bin/wp

# 9. Configure Firewall (UFW)
ufw allow OpenSSH
ufw allow 'Nginx Full'
# ufw --force enable

echo ""
echo "=========================================================="
echo "   ✅ Base server installation complete!"
echo "   Node:     $(node -v)"
echo "   NPM:      $(npm -v)"
echo "   PHP:      $(php -v | head -n 1)"
echo "   Composer: $(composer --version --no-ansi | head -n 1)"
echo "   MySQL:    $(mysql --version)"
echo "   WP-CLI:   $(wp --info | head -n 1)"
echo "=========================================================="

