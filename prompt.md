# Role

You are a senior full-stack web developer, UI/UX designer, information architect, technical SEO specialist, security engineer, and deployment engineer.

Your task is to analyze the raw materials I provide and build a **complete, production-ready, multilingual corporate/product website** from them.

Do not create only a prototype or static mockup. Build the actual working application, including frontend, backend, database, administration panel, content management, contact forms, deployment configuration, and installation instructions.

---

# 1. Project Context

I will provide a folder containing **Raw Data / Raw Content** for the company and its products.

The raw materials may include:

* RAR archives
* ZIP files
* PDFs
* Word documents
* Excel files
* Images
* Product images
* Logos
* Catalogs
* Brochures
* Technical specifications
* Existing website content
* Marketing materials
* Company descriptions
* Contact information
* Research and Development information
* Product specifications
* Miscellaneous files

Your first responsibility is to inspect and understand all available source materials before designing the website.

Do not blindly copy the files.

Analyze them, classify them, extract useful content, identify duplicate or outdated material when possible, and transform the information into a professional website structure.

---

# 2. Raw Content Analysis

Before writing the final website:

1. Recursively inspect the Raw Data directory.

2. Identify every useful file.

3. Extract archive files.

4. If `.rar` files are present, use an available extraction tool such as:

   * `unrar`
   * `7z`
   * `unar`

5. If no RAR extraction utility exists, clearly report which utility is required rather than silently ignoring the files.

6. Inspect extracted directories recursively.

7. Analyze relevant documents, images, catalogs, PDFs, spreadsheets, and text files.

8. Determine:

   * Company name
   * Company identity
   * Brand style
   * Product categories
   * Individual products
   * Product specifications
   * Product advantages
   * Industries/applications
   * Research and Development information
   * About Us content
   * Contact information
   * Addresses
   * Phone numbers
   * Email addresses
   * Certifications if present
   * Existing branding/colors
   * Logos
   * Images suitable for the website

9. Create a logical information architecture based on the actual supplied content.

Do not invent important factual claims about the company or products.

If information is missing, use editable CMS placeholders instead of presenting invented information as fact.

---

# 3. Target Audience

The primary audience is located in **Iran**.

Therefore:

* Persian should be the default language.
* Persian pages must use proper RTL layout.
* The Persian typography and reading experience must be excellent.
* The website must also support English and Arabic.

Required languages:

* Persian / Farsi — `fa`
* English — `en`
* Arabic — `ar`

Architecture must make adding additional languages later straightforward.

---

# 4. Language and Direction Support

Implement a real multilingual content system.

Do NOT simply translate interface labels with JavaScript.

Each content entity should support translated fields.

For example:

* Product title
* Product description
* Product features
* Product specifications
* Category title
* Page title
* Page body
* SEO title
* SEO description

must be manageable independently for Persian, English, and Arabic.

Direction:

* Persian → RTL
* Arabic → RTL
* English → LTR

The entire layout must automatically switch direction according to the selected language.

Add a professional language switcher in the header.

Prefer SEO-friendly URLs such as:

```text
/fa/
/fa/products
/fa/products/product-slug

/en/
/en/products
/en/products/product-slug

/ar/
/ar/products
/ar/products/product-slug
```

If you choose another URL architecture, explain why it is better.

---

# 5. Preferred Technology Stack

The production server is primarily a **PHP hosting environment**.

Therefore the final website must run reliably on ordinary PHP hosting, preferably Apache/cPanel hosting.

Preferred stack:

## Backend

Use:

* PHP 8.1+ or PHP 8.2+
* MySQL / MariaDB
* PDO with prepared statements
* Clean MVC-inspired architecture

You may use Composer if useful, but avoid unnecessary complexity.

Do not require a continuously running Node.js server.

The website should ultimately operate through PHP.

---

# 6. Frontend

Use whichever frontend approach is most appropriate for the project while prioritizing:

* Performance
* Maintainability
* Browser compatibility
* Excellent UI/UX
* Easy deployment on PHP hosting

You may use:

* HTML5
* CSS3
* modern JavaScript
* Bootstrap 5
* Tailwind CSS if it can be compiled before deployment
* jQuery where useful
* Alpine.js where appropriate
* lightweight JavaScript libraries

Avoid unnecessarily heavy SPA frameworks unless there is a very strong justification.

The production version should not require Node.js to serve the website.

---

# 7. UI/UX Quality — Extremely Important

UI/UX quality is one of the highest priorities of this project.

Do not produce a generic Bootstrap template.

The site must feel like a professionally designed modern corporate/product website.

Create a visually refined experience including:

* Strong visual hierarchy
* Elegant typography
* High-quality spacing
* Responsive design
* Modern grid systems
* Professional product presentation
* Attractive cards
* Subtle animations
* Micro-interactions
* Smooth hover states
* Professional transitions
* Well-designed buttons
* Strong calls-to-action
* Accessible forms
* Clear navigation
* Consistent design system
* Excellent mobile experience

Use the supplied branding, logo, images, and visual materials whenever possible.

If the provided branding does not define a complete design system, derive a tasteful visual system from the existing logo/product imagery.

Do not use excessive animations that hurt performance.

---

# 8. Persian Typography

Persian typography must be first-class rather than an afterthought.

Use an appropriate Persian-compatible font that can legally and practically be deployed with the project.

Consider good typography for:

* Persian headings
* Body text
* Numbers
* Forms
* Tables
* Product specifications
* Mobile interfaces

Maintain excellent readability in RTL mode.

Arabic typography must also render correctly.

---

# 9. Required Website Sections

At minimum the main navigation must include:

* Home
* Products
* Research & Development
* About Us
* Contact Us

Corresponding Persian and Arabic titles must be included.

All sections must be manageable through the administration system wherever reasonable.

---

# 10. Home Page

Create a premium home page based on the actual available content.

Potential sections may include:

* Hero section
* Company introduction
* Product categories
* Featured products
* Technology / engineering capabilities
* Research & Development highlight
* Main advantages
* Industries/applications
* Statistics if legitimate data exists
* Certifications if present
* Latest products or news if relevant
* Strong CTA
* Contact CTA

Choose the sections according to the source material rather than mechanically adding everything.

---

# 11. Products

Build a complete product catalog.

Support:

* Product categories
* Subcategories if necessary
* Individual product pages
* Product images
* Product galleries
* Product descriptions
* Features
* Technical specifications
* Applications
* Downloadable catalogs/datasheets
* Related products
* SEO metadata
* Multilingual fields
* Custom ordering

Product listing pages should support a professional browsing experience.

If the amount of content justifies it, implement:

* Search
* Category filtering
* Attribute filtering
* Sorting

Do not add meaningless filters if the dataset is too small.

---

# 12. Product Detail Page

Each product should have a polished product-detail experience.

Where appropriate include:

* Product title
* Hero image
* Gallery
* Summary
* Technical description
* Features
* Specifications table
* Applications
* Advantages
* Technical downloads
* Related products
* Inquiry CTA

Technical tables must work correctly in both RTL and LTR layouts.

---

# 13. Research & Development

Create a dedicated R&D section.

Use actual supplied source material whenever available.

Possible content may include:

* R&D philosophy
* Engineering capabilities
* Laboratories
* Development process
* Innovation
* Technology
* Testing
* Quality assurance
* New product development

This page must be editable from the admin panel.

---

# 14. About Us

Create a professional company profile based on supplied information.

Possible sections:

* Company introduction
* History
* Mission
* Vision
* Values
* Capabilities
* Production infrastructure
* Team
* Certifications
* Milestones

Only display factual information supported by the source material.

---

# 15. Contact Us

Create a professional Contact Us page.

Support:

* Company address
* Phone numbers
* Email addresses
* Working hours if provided
* Contact form
* Map/embed location if appropriate
* Department contacts if available

Contact form fields should include at minimum:

* Name
* Email
* Phone
* Company
* Subject
* Message

Add validation on both frontend and backend.

---

# 16. Contact Form Backend

Contact submissions must:

1. Be validated securely.

2. Be stored in the database.

3. Be visible in the admin panel.

4. Have status management such as:

   * New
   * Read
   * Replied
   * Archived

5. Allow administrators to view the full inquiry.

6. Allow searching/filtering.

7. Optionally send an email notification to a configurable administrator email address.

Do not expose mail credentials in frontend code.

---

# 17. Administration Panel

Build a comprehensive professional administration dashboard.

The admin panel is an important part of the project, not an afterthought.

Example URL:

```text
/admin
```

The admin panel should have its own responsive UI and authentication.

---

# 18. Admin Authentication

Implement secure login.

Requirements:

* Admin users stored in database
* Secure password hashing using PHP `password_hash()`
* Session-based authentication
* Login/logout
* Session regeneration
* CSRF protection
* Brute-force/rate-limiting protection where practical
* Secure cookie configuration where possible
* Role-ready architecture

Never store plain-text passwords.

---

# 19. Admin Dashboard

Create a useful dashboard displaying information such as:

* Number of products
* Number of product categories
* New messages
* Total inquiries
* Recently received messages
* Recently modified content
* Quick actions

Keep the dashboard practical rather than decorative.

---

# 20. Admin — Product Management

Administrators must be able to:

* Add products
* Edit products
* Delete products
* Archive/unpublish products
* Change product ordering
* Manage product categories
* Upload product images
* Manage product gallery
* Add technical specifications
* Upload product datasheets
* Edit Persian content
* Edit English content
* Edit Arabic content
* Configure SEO fields
* Control product visibility

Provide a user-friendly editing interface.

---

# 21. Admin — Page Management

Provide editing for major pages such as:

* Home
* Research & Development
* About Us
* Contact Us

Where practical use modular sections rather than one giant text field.

For example a page can contain components such as:

* Hero
* Rich text
* Image + text
* Statistics
* Features
* Gallery
* CTA

However, keep the CMS maintainable and not unnecessarily complex.

---

# 22. Admin — Contact Messages

Administrators should be able to:

* View incoming messages
* Search messages
* Filter by status
* Mark read/unread
* Mark replied
* Archive messages
* Delete messages where appropriate
* View sender email
* View sender phone number
* Copy contact details
* Export inquiries to CSV if reasonably straightforward

---

# 23. Admin — Site Settings

Create a global settings area.

Administrators should be able to manage:

* Company name
* Logo
* Favicon
* Contact emails
* Phone numbers
* Addresses
* Social media links
* Default SEO title
* Default SEO description
* Footer information
* Google Maps/embed settings where applicable
* Email notification settings
* Website language settings
* Main contact information

---

# 24. Media Manager

Implement a practical media-management approach.

Support:

* Image upload
* File upload
* Product brochures
* PDFs
* Image preview
* Secure filenames
* Allowed MIME validation
* Maximum upload limits
* Deleting unused media where safe

Uploaded files must not allow arbitrary PHP execution.

---

# 25. Search Engine Optimization

Implement proper technical SEO.

Include:

* Semantic HTML
* SEO-friendly URLs
* Editable page titles
* Editable meta descriptions
* Canonical URLs
* `hreflang` tags for Persian, English, and Arabic
* Open Graph metadata
* Twitter/X metadata if appropriate
* XML sitemap
* `robots.txt`
* Schema.org structured data where relevant
* Product structured data where appropriate
* Breadcrumb structured data
* Organization structured data
* Clean heading hierarchy

Each language must have suitable SEO metadata.

---

# 26. Performance

Optimize for real production usage.

Implement:

* Responsive images
* Lazy loading
* Image compression guidance
* Minimal JavaScript
* Efficient CSS
* Cache-friendly static assets
* Database indexing
* Optimized SQL
* Avoiding N+1 patterns
* Reasonable browser caching
* GZIP/Brotli recommendations when supported

Do not sacrifice usability for unnecessary visual effects.

---

# 27. Accessibility

Follow modern accessibility principles.

At minimum:

* Proper semantic HTML
* Keyboard-accessible navigation
* Visible focus states
* Proper form labels
* ARIA only where necessary
* Sufficient contrast
* Meaningful image alt text
* Accessible modals/dropdowns
* Logical heading hierarchy

---

# 28. Security

Apply secure PHP development practices.

At minimum:

* PDO prepared statements
* CSRF protection
* Output escaping against XSS
* Secure password hashing
* Upload validation
* File extension validation
* MIME validation
* Randomized uploaded filenames
* Session security
* Login rate limiting
* Authorization checks
* Input validation
* Protection against SQL injection
* Protection against path traversal
* Secure error handling
* Prevent directory listing
* Protect configuration files

Production mode must not display PHP stack traces or database credentials.

---

# 29. Database

Use MySQL/MariaDB.

Design a clean schema for entities such as:

* admin_users
* products
* product_translations
* product_categories
* category_translations
* product_images
* product_specifications
* product_specification_translations if required
* pages
* page_translations
* contact_messages
* media
* site_settings
* translations or language-specific settings where appropriate

You may improve this database design if you identify a cleaner architecture.

Include indexes, foreign keys where supported, timestamps, and sensible deletion behavior.

---

# 30. Database Installation

Provide one of the following:

Preferred:

```text
/install.php
```

or a CLI installation script.

The installer should:

1. Check PHP version.
2. Check required PHP extensions.
3. Test database connection.
4. Create required tables.
5. Insert default settings.
6. Create the first administrator.
7. Optionally import content extracted from the supplied raw source material.
8. Disable or lock itself after successful installation.

Installation must not remain publicly exploitable.

---

# 31. Environment Configuration

Use a secure configuration approach.

For example:

```text
.env
```

or:

```text
config/config.php
```

If `.env` is used, ensure the web server denies public access.

Required configuration should include:

```text
APP_ENV
APP_URL
DB_HOST
DB_PORT
DB_NAME
DB_USER
DB_PASSWORD
MAIL_HOST
MAIL_PORT
MAIL_USERNAME
MAIL_PASSWORD
MAIL_FROM_ADDRESS
MAIL_FROM_NAME
```

Use only the values actually required by the implemented system.

Provide an example file:

```text
.env.example
```

Never include real passwords.

---

# 32. Email

If contact notification email is implemented, use a reliable PHP-compatible mail approach.

Prefer SMTP via a mature library such as PHPMailer if practical.

Contact submissions must still be stored in the database even if email sending fails.

Log mail failures safely without exposing credentials.

---

# 33. Routing

Implement clean routing.

Avoid ugly URLs such as:

```text
product.php?id=25
```

Prefer:

```text
/fa/products/example-product
```

If Apache is used, provide a suitable `.htaccess`.

The project should work on standard shared hosting where `mod_rewrite` is available.

Also provide a fallback or clear documentation if URL rewriting is unavailable.

---

# 34. Suggested Project Structure

You may improve the architecture, but a clean structure could resemble:

```text
/
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Services/
│   ├── Helpers/
│   └── Middleware/
│
├── config/
│
├── database/
│   ├── migrations/
│   └── seeds/
│
├── public/
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   ├── images/
│   │   └── fonts/
│   ├── uploads/
│   └── index.php
│
├── resources/
│   ├── views/
│   └── lang/
│
├── routes/
│
├── storage/
│   ├── logs/
│   └── cache/
│
├── vendor/
│
├── .env.example
├── .htaccess
├── composer.json
├── install.php
└── README.md
```

However, remember that many ordinary cPanel hosts expose `/public_html`.

Make deployment straightforward even when the user cannot change the Apache document root.

If necessary, adapt the structure for shared-hosting compatibility.

---

# 35. Shared Hosting / cPanel Deployment

This project must be easy to upload to a PHP host.

Provide deployment instructions for a typical environment with:

* cPanel
* `public_html`
* PHP 8.1/8.2+
* MySQL/MariaDB
* phpMyAdmin
* Apache

The website should not require Docker.

Docker may optionally be supplied for local development, but it cannot be a production requirement.

---

# 36. One-Step Deployment Helper

Create a deployment/setup helper where practical.

For example:

```text
setup.php
```

or:

```text
install.php
```

The ideal workflow should be approximately:

```text
1. Upload files.
2. Create MySQL database.
3. Enter database credentials.
4. Open /install.php.
5. Follow installation wizard.
6. Create administrator account.
7. Installation locks itself.
8. Website is ready.
```

Make this installation flow as safe and user-friendly as possible.

---

# 37. Local Development

Also provide a straightforward local-development method.

For example:

```bash
php -S localhost:8000 -t public
```

If the actual folder structure requires another command, document the correct command.

If Composer is required:

```bash
composer install
```

Document that clearly.

---

# 38. Apache Configuration

Provide the necessary `.htaccess`.

Handle:

* Front controller routing
* Prevent directory listing
* Protect sensitive configuration files
* Basic security headers when suitable
* HTTPS redirect instructions, but do not create redirect loops
* Clean URLs

Do not write server-specific settings that will commonly crash shared hosts without providing a safe fallback.

---

# 39. Responsive Requirements

Test the website design for:

* 320px mobile
* 375px mobile
* 430px mobile
* Tablet
* Laptop
* Desktop
* Large desktop

Ensure there are no horizontal-scroll issues caused by RTL layouts.

Test tables and product specifications carefully on small screens.

---

# 40. Browser Support

Support current versions of:

* Chrome
* Edge
* Firefox
* Safari
* Mobile Safari
* Android Chrome

Graceful degradation is acceptable for non-essential visual effects.

---

# 41. Content Migration

Use the supplied Raw Data to populate the initial website whenever enough information exists.

Do not require me to manually copy every product if the information can be reliably extracted.

Create initial database seed/import data from the supplied materials where reasonable.

When automatic extraction is uncertain, preserve the original source reference and flag the field for administrator review.

---

# 42. Never Invent Product Specifications

This is critical.

Technical specifications, certifications, addresses, phone numbers, capacities, measurements, claims, statistics, and other factual information must come from provided materials.

Never invent technical values simply to fill the UI.

Missing information can be represented internally as optional fields.

---

# 43. Images

Use the provided product and company imagery whenever possible.

Optimize images for the web.

Generate appropriate sizes/thumbnails when useful.

Do not distort product images.

Use sensible object-fit behavior.

Implement meaningful alt text based on available product information.

---

# 44. Design Decision Process

Before implementing the UI:

1. Analyze the supplied brand.

2. Analyze the products.

3. Determine the target audience.

4. Determine whether the brand should feel, for example:

   * Industrial
   * Technological
   * Scientific
   * Premium
   * Corporate
   * Engineering-focused
   * Minimal
   * Innovative

5. Develop a small design system defining:

   * Primary color
   * Secondary color
   * Accent color
   * Background colors
   * Typography
   * Border radius
   * Spacing scale
   * Button styles
   * Card styles
   * Shadows
   * Form styles

Then apply it consistently.

Do not randomly choose colors independently on every page.

---

# 45. Do Not Stop at Planning

Do not only give me:

* Recommendations
* Architecture diagrams
* TODO lists
* Pseudocode
* Example snippets

Actually create the complete project files.

Continue implementation until there is a functional website.

---

# 46. Autonomous Decision-Making

You are authorized to make reasonable technical decisions without repeatedly asking me questions.

When multiple solutions are possible, choose the solution that best satisfies:

1. PHP shared-hosting compatibility
2. Security
3. Maintainability
4. UI/UX quality
5. Performance
6. Ease of administration
7. Multilingual support

Only ask a question if progress is genuinely impossible without the answer.

Otherwise make a reasonable assumption and document it.

---

# 47. Implementation Workflow

Follow this workflow:

## Phase 1 — Discovery

* Inspect all project files.
* Extract archives.
* Analyze content.
* Understand the company.
* Identify products.
* Identify branding.
* Produce an internal content map.

## Phase 2 — Architecture

Determine:

* Database schema
* PHP application architecture
* URL structure
* Localization architecture
* Admin architecture
* Media strategy
* Deployment strategy

## Phase 3 — Design

Create:

* Design tokens
* Layout system
* Header
* Footer
* Navigation
* Mobile navigation
* Product cards
* Product detail design
* Content sections
* Admin UI

## Phase 4 — Backend

Implement:

* Database
* Routing
* Models
* Controllers
* Authentication
* CMS
* Forms
* Contact inbox
* Settings
* Media uploads
* Security

## Phase 5 — Frontend

Implement all responsive multilingual public-facing pages.

## Phase 6 — Content Import

Populate the website using the supplied Raw Data.

## Phase 7 — Testing

Test:

* PHP errors
* SQL queries
* Routing
* Login/logout
* CSRF
* Contact forms
* File uploads
* RTL/LTR
* All three languages
* Mobile layouts
* Broken links
* Missing images
* Admin CRUD
* Product CRUD

## Phase 8 — Deployment Package

Prepare the application for ordinary PHP shared hosting.

---

# 48. Testing Checklist

Before declaring the project complete, verify at minimum:

* Home page works in FA.
* Home page works in EN.
* Home page works in AR.
* RTL correctly works in FA/AR.
* LTR correctly works in EN.
* Language switching works.
* Product listing works.
* Product detail works.
* Admin login works.
* Admin logout works.
* Product create works.
* Product edit works.
* Product delete/archive works.
* Image upload works.
* Contact form works.
* Contact inquiry is stored.
* Contact inquiry appears in admin.
* Settings editing works.
* Unauthorized `/admin` access is rejected.
* SQL injection protections exist.
* XSS output is escaped.
* CSRF protection works.
* Missing routes return a designed 404 page.
* Production errors are not exposed publicly.

Fix discovered problems rather than merely documenting them.

---

# 49. Final Deliverables

When finished, the project directory must include:

1. Complete PHP source code
2. Complete frontend
3. SQL/database setup
4. Installation process
5. Admin panel
6. Multilingual system
7. Product CMS
8. Contact inbox
9. Site settings
10. Media/upload system
11. `.htaccess`
12. `.env.example` or equivalent configuration example
13. README
14. Deployment instructions
15. Local-development instructions
16. Production security instructions
17. Initial content imported from supplied files where possible

---

# 50. README Requirements

Create a detailed:

```text
README.md
```

It must include:

## Requirements

For example:

```text
PHP >= 8.1
MySQL >= 5.7 / MariaDB equivalent
PDO
pdo_mysql
mbstring
fileinfo
json
openssl
GD or Imagick where required
mod_rewrite recommended
```

Adjust to the actual code.

## Local Installation

Include exact commands.

## cPanel Installation

Explain step-by-step.

## Database Setup

Explain step-by-step.

## Admin Creation

Explain how the first admin is created.

## File Permissions

Explain any writable directories and safe permission recommendations.

Never recommend `777` unless absolutely unavoidable.

## Email Configuration

Explain SMTP configuration.

## Production Checklist

Explain:

* HTTPS
* APP_ENV
* Debug off
* Installer removal/lock
* Secure passwords
* Database backups
* Upload permissions
* Email configuration

---

# 51. Installation Wizard

If feasible, create a polished `/install.php` wizard.

Suggested steps:

### Step 1

Server requirements check.

### Step 2

Database configuration.

### Step 3

Create database schema.

### Step 4

Website information.

### Step 5

Create first administrator.

### Step 6

Finalize installation.

After installation:

* Create an installation lock file.
* Prevent installer from running again unless intentionally unlocked.

Never display the entered database password after submission.

---

# 52. Optional Migration Utility

If helpful, create an importer such as:

```text
scripts/import_raw_content.php
```

It can help transform source data into database records.

Keep the original raw source directory separate from the production website where possible.

---

# 53. Source-Control Hygiene

Create an appropriate `.gitignore`.

Exclude:

* `.env`
* Logs
* Temporary files
* Cached files
* OS metadata
* Local IDE files
* Sensitive generated configuration
* User uploads where appropriate

Do not accidentally commit credentials.

---

# 54. Code Quality

Use:

* Clear naming
* Reusable functions
* Small controllers/services
* Separation of concerns
* DRY principles without overengineering
* Consistent coding conventions
* Comments only where valuable

Do not put the entire application inside one enormous PHP file.

The requirement that the website "runs through PHP" does NOT mean all source code must physically exist inside a single PHP file.

Use a professional maintainable file structure while making deployment simple.

---

# 55. Important Clarification About "Single PHP Execution"

The desired user experience is that the website can be deployed and launched easily on a PHP host.

Do not interpret this as requiring all CSS, HTML, JavaScript, PHP, database logic, and admin functionality inside one file.

Instead:

* Build a clean PHP application.
* Provide a single installation entry point such as `/install.php`.
* Provide a single public entry point such as `/index.php`.
* Make the deployment process simple enough that the administrator does not need to manually assemble frontend and backend components.

---

# 56. Final Response to Me

After implementation, provide a concise implementation report containing:

### Architecture

Explain the final stack and important technical choices.

### Raw Content Analysis

Summarize what source material was found and how it was used.

### Project Structure

Show the important directories.

### Installation

Give the exact shortest path to launch locally.

### Shared Hosting Deployment

Give the exact procedure for cPanel/PHP hosting.

### Admin

Provide the admin URL and explain how the first administrator is created.

### Configuration

List required environment/configuration variables.

### Security

Mention the important protections implemented.

### Remaining Manual Items

Clearly identify anything that could not be automatically derived from the source materials.

---

# 57. Start Now

Begin by inspecting the entire Raw Data / Raw Content directory.

Do not start with arbitrary visual design before understanding the supplied materials.

Then build the complete production-ready application.

When you encounter uncertainty, inspect the available source materials first.

Make sensible engineering decisions autonomously and proceed until the application is complete.
