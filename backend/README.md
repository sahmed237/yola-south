# Premium Admin Boilerplate (Laravel 11)

A professional, enterprise-grade administrative template built with **Laravel 11**, designed to serve as a robust starting point for secure and scalable web applications.

## 🚀 Core Features

### 1. User Management Dashboard
*   **Complete Lifecycle**: Full CRUD operations for users with **Soft Delete** support (recycle bin).
*   **Bulk Management**: Perform actions on multiple users simultaneously (Activate, Suspend, Delete, or Force Password Changes).
*   **Account Controls**: Instantly toggle user status, verify emails manually, or reset security credentials.
*   **Audit Readiness**: Built-in **Authentication Logs** tracking every login, logout, and failed attempt with IP and Browser data.

### 2. Role & Permission System (Grouped)
*   **Granular Control**: Powered by `spatie/laravel-permission` with a custom **Permission Grouping** layer.
*   **Human-Readable**: Permissions use a clean, dot-free naming convention (e.g., `users create` instead of `users.create`).
*   **Categorized UI**: A premium Role Management interface that groups permissions into logical sections (User Management, Shop Management, etc.).
*   **Super Admin Bypass**: A predefined `super-admin` role that implicitly bypasses all permission checks.

### 3. Advanced Security
*   **Two-Factor Authentication (2FA)**: Native support for security codes during login.
*   **Account Locking**: Automatically locks accounts after repeated failed login attempts for a configurable duration.
*   **Password Enforcement**: Force users to change their password upon next login (useful for new accounts or security resets).
*   **Security Logs**: Detailed tracking of security-sensitive events like 2FA resets or account unlocking.

### 4. Dynamic Branding & Settings
*   **Centralized Configuration**: All system settings (App Name, Logo, Primary Colors) are managed via the database.
*   **Dynamic SMTP**: Mail configuration is consumed directly from database settings at runtime, allowing for instant email provider updates without touching `.env` files.
*   **Premium UX**: Custom scrollbars, glassmorphic UI elements, and responsive sidebar states tailored to the system's brand colors.

---

## 🛠️ Technology Stack
*   **Backend**: Laravel 11 (PHP 8.2+)
*   **Frontend**: Tailwind CSS (Styling) & Alpine.js (Interactivity)
*   **Icons**: Lucide Icons
*   **Permissions**: Spatie Laravel-Permission
*   **Components**: Blade Components with high-performance Alpine.js state management.

---

## 📦 Installation & Setup

### 1. Clone & Install
```bash
git clone https://github.com/sahmed237/laravel-boilerplate-t1.git
cd laravel-boilerplate-t1
composer install
npm install && npm run dev
```

### 2. Environment Configuration
Copy the example environment file and update your database credentials:
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database & Seeding
Initialize the database with the core roles, permissions, and system settings:
```bash
php artisan migrate --seed
php artisan storage:link
```

### 4. Default Credentials
*   **Super Admin**: `superadmin@urcs.com` / `superadmin`

---

*   **Users**: `users view`, `users create`, `users edit`, `users delete`, `users status`, `users logs`, `users security`, `users reset password`, `users reset 2fa`, `users verify`, `users email`, `users unlock`.
*   **Roles**: `manage roles`.
*   **System**: `manage agencies`, `approve shops`, `view payments`.

---

## 👥 Roles & Geographic Designations
The system features granular role-based access control (RBAC) integrated with geographic operational boundaries (LGAs and Wards). All non-super-admin users are restricted to viewing and managing establishments, payments, invoices, reports, and APIs only within their designated areas of operation.

### Predefined Roles & Scopes:
1. **Super Admin**
   - **Scope**: Global (Unrestricted).
   - **Access**: Full system controls, settings, SMTP config, database setup, and user permissions management.
2. **Revenue Manager**
   - **Scope**: State-level or Multi-LGA.
   - **Access**: Monitors payments and invoices. Verifies manual bank transfers, manages revenue rules, and reviews analytical report trends.
3. **LGA Coordinator**
   - **Scope**: LGA-level (restricted to designated LGAs).
   - **Access**: Monitors active/inactive establishments, payments, invoices, and reports inside their assigned LGAs. Requests details updates.
4. **Verification Officer**
   - **Scope**: LGA-level (restricted to designated LGAs).
   - **Access**: Reviews pending establishment registrations, audits geotagged evidence, and approves (generates unique ID/QR Code) or rejects submissions.
5. **Field Officer**
   - **Scope**: Ward-level (restricted to designated Wards/LGAs).
   - **Access**: Registers businesses in the field, captures Owner/Occupant profiles, uploads geotagged images, records collections, and syncs offline records.
6. **Agency Representative**
   - **Scope**: Agency-specific (Read-Only).
   - **Access**: Accesses payments, invoices, and report splits belonging only to their specific government ministry/department.

---

## 🎨 UI Customization
System colors and aesthetics are controlled via the `Setting` model. To update the primary color or sidebar scrollbar:
1.  Locate `SettingSeeder.php`.
2.  Update `theme_primary_color` or `theme_sidebar_scrollbar_color`.
3.  Re-run seeder or update via the database.

---

## 📄 License
This template is open-sourced software licensed under the **MIT license**.
