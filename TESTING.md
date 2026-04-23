# Ticko-Plug — Local Testing Setup Guide

A step-by-step guide to get the platform running end-to-end on your machine with all payment gateways in sandbox mode.

---

## 1. Prerequisites

| Tool | Version | Notes |
|------|---------|-------|
| PHP | 8.2+ | Extensions: `ext-gd`, `ext-mbstring`, `ext-pdo_mysql`, `ext-redis`, `ext-zip` |
| Composer | 2.x | `composer --version` |
| Node.js | 18+ LTS | `node --version` |
| MySQL | 8.0+ | Or MariaDB 10.6+ |
| Redis | 7.x | Optional; use `QUEUE_CONNECTION=database` to skip |
| ngrok | Latest | Required for M-Pesa webhook callbacks |

---

## 2. Clone & Install Dependencies

```bash
cd ~/Desktop/event-ticketing

# PHP dependencies
composer install

# Frontend dependencies
npm install
```

---

## 3. Environment Configuration

Copy the example file and fill in every section below:

```bash
cp .env.example .env   # if you have one, otherwise edit .env directly
php artisan key:generate
```

### 3.1 Core App Settings

```env
APP_NAME="Ticko-Plug"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Africa/Nairobi
```

### 3.2 Database

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tickoplug        # create this DB first: CREATE DATABASE tickoplug;
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3.3 Queue (choose one)

**Option A — Database queue (easiest, no Redis needed):**
```env
QUEUE_CONNECTION=database
```

**Option B — Redis (recommended for production-like testing):**
```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
```

### 3.4 Cache & Session

```env
CACHE_STORE=redis          # or "database" if no Redis
SESSION_DRIVER=database    # or "redis"
SESSION_LIFETIME=120
```

### 3.5 Email — Mailtrap (Free)

1. Sign up at https://mailtrap.io → Inboxes → SMTP Settings → Laravel
2. Copy the credentials:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_FROM_ADDRESS=noreply@tickoplug.test
MAIL_FROM_NAME="Ticko-Plug"
```

### 3.6 Africa's Talking — SMS & WhatsApp (Sandbox)

1. Register at https://africastalking.com → Sandbox
2. Create an app → copy API key

```env
AFRICASTALKING_USERNAME=sandbox
AFRICASTALKING_API_KEY=your_sandbox_api_key
```

> **Sandbox note:** SMS will appear in the AT Simulator at https://simulator.africastalking.com — not real phones.

### 3.7 M-Pesa Daraja — STK Push (Sandbox)

1. Go to https://developer.safaricom.co.ke → Create App
2. Enable **Lipa Na M-Pesa Sandbox**
3. Get your credentials from the "Keys" tab

```env
MPESA_ENV=sandbox
MPESA_CONSUMER_KEY=your_consumer_key
MPESA_CONSUMER_SECRET=your_consumer_secret
MPESA_SHORTCODE=174379                    # Safaricom test shortcode
MPESA_PASSKEY=bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919
MPESA_STK_CALLBACK_URL=https://YOUR_NGROK_URL/webhooks/mpesa/stk
MPESA_C2B_VALIDATION_URL=https://YOUR_NGROK_URL/webhooks/mpesa/c2b/validate
MPESA_C2B_CONFIRMATION_URL=https://YOUR_NGROK_URL/webhooks/mpesa/c2b/confirm
```

**Setting up ngrok for M-Pesa callbacks:**

```bash
# Install ngrok: https://ngrok.com/download
ngrok http 8000

# Copy the HTTPS URL (e.g., https://abc123.ngrok.io)
# Replace YOUR_NGROK_URL in the .env values above
```

> **Sandbox test phone:** Use `254708374149` as buyer phone — Daraja always returns success for this number.
> **Sandbox test PIN:** Any 4-digit PIN works in the STK prompt simulator.

### 3.8 Flutterwave — Card Payments (Test Mode)

1. Sign up at https://app.flutterwave.com → Settings → API Keys
2. Toggle to **Test Mode**

```env
FLUTTERWAVE_PUBLIC_KEY=FLWPUBK_TEST-xxxxxxxxxxxxxxxxxxxx-X
FLUTTERWAVE_SECRET_KEY=FLWSECK_TEST-xxxxxxxxxxxxxxxxxxxx-X
FLUTTERWAVE_ENCRYPTION_KEY=your_encryption_key
FLUTTERWAVE_WEBHOOK_HASH=a_secret_string_you_choose   # set same in Flutterwave dashboard
```

**Test card numbers:**

| Network | Card Number | CVV | Expiry | OTP |
|---------|-------------|-----|--------|-----|
| Visa | `4187427415564246` | `828` | `09/32` | `12345` |
| Mastercard | `5531886652142950` | `564` | `09/32` | `12345` |
| Verve | `5061460410120223210` | `780` | `12/31` | `3310` |

### 3.9 Platform Fee Settings

```env
PLATFORM_FEE_PERCENT=3.5
PLATFORM_FEE_FLAT=0
PLATFORM_FEE_CAP=500        # KES — max fee per order
```

---

## 4. Database Setup

```bash
php artisan migrate --seed
```

This creates all tables and seeds:
- Roles: `super_admin`, `admin`, `organizer`, `attendee`
- 1 super admin user
- Sample categories
- Sample events

**Default admin credentials** (check `database/seeders/AdminSeeder.php`):
```
Email:    admin@tickoplug.test
Password: password
```

---

## 5. Start All Services

Open **4 separate terminals:**

```bash
# Terminal 1 — Laravel web server
php artisan serve

# Terminal 2 — Vite (CSS/JS hot reload)
npm run dev

# Terminal 3 — Queue worker (ticket generation, SMS, emails)
php artisan queue:work --queue=default --tries=3

# Terminal 4 — ngrok tunnel (for M-Pesa callbacks)
ngrok http 8000
```

---

## 6. Access the Platform

| URL | Description |
|-----|-------------|
| http://localhost:8000 | Public site |
| http://localhost:8000/admin | Admin panel (super_admin / admin) |
| http://localhost:8000/manage | Organizer panel |
| http://localhost:8000/sitemap.xml | Auto-generated sitemap |
| http://localhost:8000/robots.txt | robots.txt |

---

## 7. Test the Full Purchase Flow

1. **Browse** → http://localhost:8000 → click any event
2. **Select tickets** → choose quantity → click "Get Tickets"
3. **Fill details** → buyer name, email, Kenyan phone number (`0712345678`)
4. **M-Pesa payment** → enter `0708374149` (Daraja test number) → submit
5. **Processing page** → polls every 3s waiting for callback
6. **Simulate M-Pesa callback** (since Daraja won't auto-fire locally):

```bash
curl -X POST http://localhost:8000/webhooks/mpesa/stk \
  -H "Content-Type: application/json" \
  -d '{
    "Body": {
      "stkCallback": {
        "MerchantRequestID": "...",
        "CheckoutRequestID": "ws_CO_...",
        "ResultCode": 0,
        "ResultDesc": "The service request is processed successfully.",
        "CallbackMetadata": {
          "Item": [
            {"Name": "Amount", "Value": 500},
            {"Name": "MpesaReceiptNumber", "Value": "QKJ12ABC45"},
            {"Name": "TransactionDate", "Value": 20260315120000},
            {"Name": "PhoneNumber", "Value": 254708374149}
          ]
        }
      }
    }
  }'
```

> Replace `CheckoutRequestID` with the value stored in your Redis/cache for the test order.

7. **Confirmation** → tickets emailed + SMS sent → download PDF
8. **Check-in** → http://localhost:8000/scan/{event-slug} → scan QR from email

---

## 8. Run Tests

```bash
# All tests
php artisan test

# With coverage
php artisan test --coverage

# Specific test file
php artisan test --filter=CheckoutTest
```

---

## 9. Production Checklist

Before going live, ensure:

- [ ] `APP_ENV=production` and `APP_DEBUG=false`
- [ ] `MPESA_ENV=production` with live Daraja credentials
- [ ] Replace ngrok URLs with your real HTTPS domain
- [ ] Set `SESSION_SECURE_COOKIE=true` (HTTPS only)
- [ ] Configure Supervisor to keep queue workers alive
- [ ] Set up Redis for cache/sessions/queues
- [ ] Configure proper CORS on API routes if using external scanners
- [ ] Set `FLUTTERWAVE_WEBHOOK_HASH` to a strong random secret (same in Flutterwave dashboard)
- [ ] Schedule `sitemap:regenerate --ping` in server cron:
  ```
  * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
  ```
- [ ] Run `php artisan optimize` after deployment:
  ```bash
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  php artisan optimize
  ```
