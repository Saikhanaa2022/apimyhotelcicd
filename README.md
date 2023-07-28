# RMS API aka api-myhotel
> api.myhotel.mn домайн дээр байгаа API код

# AWS DEPLOY LINK
> https://docs.google.com/document/d/1qHpKt1kjMSgfuEDdo9cfGh8NwdIy2sQG

# SYSTEM MIN REQUIREMENT
> NGINX, PHP 7.4, Composer

# APP FRAMEWORK
> Laravel
> https://laravel.com/

# NGINX FIREWALL SETUP
```bash
$ sudo ufw allow "Nginx Full"
$ ufw allow OpenSSH
$ ufw enable
```

# UPGRADE SERVER PACKAGES
```bash
$ sudo apt update && sudo apt upgrade
```

# NGINX CERTBOT INSTALLATION
```bash
$ sudo apt install nginx certbot python3-certbot-nginx
```

# PHP 7.4 INSTALLATION
```
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update
sudo apt-get install -y php7.4 php7.4-cli php7.4-json php7.4-common php7.4-mysql php7.4-zip php7.4-gd php7.4-mbstring php7.4-curl php7.4-xml php7.4-bcmath php7.4-fpm
```

# INSTALL COMPOSER
```
cd ~
curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
HASH=`curl -sS https://composer.github.io/installer.sig`
echo $HASH

php -r "if (hash_file('SHA384', '/tmp/composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"

sudo php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
```

# NGINX SETUP
```bash
$ sudo nano /etc/nginx/sites-available/api.myhotel.mn
```
> Дээрх коммандыг дуудан доорх кодыг бичээд хадгална
```
server {
    listen 80;
    server_name server_domain_or_IP;
    root /var/www/travellist/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.html index.htm index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```
## CREATE LINK
```bash
$ sudo ln -s /etc/nginx/sites-available/api.myhotel.mn /etc/nginx/sites-enabled/
```
## BUILD THE APP
> Эхлээд апп рүүт дотор .env файл нэмэж өөрийн орчин бэлтгэх

```bash
$ composer install
$ php artisan passport:keys
```

# RUN
```bash
$ systemctl restart nginx
```

# LOG USERNAME & PASSWORD
> very_basic_auth.php шалгах