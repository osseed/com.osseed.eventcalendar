FROM php:8.3-cli-bookworm

RUN apt-get update && apt-get install -y \
    git unzip curl ca-certificates \
    libnss3 libatk1.0-0 libatk-bridge2.0-0 \
    libx11-6 libx11-xcb1 libxcb1 libxcomposite1 \
    libxdamage1 libxext6 libxfixes3 libxrandr2 \
    libgbm1 libglib2.0-0 libasound2 \
    libpango-1.0-0 libcairo2 libatspi2.0-0 \
    libxshmfence1 libxkbcommon0 fonts-liberation \
    libxcursor1 \
    libgtk-3-0 \
    libgdk-pixbuf-2.0-0 \
    libpangocairo-1.0-0 \
    libcairo-gobject2 \
    && rm -rf /var/lib/apt/lists/*

COPY --from=node:22-bookworm-slim /usr/local/ /usr/local/
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /tests

ENV PLAYWRIGHT_BROWSERS_PATH=/ms-playwright

RUN mkdir -p /ms-playwright \
    && chmod 777 /ms-playwright
