# Unified Revenue Collection System (URCS)

A government-grade, enterprise-scale revenue collection platform designed for Nigerian state governments. This system facilitates the registration, tracking, and payment processing for commercial establishments (shops) through a unified digital ecosystem.

## 🏛️ Project Overview

The URCS provides a seamless bridge between field operations and administrative oversight. It ensures that every shop within the state is accurately captured, verified, and assigned a unique digital identity for transparent revenue collection.

### Key Components
*   **Backend (`backend/`)**: A robust Laravel-powered API and Admin Dashboard for verification, reporting, and system management.
*   **Mobile (`mobile/`)**: A Flutter-based, offline-first application for Field Officers to enroll shops in remote locations without active internet.

---

## 🚀 Phase 1 Features (MVP)

### Field Operations (Mobile)
*   **Offline Enrollment**: Field officers can register shop details, GPS coordinates, and occupant information entirely offline.
*   **Intelligent Sync Engine**: Automatic detection of internet connectivity to sync local records to the central server.
*   **Status Tracking**: Real-time visibility into the sync status (Pending, Synced, Failed) of every record.

### Administrative Control (Backend)
*   **Verification Workflow**: Admins review, approve, or reject shop registrations submitted from the field.
*   **Unique ID Generation**: Automatic generation of government-standardized IDs in the format: `LGA-WARD-XXXXXXXXXX`.
*   **QR Code Engine**: Instant generation of unique QR codes for every approved shop to facilitate public payments and verification.

---

## 🛠️ Technology Stack

| Component | Technology | Role |
| :--- | :--- | :--- |
| **Backend** | Laravel 11 | API Gateway & Admin Management |
| **Mobile** | Flutter | Field Officer Enrollment Tool |
| **Database** | MySQL / PostgreSQL | Central Data Repository |
| **Local Storage**| SQLite (sqflite) | Offline Data Persistence |
| **Auth** | Laravel Sanctum | Secure API Communication |

---

## 📂 Project Structure

```text
├── backend/            # Laravel API & Admin Dashboard
│   ├── app/            # Core business logic
│   ├── database/       # Migrations, Seeds, and Factories
│   └── resources/      # Admin UI (Blade, Tailwind, Alpine.js)
│
├── mobile/             # Flutter Field Officer Application
│   ├── lib/            # Business logic, UI, and Services
│   └── assets/         # App icons and resources
```

---

## ⚙️ Initial Setup

### Backend
Refer to the detailed [Backend README](backend/README.md) for environment configuration, role seeding, and system settings.

### Mobile
1. Ensure Flutter SDK is installed.
2. Update the API Base URL in the environment configuration.
3. Run `flutter pub get`.

---

## 🗺️ Roadmap (Upcoming Phases)
*   **Phase 2**: Integration of Paystack for public QR-based payments.
*   **Phase 3**: Dynamic revenue rule generation and automated payment splitting across multiple government agencies.
*   **Phase 4**: Real-time revenue analytics dashboard for state executives.

---

## 📄 License
This project is proprietary software developed for government revenue collection.
