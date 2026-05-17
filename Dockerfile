# Use official PHP image with FrankenPHP
FROM dunglas/frankenphp:php8.3-bookworm

# Build cache invalidation - triggers fresh build (2026-05-15T15:30:00Z)
# Previous builds were using cached npm ci && npm prune command
# Updated to: install deps → build → prune (correct order for Vite)

# Set working directory
WORKDIR /app

# Install system dependencies and PHP extensions
RUN install-php-extensions \
    ctype \
    curl \
    dom \
    fileinfo \
    filter \
    gd \
    hash \
    mbstring \
    openssl \
    pcre \
    pdo \
    pdo_mysql \
    session \
    tokenizer \
    xml

# Install system packages
RUN apt-get update && apt-get install -y \
    ca-certificates \
    curl \
    gnupg \
    git \
    unzip \
    zip \
    && rm -rf /var/lib/apt/lists/*

# Install Node.js (required for npm/vite asset building)
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
    apt-get install -y nodejs && \
    rm -rf /var/lib/apt/lists/*

# Verify Node.js and npm installation
RUN node --version && npm --version

# Copy composer from composer image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-scripts --no-interaction

# Install all Node dependencies first (before pruning)
# This ensures Vite and other dev dependencies are available
RUN npm ci --verbose

# Build frontend assets with Vite
# Must run BEFORE npm prune, as Vite is a dev dependency
RUN npm run build --verbose

# Remove dev dependencies to reduce image size
# Now safe to remove since build is complete
RUN npm prune --omit=dev --ignore-scripts --verbose

# Create required directories
RUN mkdir -p storage/framework/{sessions,views,cache,testing} storage/logs bootstrap/cache && \
    chmod -R a+rw storage bootstrap/cache

# Cache Laravel configuration
RUN php artisan event:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Expose port
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/up || exit 1

# Start FrankenPHP
CMD ["frankenphp", "run", "--bind=0.0.0.0:80"]
