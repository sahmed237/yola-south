# System Documentation: Unified Revenue Collection System (URCS)

This document provides a detailed breakdown of the architecture, key workflows, role permissions, and database operations within the **Unified Revenue Collection System (URCS)**.

---

## 🛠️ Global URL Reference Configuration

For easy configuration, replace the base URL variable placeholder below to update all system endpoint links:

```
[BASE_URL] = "http://localhost/revenue-collection-system/backend/public"
```

---

## 1. ⚙️ Base Configurations & Setup

The system manages physical and legal business profiles via customizable settings and criteria:

### A. System Settings & Custom Theme
- Managed via the Settings Panel by Super Admins.
- **Dynamic Configuration**: Saves `platform_name`, `platform_logo`, and branding theme colors (`theme_primary_color`, `theme_sidebar_bg`).
- **Render Engine**: Frontend views dynamically fetch values from `$system_settings` to override tailwind styling classes.

### B. Establishment Types & Sizes
- **Route Prefix**: `[BASE_URL]/admin/setup`
- **Establishment Types**: Group profiles (e.g. Retail, Manufacturing, Hospitality). Managed via `EstablishmentTypeController`.
- **Establishment Sizes**: Classification based on business scope (e.g., Micro, Small, Medium, Large). Managed via `EstablishmentSizeController`.
- **Permission required**: `manage system setup`

---

## 2. 🏢 Establishment Management & Update Requests

Business profiles are registered as **Establishments** and are audited via strict admin review steps.

### A. Establishment Lifecycle (CRUD)
- **Routes**: `[BASE_URL]/admin/establishments`
- **Creation**: Registrations are stored with state identifiers, owner contact details, size tier, and type attributes.
- **Verification**: Internal officers inspect active state licensing credentials and tax profile linkages.

### B. Approval Workflow
- Officers with `approve establishment` permission review pending submissions.
- **Status transitions**: `Pending` ➔ `Approved` OR `Rejected`.
- Only `Approved` establishments are indexable by taxpayers on the public portal lookup.

### C. Establishment Update Requests
- **Routes**: `[BASE_URL]/admin/establishment-update-requests`
- Taxpayers or officers can submit a requested profile change.
- Request is stored in `establishment_update_requests`. Admin reviews, then clicks `Approve` (which automatically updates the main establishment record) or `Reject`.

---

## 3. 🏦 Agencies, Revenue Rules & Split Payments

At the core of the URCS is the automated splitting of revenue among multiple state departments and service providers.

### A. Agency Configuration
- **Agencies**: Represents state departments (e.g., Ministry of Finance, Land Registry).
- **Service Fee Agency**: A flag `is_service_fee` is set to `true` to distinguish operational service providers.
- **Subaccounts**: Linked to Paystack/Monnify subaccount codes for automated split routing.

### B. Revenue Rules (Tax Tiers)
- Defines tax rates based on Establishment Type + Size.
- Linked to a primary collecting **Agency**.

### C. Splitting Mechanics (Service Fee & Net Splits)
1. **Invoice Splits (`invoice_splits` / `payment_splits`)**:
   - Stores the split ratio, destination subaccount, and indicates if the split represents a `service_fee_split` or core agency revenue.
2. **Payout Splitting Flow**:
   - The total invoice amount is checked out.
   - The payment gateway splits the transaction: the primary agency receives the core tax portion, while the service fee agency receives its designated transaction split.

---

## 4. 📄 Invoices & Payment Verification

Invoices represent active bills generated for establishments based on active revenue rules.

### A. Invoice Management (CRUD)
- **Routes**: `[BASE_URL]/admin/invoices`
- **Detailed View**: Displays outstanding itemized tax lines, calculated service fee splits, destination subaccounts, and payment status.

### B. Payment Verification (Failure Recovery)
- **Endpoint**: `[BASE_URL]/admin/invoices/{id}/verify`
- **Failed Callback Recovery**: If a webhook or redirect callback fails, the details page renders a **Reverify Payment** button for unpaid invoices.
- **Recheck Action**: Queries the Paystack/Monnify gateway API status using the reference key. If successful, updates the database state (`status = paid`), writes to payment splits, and issues digital receipts.

---

## 5. 💳 Payment Gateways

The platform integrates two central payment providers:

### A. Paystack Integration
- **Class**: `App\Services\Payment\Gateways\PaystackGateway`
- Uses Paystack Subaccounts to split funds inline during transaction initialization.
- Validates payouts and verifies transaction amounts against invoice totals.

### B. Monnify Integration
- **Class**: `App\Services\Payment\Gateways\MonnifyGateway`
- Settle transactions using Monnify Reserved Accounts or split contracts.

---

## 6. 📊 Reports & Interactive Analytics

Interactive insights dashboard for system officers and super-admins.

### A. Analytics Charts
- **Interactive UI**: Includes Pie charts, Bar charts, and Line charts.
- **Visual Insights**: Displays collection distribution per Agency, payment method percentages, and monthly collection trends.

### B. Interactive Filtering & Date Picker
- **Routes**: `[BASE_URL]/admin/reports`
- **Date Range Picker**: A calendar filter allows precise range searches.
- **Agencies & Status Filters**: Filter analytics by agency or split type.

### C. Exporting Data
- **Excel/CSV Export**: Exports filtered lists of invoices/payments, capturing agency split details, payment methods, transaction references, and service fees.

---

## 7. 💬 Dynamic FAQ Management

The taxpayers' FAQ portal is powered dynamically from the administration dashboard.

### A. CRUD Operations
- **Routes**: `[BASE_URL]/admin/faqs`
- **Fields**: `question`, `answer`, `sort_order` (controls presentation hierarchy), and `is_published` (toggle Draft vs. Live).
- **Empty State**: Centralized card layout containing illustration triggers.

### B. Public Portal View
- **Endpoint**: `[BASE_URL]/faq`
- Queries the `faqs` table where `is_published = true`. Includes a formatted fallback if the database has not been seeded.

---

## 8. 🔐 Role & Permission Matrix

The system implements role-based access control (RBAC) via Spatie permissions:

| Permission Name | Guarded Action / Button | Target Routes | Allowed Roles |
| :--- | :--- | :--- | :--- |
| **`manage system setup`** | Configures sizes & types | `/admin/setup/*` | `super-admin`, `admin` |
| **`manage users`** | Add, edit, ban, delete users | `/admin/users/*` | `super-admin` |
| **`manage roles`** | Configure permissions | `/admin/roles/*` | `super-admin` |
| **`view invoices`** | View invoice lists | `/admin/invoices` | `super-admin`, `admin`, `officer` |
| **`view invoice`** | View invoice details | `/admin/invoices/{id}` | `super-admin`, `admin`, `officer` |
| **`verify invoice`** | Re-verify payment button | `/admin/invoices/{id}/verify` | `super-admin`, `admin` |
| **`view payments`** | View payment registers | `/admin/payments` | `super-admin`, `admin`, `officer` |
| **`view report`** | Open Reports & Charts | `/admin/reports` | `super-admin`, `admin` |
| **`manage faq`** | Add, Edit, Delete FAQs | `/admin/faqs/*` | `super-admin`, `admin` |
| **`approve establishment`**| Accept or reject business profile | `/admin/establishment-update-requests/*` | `super-admin`, `admin` |

---
