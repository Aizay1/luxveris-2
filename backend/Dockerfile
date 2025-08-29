FROM php:8.2-apache

# --- PHP extensions ---
RUN docker-php-ext-install mysqli pdo pdo_mysql

# --- Apache mods ---
RUN a2enmod rewrite headers

# --- PHP settings ---
RUN { \
    echo "upload_max_filesize=25M"; \
    echo "post_max_size=25M"; \
    echo "memory_limit=256M"; \
    echo "max_execution_time=120"; \
  } > /usr/local/etc/php/conf.d/uploads.ini

# --- Install Node.js & npm ---
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest

# --- Set working directory ---
WORKDIR /var/www/html

# --- Copy only package & Tailwind config files (for caching npm install) ---
COPY package.json package-lock.json* tailwind.config.js postcss.config.js ./

# --- Install Node modules ---
RUN npm install

# --- Copy all backend project files ---
COPY . /var/www/html/

# --- Tailwind build ---
    RUN npx tailwindcss -i ./backend/assets/input.css -o ./backend/assets/app.css --minify

# --- Ensure uploads directory exists & correct permissions ---
RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/uploads

EXPOSE 80
