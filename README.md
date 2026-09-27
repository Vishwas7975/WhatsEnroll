# WhatsEnroll 🎓💬

> **WhatsApp-Based Automated Course Enrollment & Payment Platform**  
> A production-tested, pluggable Laravel template that automates student course discovery, payments via Razorpay, credential delivery, and enrollment mapping—all powered through WhatsApp.

---

> [!NOTE]
> **Status:** Fully functional and tested end-to-end (WhatsApp bot flow, payment capture, credential delivery, admin panel). The only remaining step is connecting your own Meta WhatsApp Business number — pending Meta's approval process on my end, but the integration code is complete and tested against Meta's API.

---

## 📖 Overview

**WhatsEnroll** is a reusable, general-purpose **WhatsApp course and service enrollment automation platform**. Instead of requiring learners to navigate complex checkout portals or manually coordinate bank transfer screenshots over chat, WhatsEnroll gives educational institutions, cohort-based course creators, and coaching academies a turnkey conversational sales and fulfillment engine:

1. **Conversational Catalog & Onboarding:** Prospective students initiate contact via WhatsApp. The automated bot handles language selection (multilingual support built-in: English, Hindi, Telugu), displays available courses, and captures learner details.
2. **Instant Payment Link & Webhooks:** Generates dynamic Razorpay payment links and delivers them directly into the chat session.
3. **Automated Verification & Receipt:** Automatically verifies Razorpay webhooks using HMAC-SHA256 signatures and handles manual UTR reconciliation when offline payments occur.
4. **Pluggable Backend Provisioning:** Immediately provisions course access via a pluggable LMS/CRM adapter (`PortalService`), saving credentials to the database.
5. **Multi-Channel Credential Delivery:** Dispatches login credentials, course dashboard links, and welcome confirmations instantly via WhatsApp and transactional email (Markdown-styled Blade templates).
6. **Unified Admin Panel:** Built-in web dashboard for staff to manage courses, review pending payments, inspect audit logs, and broadcast WhatsApp messages.

Anyone cloning this repository can plug in their own WhatsApp Business account, Razorpay credentials, course catalog, and custom enrollment backend to adapt it to their exact use case.

---

## 🛠️ Tech Stack

| Layer | Technology | Purpose |
|---|---|---|
| **Framework** | Laravel 11.x / 12.x (PHP 8.3+) | Modern backend framework, routing, queues, and security |
| **Database** | MySQL 8.0+ / MariaDB 10.4+ | Relational schema with migrations, foreign keys, and indexes |
| **Queue & Cache** | Redis + Laravel Horizon | Asynchronous background processing for WhatsApp messages, webhooks, and email dispatch |
| **WhatsApp API** | Meta WhatsApp Business Cloud API | Direct cloud webhook handling and interactive button/list messages |
| **Payment Gateway** | Razorpay (PHP SDK + Webhooks) | Secure automated payment links with webhook HMAC verification |
| **Authentication & RBAC**| Laravel Breeze + Spatie Permission | Secure admin dashboard access with role-based permissions (`super-admin`, `admin`, `support`) |
| **Frontend Styling** | Tailwind CSS + Alpine.js | Modern, responsive admin interface |

---

## 🛡️ Architecture & Security Highlights

- **Bot-First Self-Registration Model:** Public self-registration (`/register`) is intentionally disabled in `routes/auth.php`. End users interact strictly through WhatsApp. Admin and staff credentials can only be provisioned via environment-driven CLI seeders (`AdminSeeder`) or by existing super-admins.
- **Role-Based Admin Protection:** All `/admin/*` routes are protected by both authentication and role verification middleware (`['auth', 'role:admin|super-admin']`).
- **Cryptographic Signature Verification:**
  - **Meta Webhooks:** Every inbound payload received by `BotController::handle` is validated against Meta's `X-Hub-Signature-256` HMAC header using your secret `WHATSAPP_META_APP_SECRET`.
  - **Razorpay Webhooks:** Inbound payment webhooks are verified with HMAC-SHA256 against `RAZORPAY_WEBHOOK_SECRET` before updating payment statuses or triggering credential delivery.
- **Audit Logging:** Administrative approvals and rejections of manual payments are logged to the `admin_logs` table with admin IDs, timestamps, and contextual metadata.
- **Zero Hardcoded Secrets:** All API keys, tokens, and database passwords are read strictly from environment variables (`.env`).

---

## 🚀 Quick Start & Installation

### 1. Clone & Install Dependencies

```bash
git clone https://github.com/Vishwas7975/whatsenroll.git
cd whatsenroll

# Install PHP dependencies
composer install

# Install frontend dependencies and build assets
npm install
npm run build
```

### 2. Environment Configuration

Copy the example environment file and generate the application encryption key:

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and fill in your database, Redis, mail, and third-party API credentials:

```dotenv
APP_NAME=WhatsEnroll
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=whatsenroll
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Meta WhatsApp Cloud API
WHATSAPP_PHONE_NUMBER_ID=<YOUR_PHONE_NUMBER_ID>
WHATSAPP_BUSINESS_ACCOUNT_ID=<YOUR_BUSINESS_ACCOUNT_ID>
WHATSAPP_API_TOKEN=<YOUR_PERMANENT_SYSTEM_USER_ACCESS_TOKEN>
WHATSAPP_VERIFY_TOKEN=<YOUR_CUSTOM_VERIFY_TOKEN>
WHATSAPP_META_APP_SECRET=<YOUR_META_APP_SECRET>

# Razorpay Keys
RAZORPAY_KEY_ID=rzp_live_<YOUR_KEY_ID>
RAZORPAY_KEY_SECRET=<YOUR_KEY_SECRET>
RAZORPAY_WEBHOOK_SECRET=<YOUR_WEBHOOK_SECRET>

# Pluggable Enrollment Backend (Optional - see guide below)
PORTAL_API_URL=https://your-lms.com/api/enroll_student
PORTAL_API_KEY=<YOUR_PORTAL_API_KEY>

# Admin Seeder
ADMIN_SEED_EMAIL=admin@your-domain.com
ADMIN_SEED_PASSWORD=YourSecurePassword123!
```

### 3. Run Migrations & Seed Admin User

```bash
# Run database migrations
php artisan migrate

# Seed roles and your initial super-admin account
php artisan db:seed --class=AdminSeeder
```

> [!IMPORTANT]
> `AdminSeeder` reads `ADMIN_SEED_EMAIL` and `ADMIN_SEED_PASSWORD` from your `.env` file. It will fail with an error if these are not set. There are no hardcoded default passwords.

### 4. Start Background Workers & Dev Server

WhatsEnroll relies on background queues for WhatsApp message processing and transactional email delivery:

```bash
# Terminal 1: Run queue worker or Horizon
php artisan horizon
# or: php artisan queue:work --tries=3

# Terminal 2: Start Laravel HTTP server
php artisan serve
```

---

## 🔌 Connecting Your Own Enrollment Backend

The "portal" or "enrollment backend" is what happens after a student pays and their enrollment is approved (e.g. mapping them to a course, creating an account on your LMS, or notifying your admissions team).

> [!TIP]
> **Connecting an External LMS (Moodle, WordPress / LearnDash, Teachable, or Custom API):**  
> If you already have an existing learning website or LMS platform, you do not need to rewrite bot code. Simply set:
> ```dotenv
> PORTAL_API_URL=https://your-website.com/api/enroll_student
> PORTAL_API_KEY=your_secret_api_key
> ```
> `PortalService.php` will automatically send a POST request with the student's details to your external endpoint.
>
> **Don't have an external LMS? (Standalone WhatsApp Bot):**  
> Leave `PORTAL_API_URL` empty in `.env`. WhatsEnroll works **100% standalone out-of-the-box**—storing enrollments in the local database and delivering instant confirmations over WhatsApp and email!

In WhatsEnroll, this entire process is isolated into a single, pluggable class:  
[`app/Services/Portal/PortalService.php`](app/Services/Portal/PortalService.php).

### The Adapter Contract

Whenever payment is verified, `CredentialService` calls `PortalService::enrollStudent(array $data)`:

#### Input Array (`$data`):
```php
[
    'name'         => 'Jane Doe',
    'email'        => 'jane@example.com',
    'phone'        => '919876543210',
    'course_id'    => 5,              // Matches portal_course_id configured in Admin panel
    'amount'       => 2999.00,
    'utr_number'   => 'pay_NXXXXX123',// Razorpay payment ID or manual UTR
    'payment_type' => 'full',
]
```

#### Expected Return Value:
```php
// On Success:
return [
    'success' => true,
    'data' => [
        'username'     => 'jane@example.com',
        'password'     => 'SecureGeneratedPass99!', // Optional for existing students
        'portal_url'   => 'https://learn.yourdomain.com/login',
        'course_title' => 'Advanced Python Bootcamp',
        'is_new_user'  => true, // boolean: true delivers credentials; false delivers welcome-back notice
    ]
];

// On Failure:
return [
    'success' => false,
    'message' => 'Course cohort is full or API unreachable',
];
```

### 3 Example Implementations

#### Option A: External LMS API (Default)
If you run an external LMS (e.g., Moodle, Teachable, WordPress/LearnDash, custom REST API):
Set `PORTAL_API_URL` and `PORTAL_API_KEY` in `.env`. `PortalService` will send an HTTP `POST` with the payload and a `X-Api-Key` header.

#### Option B: Internal Database-Only (No External API)
If you don't have an external LMS and just want to maintain enrollments inside WhatsEnroll:
Leave `PORTAL_API_URL` empty in `.env`. `PortalService` automatically falls back to an internal success response, generating a secure temporary password and pointing the learner to your `APP_URL`.

#### Option C: Google Sheets / Zapier / Make.com Webhook
To sync student enrollments directly to a Google Sheet:
1. Create a Zapier or Make.com "Catch Hook" trigger (or a Google Apps Script Web App).
2. Set `PORTAL_API_URL` in `.env` to your webhook URL.
3. In `PortalService.php`, forward the `$data` payload and return `'success' => true`.

---

## 📲 Connecting to Meta / Going Live

> [!NOTE]
> This project's code is fully built and tested against Meta's Cloud API — this section is only about the account/business setup steps on Meta's side, which every new deployment must complete themselves.

Connecting a production phone number to Meta's WhatsApp Business Platform follows the modern **Cloud API** architecture (the On-Premises API was sunset by Meta in October 2025):

### Step 1: Create a Meta Business Portfolio & Verify Business
1. Go to [business.facebook.com](https://business.facebook.com) and log in with your primary business Facebook account.
2. Create or select your **Business Portfolio**.
3. Navigate to **Business Settings > Security Center** and initiate **Business Verification**.
   - You will need official business documents (e.g. GST certificate, incorporation certificate, or utility bill with matching legal name and address).
   - *Verification typically takes 1–3 business days.*

### Step 2: Create a Meta Developer App
1. Navigate to [developers.facebook.com](https://developers.facebook.com) and click **Create App**.
2. Select **Other** as the use case, then choose **Business** as the app type.
3. Assign the app to the Meta Business Portfolio you created in Step 1.
4. Under **Add products to your app**, locate **WhatsApp** and click **Set up**.

### Step 3: Add & Register Your Phone Number
1. In your Meta Developer App, open **WhatsApp > API Setup**.
2. Under "Step 5: Add a phone number", click **Add phone number**.
3. Enter your display name, business category, and the phone number you wish to use.
   - *Important:* The phone number must **not** be registered to an active personal WhatsApp or WhatsApp Business mobile app. If it is, delete the account in the mobile app first.
4. Verify the number via SMS or voice call OTP.
5. Note down your assigned **Phone number ID** and **WhatsApp Business Account ID (WABA ID)**. Add them to `.env`:
   ```dotenv
   WHATSAPP_PHONE_NUMBER_ID=100000000000000
   WHATSAPP_BUSINESS_ACCOUNT_ID=200000000000000
   ```

### Step 4: Generate a Permanent System User Token
> [!WARNING]
> Do **not** use the 24-hour temporary token displayed in the developer portal for production. It will expire and stop message delivery.

1. Go to **Meta Business Settings > Users > System Users**.
2. Click **Add** and create a system user named `whatsenroll-system-user` with the role **Admin**.
3. Click **Add Assets**, select **Apps**, choose your Developer App, and grant **Full Control**.
4. Click **Generate New Token**, select your App, set token expiration to **Never**, and check the following permissions:
   - `whatsapp_business_management`
   - `whatsapp_business_messaging`
5. Copy the generated permanent token and paste it into `.env`:
   ```dotenv
   WHATSAPP_API_TOKEN=EAAG...
   ```

### Step 5: Configure Webhooks
1. Make sure your application is deployed to a publicly accessible HTTPS domain (e.g. `https://your-domain.com` or via an ngrok tunnel during development).
2. In your Developer App, navigate to **WhatsApp > Configuration**.
3. Under **Webhook**, click **Edit**:
   - **Callback URL:** `https://your-domain.com/api/webhook`
   - **Verify Token:** The exact string you set for `WHATSAPP_VERIFY_TOKEN` in `.env`.
4. Click **Verify and Save**. Meta will send a GET request to verify your endpoint.
5. In the **Webhook fields** table, click **Manage** and subscribe to:
   - `messages` (Mandatory: triggers bot conversation flow)
6. Retrieve your **App Secret** from **App Settings > Basic** and save it in `.env` as `WHATSAPP_META_APP_SECRET`.

### Step 6: Create Message Templates (For Outbound Notifications)
- Meta allows 2-way session messaging within a 24-hour customer service window starting from the user's last message.
- For business-initiated notifications sent outside this 24-hour window, you must submit pre-approved **WhatsApp Message Templates** in the WhatsApp Manager interface under your WABA account.

### Step 7: Testing in Sandbox Before Going Live
Meta provides a free test sandbox phone number and test recipient list in **WhatsApp > API Setup**. You can whitelist up to 5 tester phone numbers to test conversational flows and payment webhooks before completing business verification and deploying your production number.

---

## 📄 License

This project is open-source software licensed under the [MIT License](LICENSE).

---

## 👨‍💻 Author

**Vishwas**
- GitHub: [@Vishwas7975](https://github.com/Vishwas7975)
- LinkedIn: [vishwas-s-48205327a](https://www.linkedin.com/in/vishwas-s-48205327a)
