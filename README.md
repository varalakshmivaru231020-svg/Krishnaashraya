# Krishnashraya Educational Trust website

A dynamic website with an admin panel. Built with **PHP 8 + MySQL**, with no frameworks and no build step, so it runs on any normal cPanel hosting (Hostinger, GoDaddy, BigRock, etc.).

| Part | Technology |
|---|---|
| Pages | PHP templates + the original Bootstrap 5 design (`assets/css/styles.css`) |
| Database | MySQL / MariaDB (`database/schema.sql`) |
| Payments | Razorpay **or** Cashfree (chosen in the admin), each confirmed on the server and by webhook |
| Admin | `/admin`: sessions, hashed passwords, CSRF protection, login throttling |
| Images | Uploads are checked, resized and converted to WebP automatically |

## What is dynamic (editable in /admin)

- **Donations**: only the donor's first name and an amount are required (last name, phone, email, gender, PAN and message are optional). Every attempt is saved *before* payment, with status Paid / Not paid / Cancelled / Failed. Unpaid or failed attempts are listed so the team can follow up (WhatsApp, call and email buttons). Filters and an Excel/CSV download are included.
- **Enquiries**: the contact form (general, volunteer, sponsor a child…), with status and notes.
- **Donation Purposes**: the "Donate towards" choices (General Donation, Education, Food & Nutrition, Shelter & Care…) — add, edit, reorder, switch on/off or delete. Ongoing projects are listed there too.
- **About Us**: the About section on the home page and About Us page — titles, story text, quote, photo, the 3 key numbers, mission card, vision, mission and our work.
- **Banners**: home page slider (add, edit, reorder, hide, delete slides — each slide is a photo or a short MP4/WebM video up to 40 MB, which plays without sound and then moves on) and the top banner of each page, each with a live preview.
- **Impact Numbers**: the "Your Contribution Creates Measurable Change" figures on the home page.
- **Projects**: add, edit or hide projects, with a photo, key points and an optional fundraising goal with a progress bar. They appear on the home page, `/projects`, `/projects/{name}` and in the donation form.
- **Activities & Gallery**: the 7 activities (Education Programs, Food & Nutrition, Community Support, Extracurricular, Events & Initiatives, Donor Testimonials, Media Gallery). You can add new ones, upload many photos at once, set captions, order and the cover photo.
- **Testimonials**: the "Voices of Change" section on the home page.
- **Website Popup**: an announcement box with a photo (or a poster/flyer on its own) that opens over the home page or every page — on/off, button link, once per visit / once a day, optional start and end dates. It never opens on the donate page.
- **Site Settings**: phones, emails, addresses, bank and UPI details, About / Vision / Mission text, stats, social links, suggested donation amounts.

## Pages

`/` · `/about` · `/projects` · `/projects/{slug}` · `/our-activities` · `/{activity}` (e.g. `/education-programs`, same URLs as the old site) · `/contact` · `/donate` · `/donate/thank-you`

Clicking **Donate Now** anywhere opens the donation page. The flow is: details → amount → Razorpay or Cashfree (UPI, cards, net banking) → thank-you page.

## Run on your computer (XAMPP)

1. Start **MySQL** in the XAMPP Control Panel.
2. In this folder run: `php -S localhost:8000 router.php`
3. Open http://localhost:8000/install once to create the tables and the first admin.
   (On a fresh copy this creates your own admin email and password. Change the password any time under **Account & Users**.)
4. Website: http://localhost:8000 · Admin: http://localhost:8000/admin

## Go live on cPanel hosting

1. cPanel → **MySQL Databases**: create a database and a user, and give the user all privileges.
2. Upload every file in this folder to `public_html` (not the `Ashraya-static-backup` folder).
3. Copy `app/config.sample.php` to `app/config.php` and fill in the database name, user and password. Keep `debug` set to `false`.
4. Visit `https://www.krishnashraya.in/install` and create your admin account. The installer locks itself afterwards.
5. Turn on SSL (cPanel → SSL/TLS Status, or AutoSSL). Both payment gateways need HTTPS.

## Cloudflare (optional, recommended)

Cloudflare cannot run PHP/MySQL, so the site stays on the PHP hosting; Cloudflare sits in front for free SSL, caching and protection.

1. Upload the site to the hosting first (needs **PHP 8.1+** and MySQL) and check it works on the server.
2. Cloudflare → **Add a domain** → `krishnashraya.in` → **Free** plan. Check the imported DNS records: `@` and `www` must point to the hosting server's IP (currently `168.231.103.114`), with the orange cloud (Proxied) on. Keep any MX/email records exactly as they are.
3. BigRock → Domain → **Name Servers** → replace `dns1–dns4.bigrock.in` with the two Cloudflare nameservers. Takes from a few minutes up to 24 hours.
4. Cloudflare → **SSL/TLS** → mode **Full (strict)** (or **Full** if the server has no SSL certificate yet), and turn on **Always Use HTTPS**.
5. Leave **Rocket Loader** off (it can break the payment window) and don't use "Cache Everything". If you turn on **Bot Fight Mode** or WAF rules, allow `/donate/webhook` so Razorpay/Cashfree payment notifications still arrive.

The site already reads the visitor's real IP address from Cloudflare, so the admin login lock-out works per visitor.

## Turn on online payments

Everything is in **Admin → Payment Settings**. Pick **one** gateway — switching one on switches the other off.
Start with **Test Mode** (no real money), click **Save & test … keys** to check them, then turn Test Mode off and enter your live keys.

**Razorpay** — https://dashboard.razorpay.com → Account & Settings → API Keys. Test keys start `rzp_test_`, live keys `rzp_live_`.
Test payment: UPI `success@razorpay`.

**Cashfree** — https://merchant.cashfree.com → Developers → API Keys (Payment Gateway). Copy the App ID and Secret Key.
Before going live, add `https://www.krishnashraya.in` under **Developers → Whitelisting**, or Cashfree blocks the payment window.

### Webhooks (recommended)

A webhook is a message the gateway sends straight to the website when a payment succeeds or fails. It marks the donation
**Paid** even if the donor closes the browser before the thank-you page loads. Gateways can't reach a site running on your
own computer, so set this up once the site is live:

- **Razorpay** → Accounts & Settings → Webhooks → Add New Webhook: URL `https://www.krishnashraya.in/donate/webhook`,
  events `payment.captured`, `payment.failed`, `order.paid`, and a secret you also paste into Payment Settings.
- **Cashfree** → nothing to do: on https the site sends Cashfree the address `https://www.krishnashraya.in/donate/webhook/cashfree`
  with every order. (You may also add it under Developers → Webhooks; no secret needed.)

Until a gateway is switched on, the form still saves donor details, and the donor is told the team will contact them.

## Google: Analytics, Search Console, sitemap

- **Sitemap** `/sitemap.xml` and **robots.txt** `/robots.txt` are generated automatically (new projects and activities are added on their own). On a local copy, robots.txt blocks all indexing.
- **Google Analytics**: analytics.google.com → Admin → Create property → Web data stream for `https://www.krishnashraya.in` → View tag instructions → Install manually → copy the whole code into **Admin → Site Settings → Google & SEO** (the site picks out the `G-…` ID itself; Google Tag Manager code works too). Tracking runs only on the live site, never in the admin.
- **Search Console**: search.google.com/search-console → Add property (URL prefix `https://www.krishnashraya.in`) → HTML tag → paste the code into **Google & SEO** → Save → click Verify → Sitemaps → submit `sitemap.xml`.

## Optional

- **Email alerts** for new donations and enquiries: set `mail.notify_to` in `app/config.php`.
- **reCAPTCHA** on the contact form: add `site_key` and `secret_key` (a hidden anti-spam field is always on).
- **Map on the Contact page**: Site Settings → Google Maps embed URL.

## Folder layout

```
index.php            router: every URL comes here
app/                 PHP logic (pages/, admin/, helpers, Razorpay + Cashfree, config)
templates/           HTML templates (partials/, admin/)
assets/              CSS, JS, original images
uploads/             photos uploaded from the admin (back this folder up)
database/schema.sql  database tables
```

**Backups:** download the database (cPanel → phpMyAdmin → Export) and the `uploads/` folder regularly.
