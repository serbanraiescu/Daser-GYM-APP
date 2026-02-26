# Gym App Architecture Overview

This project implements a universal, single-tenant, white-label gym application architecture using Laravel 12 and Filament 3.

## Core Modules

### 1. Settings System (Milestone A)
- **Centralized Management**: Managed via `SettingsService`.
- **Auditing**: Every change is versioned and logged in `settings_history`.
- **Performance**: High-speed access via prefixed caching (`setting:{key}`).
- **Public API**: Secure `/api/public/settings` endpoint for frontend branding.

### 2. Membership Core (Milestone B)
- **Lifecycle Management**: `MembershipService` handles renewals, automated expiry, and grace-period transitions.
- **Event-Driven**: Uses `PaymentPaid` events to trigger downstream logic like loyalty updates.
- **Flexibility**: Supports global grace period defaults with plan-specific overrides.

### 3. Promotions Engine (Milestone C)
- **Stateless Logic**: `PromotionsEngine` calculates discounts based on a `TransactionRequest`.
- **Complex Rules**:
  - **Stacking**: Supports `STACKABLE`, `EXCLUSIVE`, and `EXCLUSIVE_GROUP` modes.
  - **Incompatibilities**: Define which promos cannot coexist.
  - **Prioritization**: High-priority promos apply first.
- **Explainability**: Returns a log explaining why each promo was applied or rejected.

### 4. Loyalty Engine (Milestone D)
- **Stateful Tracking**: `LoyaltyEngine` tracks punchcard progress in `loyalty_progress`.
- **Grace Resets**: Automatically resets progress if a member's lapse exceeds the configured grace period.
- **Reward Integration**: Flexible system to grant free renewals or credits upon reaching targets.

## Infrastructure

### Feature Flags
- Configured in `config/features.php`.
- Toggled via `.env` (e.g., `FEATURE_CLASSES=true`).
- Helper function `feature_enabled('classes')` available globally.

### Testing & Quality
- **Unit Tests**: Comprehensive coverage for `PromotionsEngine` and `LoyaltyEngine` logic.
- **Seeding**: `DemoDataSeeder` provides a fully functional starting point with plans, members, and active campaigns.
