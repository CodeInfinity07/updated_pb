# PredictionBot - Laravel MLM/Investment Platform

## Overview
The PredictionBot is a Laravel 11 PHP application designed to manage investments, referrals, and a multi-level marketing (MLM) commission structure. Its primary purpose is to provide a comprehensive platform for users to invest, track their earnings, and participate in a referral program, while offering administrators robust tools for system management, user oversight, and financial tracking. The platform includes user authentication with 2FA, an extensive admin dashboard, dynamic investment plan management, a multi-tiered referral commission system, KYC verification, a support ticket system, and push notifications. The project aims to provide a robust and scalable solution for online investment and MLM operations.

## User Preferences
I want to use iterative development. Ask before making major changes. I prefer simple language and detailed explanations when new features or complex logic are introduced. I prioritize clear, maintainable code. Do not make changes to the folder `Z`. Do not make changes to the file `Y`.

## System Architecture
The application is built on Laravel 11 (PHP 8.2) with a PostgreSQL database. The frontend utilizes Blade templates with Bootstrap 5 for UI/UX, and Vite for asset bundling.

**Key Technical Implementations & Features:**
*   **User Management**: Includes registration, login, profile management, and 2FA.
*   **Investment Plans**: Supports both fixed and variable ROI, where variable ROI uses a date-based seed for consistent daily rates across users for a given plan.
*   **Earnings System**:
    *   **Direct Sponsor Commission**: 8% on new investments paid immediately to the direct sponsor (uses 'commission' transaction type).
    *   **Profit Sharing (Multi-Level)**: Up to 3 levels of upline receive profit share when a downline user earns daily ROI, with configurable percentages per level (uses 'profit_share' transaction type).
    *   **Profit Sharing Shield**: Optional feature requiring users to have N direct referrals to receive Level N profit share, with configurable minimum combined investment threshold (`profit_sharing_shield_enabled`, `profit_sharing_shield_min_investment`). For Level N, the top N referrals' combined active investments must meet the minimum amount.
    *   **Commission Tiers System**: Tiered commission structure where users earn different commission rates based on their tier level. Tiers are determined by investment amount, direct referrals, and indirect referrals. Each tier has 3 commission levels with configurable percentages. Access via Admin > Referrals > Commission.
*   **Package Expiry System**: Investments expire when total earnings (ROI + commissions) reach a cap (3x base, 6x for qualified referrers on their oldest active package). A one-time bot fee of $10 is applied to the first package. Qualification for 6x multiplier depends on direct referrals or composite criteria across multiple levels.
*   **KYC Verification**: Supports both automated (Veriff SDK) and manual modes. Manual mode involves user document uploads (front, back, selfie) for admin review, with secure storage and path validation for documents.
*   **Dummy Users Management**: Admin interface to mark users as "dummy" and apply granular restrictions (withdraw disabled, ROI disabled, commission disabled, referral disabled) for testing or specific scenarios.
*   **Impersonation Logs**: Comprehensive audit trail for admin impersonation sessions:
    *   Records admin_id, impersonated_user_id, started_at, ended_at, duration
    *   Captures IP address and user agent for security auditing
    *   Admin view with filtering by admin, date range, and search
    *   Statistics: total logs, today's logs, active sessions, unique admins
    *   Accessible via sidebar under "Quick Actions" with users.impersonate permission
*   **Admin Dashboard**: Comprehensive analytics with improved financial metrics:
    *   **Performance Metrics**: User growth rate, deposit growth, conversion rate (users with deposits), retention rate (30-day active users)
    *   **Average Transaction Values**: Separate averages for deposits, withdrawals, and investments
    *   **Platform Health Metrics**: Net cash position (deposits - withdrawals), coverage ratio (cash/liabilities), payout ratio (withdrawals/deposits)
    *   **Revenue Tracking**: Bot fee revenue as actual platform income, separated from user fund flows
    *   **Liability Tracking**: User balances shown as platform liability, total payouts (ROI + commissions + bonuses)
    *   **Net Cashflow**: Today, this month, and all-time net cashflow (deposits minus withdrawals)
    *   **MLM Metrics**: Active investor count, commission payout ratio, pending commissions, daily ROI liability estimate
    *   **Exclusion Filters**: All metrics exclude dummy users and users marked as excluded_from_stats for accurate reporting
*   **Notifications**: Push notifications and email templating for user communication.
*   **Support System**: Integrated FAQ and ticket management.
*   **Leaderboard System**: Flexible referral-based leaderboards with advanced configuration options:
    *   Two leaderboard types: competitive (ranking-based prizes) and target (goal achievement with fixed prize)
    *   Two referral counting modes: "Direct Referrals Only" (Level 1) and "All Referrals (Multi-Level)" with configurable depth (2-20 levels)
    *   Optional minimum investment amount filter for referral qualification
    *   Automated position calculation every 15 minutes and auto-completion with retry logic
    *   Prize distribution to user wallets with scheduling and targeting options
    *   Social sharing, view tracking, and user-facing display controls
*   **Announcement System**: Comprehensive announcement management supporting both text-based and image-based announcements. Features include:
    *   Two announcement types: text (rich content) and image (visual banners with optional text)
    *   Image upload validation (JPEG, PNG, GIF, WebP, max 5MB)
    *   Target audience filtering (all users, active, verified, KYC verified, specific users)
    *   Scheduling and expiration dates
    *   Priority levels for display ordering
    *   View tracking with unique viewer counts
    *   Dismissibility options (allow users to close or require acknowledgment)
    *   User-facing modal display with appropriate styling for each type
    *   Automatic image cleanup on deletion or type switching
*   **Monthly Salary Program**: Recurring growth-based salary system for rewarding active network builders:
    *   **Application-Based Enrollment**: Users must meet Stage 1 requirements to apply for the program
    *   **Requirements to Apply**: Direct members (min $50 investment each), self deposit, and team count (all levels)
    *   **Monthly Growth Targets**: 
        *   35% team growth required each month (e.g., 60 members → 81 → 110 → 149)
        *   Plus 3 new direct referrals per month (min $50 investment each)
    *   **Evaluation Flow**: Monthly evaluations at period end, salary requires manual admin approval
    *   **Manual Salary Approval**: Admin must review and approve each salary payment via Evaluations page
    *   **Sequential Stage Progression**: Can only advance one stage at a time when meeting next stage's requirements
    *   **Stage Salaries**: Configurable per stage (e.g., Stage 1: $200, Stage 2: $400, etc.)
    *   **Failure Handling**: Missing targets marks application as failed; user can re-apply when eligible
    *   **Idempotency Protection**: Prevents duplicate evaluations for the same period
    *   **Console Commands**: 
        *   `salary:evaluate` - Process monthly evaluations (with --dry-run option)
        *   `salary:notify-eligible` - Notify eligible users at month start
    *   **Notifications**: Email/database notifications for eligibility, payments, and failures
    *   **Admin Views**: Applications list, evaluations history, manual evaluation trigger
    *   **User Views**: Eligibility check, application, progress tracking, payment history
    *   Creates 'salary' transaction type for tracking
    *   Full RBAC integration (salary.view, salary.manage permissions)
*   **Rank & Reward System**: Sequential rank achievement program rewarding user growth:
    *   5 initial ranks: Genesis Rock ($30), Quantum Stone ($70), Mythic Ore ($200), Celestial Peak ($300), Imperial Crown ($500)
    *   Sequential progression required (must achieve lower-ordered ranks before higher ones)
    *   Requirements per rank: Self Deposit (active investment), Direct Members (min $100 each), Team Members (optional, across all levels)
    *   One-time reward payment when rank is achieved
    *   Admin CRUD for ranks, achievement tracking, and manual reward payouts
    *   User-facing rank progress page with visual progress bars
    *   Console command `ranks:check` for automated rank checking (with optional `--pay-rewards` flag)
    *   Full RBAC integration (ranks.view, ranks.manage, ranks.payouts permissions)
    *   Creates 'rank_reward' transaction type for tracking
*   **Role-Based Access Control (RBAC)**: Comprehensive admin permissions system with:
    *   AdminRole model with customizable roles (Super Admin, Support Staff, Moderator, custom roles)
    *   AdminPermission model with 40+ granular permissions across 18 modules
    *   Role-permission pivot table for flexible permission assignment
    *   AdminPermissionMiddleware for route-level access control
    *   Super Admin role has full access and bypasses permission checks
    *   System roles (seeded defaults) can be edited but not deleted
    *   Admin sidebar visibility controlled by user permissions
    *   Admins without assigned roles are denied access (security enforced)
    *   Permission modules: dashboard, analytics, users, investments, withdrawals, deposits, kyc, commission, leaderboards, announcements, support, crm, settings, email, push, roles, salary, ranks
*   **Live Chat Support System**: Real-time customer support chat using Laravel Reverb WebSockets:
    *   Floating chat widget on user dashboard for instant support access
    *   Real-time messaging with WebSocket broadcasting (Laravel Reverb)
    *   Conversation status management (open, pending, resolved, closed)
    *   **Auto-Assignment System**: Chats auto-assign when staff views/claims them using atomic DB transactions with row locking to prevent race conditions
    *   **Staff Chat Limit**: Each staff member limited to 3 concurrent open chats (configurable in AdminChatStatsService::MAX_OPEN_CHATS)
    *   **Staff Performance Tracking**: admin_chat_stats table tracks total_chats_handled, chats_closed_today, average_response_time, last_active_at per staff member
    *   **Staff Dashboard Widget**: Shows open chats count, unassigned chats, pending tickets on staff dashboard
    *   **Super Admin Staff Overview**: View all staff performance stats, chat history, and individual staff activity (support.staff_chats permission)
    *   Message read tracking for both users and admins
    *   Admin chat dashboard with "My Chats" and "Available Chats" sections
    *   Polling-based updates for admin side (security-conscious design)
    *   Full RBAC integration (support.view, support.manage, support.staff_chats permissions)
    *   Systemd service documentation for VPS deployment (docs/reverb-systemd-service.md)

**System Design Choices:**
*   **Modular Codebase**: Organized into `app/Console`, `app/Http`, `app/Models`, `app/Services`, etc., for clear separation of concerns.
*   **Database Optimization**: Dashboard queries are optimized with caching and query consolidation to mitigate latency issues with external database connections.
*   **Secure Document Handling**: KYC documents are stored in a private disk with strict path validation to prevent unauthorized access.
*   **Environment Configuration**: Key settings are managed via `.env` variables for flexibility and security.

## External Dependencies
*   **Database**: PostgreSQL
*   **Payment Gateway**: Plisio (for cryptocurrency deposits and withdrawals, configured via API key and webhook).
*   **KYC Service**: Veriff (for automated identity verification).
*   **Frontend**: Bootstrap 5, Vite.
*   **Package Management**: Composer (PHP), Yarn (Node.js).