# DigiStore

A catalog-first digital product store built with PHP and MySQL. Display premium accounts, source code, AI tools, templates, and more.

**English** | [Bahasa Indonesia](./README.id.md)

## Features

- **Catalog-First Design** — Products visible immediately on page load
- **Real-Time Search** — Find products by name or description instantly
- **Category Filtering** — Filter by product category
- **Sorting Options** — Sort by newest, price, rating, or popularity
- **Dark/Light Mode** — Theme preference saved to localStorage
- **Responsive Design** — Mobile, tablet, and desktop optimized
- **Featured Products** — Highlight best sellers and popular items
- **Stock Management** — Real-time stock display
- **Admin Dashboard** — Full CRUD for products, categories, and orders
- **Payment Integration** — Payment confirmation system ready

## Tech Stack

- **Backend**: PHP Native
- **Database**: MySQL
- **Frontend**: HTML, CSS, Vanilla JavaScript
- **Styling**: Tailwind CSS (CDN)
- **Icons**: Font Awesome 6
- **Fonts**: Plus Jakarta Sans, Sora

## Requirements

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Apache with mod_rewrite (or nginx config)
- Composer (optional, for development)

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd digital-store
```

### 2. Configure Environment

Copy the example environment file and update with your settings:

```bash
cp .env.example .env
```

Update these values in `.env`:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=digital_store
DB_USER=your_username
DB_PASS=your_password
DB_CHARSET=utf8mb4

APP_URL=http://localhost
APP_DEBUG=false

# Rate Limiting
RATE_LIMIT_ENABLED=true
RATE_LIMIT_MAX_REQUESTS=100
RATE_LIMIT_WINDOW_SECONDS=60
```

### 3. Create Database

Create a MySQL database and import the schema:

```bash
mysql -u your_username -p -e "CREATE DATABASE digital_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u your_username -p digital_store < database/schema.sql
mysql -u your_username -p digital_store < database/seed.sql
```

### 4. Configure Web Server

For Apache (`.htaccess` is already included):

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/digital-store
    <Directory /var/www/digital-store>
        AllowOverride All
        Require all granted
    </Directory>
</Directory>
</VirtualHost>
```

For nginx:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/digital-store;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 5. Cron Job Setup

Set up a cron job to expire unpaid orders (run every minute):

```bash
* * * * * php /path/to/digital-store/scripts/expire-orders.php
```

Or add to system crontab:

```bash
crontab -e
# Add this line:
* * * * * /usr/bin/php /path/to/digital-store/scripts/expire-orders.php
```

## Project Structure

```
digital-store/
├── index.php              # Main catalog page
├── checkout.php           # Checkout page
├── payment.php            # Payment page
├── product.php            # Product detail page
├── order-status.php       # Order status tracking
│
├── api/                   # Public API endpoints
│   ├── products.php       # GET products, filter by category
│   ├── categories.php     # GET all categories
│   ├── checkout.php       # POST create order
│   ├── orders.php         # GET/PATCH order status
│   ├── payment-confirmations.php
│   ├── settings.php
│   └── testimonials.php
│
├── dashboard/             # Admin panel
│   ├── index.php          # Dashboard home
│   ├── login.php          # Admin authentication
│   ├── products.php       # Product management (CRUD)
│   ├── categories.php     # Category management
│   ├── orders.php         # Order management
│   ├── testimonials.php   # Testimonial management
│   ├── settings.php       # Store settings
│   ├── settings-payment.php # Payment settings
│   └── api/               # Protected API (admin only)
│
├── config/                # Configuration files
│   ├── database.php       # Database connection
│   ├── env.php            # Environment loader
│   ├── rate-limit.php     # Rate limiting
│   ├── response.php       # API response helpers
│   └── security-headers.php # Security headers
│
├── database/              # SQL migrations
│   ├── schema.sql         # Database schema
│   ├── seed.sql           # Sample data
│   └── migrate-*.sql      # Additional migrations
│
├── includes/              # PHP includes
│   └── order-expiration.php
│
├── scripts/               # CLI scripts
│   └── expire-orders.php  # Order expiration script
│
└── assets/                # Static assets
    ├── css/
    └── js/
```

## Database Schema

### Main Tables

| Table | Description |
|-------|-------------|
| `products` | Digital products catalog |
| `categories` | Product categories |
| `orders` | Customer orders |
| `order_items` | Items within each order |
| `product_accounts` | Delivered account credentials |
| `testimonials` | Customer testimonials |
| `payment_confirmations` | Payment proof uploads |
| `store_settings` | Configuration values |
| `admin_users` | Admin accounts |

### API Endpoints

#### Public API (`/api/`)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/products.php` | GET | List products (supports `?category=`, `?search=`) |
| `/api/categories.php` | GET | List all categories |
| `/api/orders.php` | GET | Get order by ID |
| `/api/checkout.php` | POST | Create new order |
| `/api/testimonials.php` | GET | List approved testimonials |
| `/api/settings.php` | GET | Public store settings |

#### Dashboard API (`/dashboard/api/`)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/dashboard/api/orders.php` | GET | List all orders |
| `/dashboard/api/orders.php` | PATCH | Update order status |
| `/dashboard/api/products.php` | GET/POST | List/create products |
| `/dashboard/api/products.php` | GET/PUT/DELETE | Single product operations |

## Configuration

### Store Settings

Access via Dashboard → Settings:

- Store name
- Contact information
- WhatsApp number
- Social media links

### Payment Settings

Access via Dashboard → Settings → Payment:

- Payment method configuration
- Account numbers
- Payment instructions

### Rate Limiting

Configure in `.env`:

```env
RATE_LIMIT_ENABLED=true
RATE_LIMIT_MAX_REQUESTS=100
RATE_LIMIT_WINDOW_SECONDS=60
```

## Development

### Adding New Products

1. Log in to the dashboard
2. Navigate to Products
3. Click "Add Product"
4. Fill in product details:
   - Name, description, price
   - Category selection
   - Stock quantity
   - Featured status
   - Product accounts (comma-separated)

### Order Flow

1. Customer selects product → Checkout
2. Order created with "pending" status
3. Customer makes payment
4. Customer submits payment proof
5. Admin verifies payment → Status: "paid"
6. System auto-delivers product accounts → Status: "completed"

### Order Expiration

Unpaid orders expire automatically after the configured time:

- Cron job runs every minute
- Scans for pending orders past expiration
- Updates status to "expired"
- Frees reserved stock

## Security

- Password hashing with `password_hash()`
- Prepared statements (SQL injection prevention)
- Rate limiting on API endpoints
- CSRF token validation
- XSS prevention (output escaping)
- Security headers via `config/security-headers.php`

## License

Copyright © 2026 DigiStore. All rights reserved.