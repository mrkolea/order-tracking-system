# Order Tracking System

> **⚠️ Important Notice**
>
> This project is **not ready for production** and is currently configured to work **only in DDEV environment**.
> Do not deploy to production without proper security hardening, environment configuration, and testing.

---

## Quick Start

### 1. Install DDEV

**macOS:**
```bash
brew install ddev/ddev/ddev
```

**Linux:**
```bash
curl -fsSL https://ddev.com/install.sh | bash
```

**Windows:**
Download from https://ddev.com/get-started/

Make sure Docker Desktop is installed and running.

### 2. Setup Project

```bash
# Clone the repository
git clone <repository-url>
cd order-tracking-system

# Copy environment file
cp environments/ddev.env .env

# Start DDEV
ddev start

# Install dependencies
ddev composer install

# Run migrations
ddev artisan migrate
```

### 3. Access the Application

- **Main URL**: https://order-tracking-system.ddev.site
- **Mailpit** (Email testing): https://order-tracking-system.ddev.site:8026

---

## Common Commands

```bash
# Start/stop DDEV
ddev start
ddev stop
ddev restart

# Artisan commands
ddev artisan migrate
ddev artisan test
ddev artisan queue:work
ddev artisan tinker

# Composer
ddev composer install
ddev composer update

# View logs
ddev logs -f

# Database access
ddev mysql
```

---

## API Endpoints

- `GET /api/orders` - List orders
- `POST /api/orders` - Create order
- `GET /api/orders/{order_number}` - Get order
- `PUT /api/orders/{order_number}` - Update order
- `DELETE /api/orders/{order_number}` - Delete order

### Postman Collection

Import `ots_api_collection.json` into Postman to see working examples of all API endpoints.

---

