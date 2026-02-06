# ElgioTik - MikroTik Management System

## Project Overview

ElgioTik is a comprehensive Laravel 10-based MikroTik router management system designed for managing village WiFi networks (WISP - Wireless ISP). It provides complete hotspot user management, voucher generation, bandwidth control, customer management, and real-time monitoring capabilities.

## Technology Stack

- **Framework**: Laravel 10
- **PHP**: 8.1+
- **Database**: MySQL/PostgreSQL
- **Frontend**:
  - Tailwind CSS (UI framework)
  - Alpine.js (JavaScript framework)
  - Blade Templates
- **MikroTik Integration**: RouterOS API PHP (`evilfreelancer/routeros-api-php`)

## Project Purpose

This system enables village WiFi operators to:
- Manage multiple MikroTik routers from a single interface
- Generate and sell WiFi vouchers with different bandwidth plans
- Track customer information and usage
- Monitor real-time hotspot users and sessions
- Generate reports and analytics
- Control bandwidth allocation per user
- Manage staff with role-based access control (Admin, Staff, Cashier)

## Development Progress

### ✅ Completed Tasks

1. **Project Setup**
   - ✅ Created Laravel 10 project named 'elgiotik'
   - ✅ Installed RouterOS API PHP package
   - ✅ Configured Tailwind CSS
   - ✅ Configured Alpine.js
   - ✅ Set up PostCSS

2. **Database Layer**
   - ✅ Created all database migrations:
     - `users` table (with role and is_active columns)
     - `routers` table
     - `bandwidth_plans` table
     - `customers` table
     - `vouchers` table
     - `hotspot_users` table
     - `user_sessions` table
     - `settings` table

3. **Models and Relationships**
   - ✅ User model (with roles: admin, staff, cashier)
   - ✅ Router model
   - ✅ BandwidthPlan model
   - ✅ Customer model
   - ✅ Voucher model
   - ✅ HotspotUser model
   - ✅ UserSession model
   - ✅ Setting model
   - ✅ All model relationships properly defined
   - ✅ Query scopes for common queries
   - ✅ Helper methods for business logic

### 🔄 In Progress

4. **MikroTik Service Layer**
   - ⏳ RouterOS API service implementation
   - ⏳ Connection management
   - ⏳ Hotspot user CRUD operations
   - ⏳ User profile management
   - ⏳ Active connections monitoring
   - ⏳ Traffic statistics retrieval

### 📋 Pending Tasks

5. **Authentication System**
   - ⏳ Role-based middleware
   - ⏳ Login/logout functionality
   - ⏳ User management interface
   - ⏳ Password reset

6. **Router Management**
   - ⏳ Router CRUD operations
   - ⏳ Connection testing
   - ⏳ Status monitoring
   - ⏳ Router dashboard

7. **Voucher System**
   - ⏳ Voucher generation (single/batch)
   - ⏳ Voucher code generation algorithm
   - ⏳ Voucher activation
   - ⏳ Voucher sales tracking
   - ⏳ Expiration management
   - ⏳ Print voucher templates

8. **Customer Management**
   - ⏳ Customer CRUD operations
   - ⏳ Customer search and filtering
   - ⏳ Purchase history
   - ⏳ Customer statistics

9. **Bandwidth Plans**
   - ⏳ Plan CRUD operations
   - ⏳ Speed limit configuration
   - ⏳ Time/data limits
   - ⏳ Pricing management

10. **Hotspot User Management**
    - ⏳ User creation/deletion
    - ⏳ Online users monitoring
    - ⏳ Disconnect users
    - ⏳ Usage statistics
    - ⏳ User search and filtering

11. **Dashboard**
    - ⏳ Main dashboard with statistics
    - ⏳ Revenue charts
    - ⏳ Active users count
    - ⏳ Router status overview
    - ⏳ Recent activities
    - ⏳ Quick actions

12. **Reports & Analytics**
    - ⏳ Sales reports
    - ⏳ Usage reports
    - ⏳ Customer reports
    - ⏳ Revenue analytics
    - ⏳ Export to Excel/PDF

13. **UI Implementation**
    - ⏳ Main layout with sidebar
    - ⏳ Navigation menu
    - ⏳ Data tables with search/filter
    - ⏳ Forms with validation
    - ⏳ Modals and notifications
    - ⏳ Responsive design
    - ⏳ Dark mode support (optional)

14. **Settings Management**
    - ⏳ Application settings
    - ⏳ Hotspot settings
    - ⏳ Billing settings
    - ⏳ Email/SMS configuration

## Database Schema

### Users Table
- Authentication and role management
- Roles: admin, staff, cashier
- Tracks created customers and sold vouchers

### Routers Table
- MikroTik router information
- Connection credentials
- Status tracking (online/offline/maintenance)

### Bandwidth Plans Table
- Speed limits (upload/download)
- Validity periods (hours/days)
- Data limits
- Pricing
- Session and idle timeouts

### Customers Table
- Customer contact information
- Purchase tracking
- Creator tracking

### Vouchers Table
- Unique voucher codes
- Status tracking (active/used/expired/disabled)
- Linked to bandwidth plans and routers
- Sales tracking (who sold, when)
- Activation tracking

### Hotspot Users Table
- Active hotspot accounts
- Usage statistics (bytes in/out, session time)
- Online status tracking
- Expiration dates
- MAC and IP binding

### User Sessions Table
- Historical session data
- Duration and traffic statistics
- Termination reasons

### Settings Table
- Key-value configuration storage
- Grouped by category
- Type-aware (string, integer, boolean, json)

## Key Features

### User Roles & Permissions
- **Admin**: Full system access
- **Staff**: Manage customers, vouchers, view reports
- **Cashier**: Sell vouchers, view basic info

### Voucher Management
- Generate single or batch vouchers
- Multiple bandwidth plans
- Expiration tracking
- Sales tracking
- Activation monitoring

### Real-time Monitoring
- Online users count
- Active sessions
- Router status
- Traffic usage
- Revenue tracking

### Customer Management
- Complete customer database
- Purchase history
- Active services tracking
- Custom notes

### Reporting
- Sales reports (daily/weekly/monthly)
- Usage statistics
- Revenue analytics
- Customer insights
- Export capabilities

## API Integration

### MikroTik RouterOS API
- Hotspot user management
- Active connection monitoring
- Traffic statistics
- User disconnect/enable/disable
- Profile management

## File Structure

```
elgiotik/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php
│   │   │   ├── RouterController.php
│   │   │   ├── BandwidthPlanController.php
│   │   │   ├── CustomerController.php
│   │   │   ├── VoucherController.php
│   │   │   ├── HotspotUserController.php
│   │   │   ├── ReportController.php
│   │   │   └── SettingController.php
│   │   └── Middleware/
│   │       └── RoleMiddleware.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Router.php
│   │   ├── BandwidthPlan.php
│   │   ├── Customer.php
│   │   ├── Voucher.php
│   │   ├── HotspotUser.php
│   │   ├── UserSession.php
│   │   └── Setting.php
│   └── Services/
│       └── MikroTikService.php
├── database/
│   └── migrations/
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   ├── dashboard/
│   │   ├── routers/
│   │   ├── bandwidth-plans/
│   │   ├── customers/
│   │   ├── vouchers/
│   │   ├── hotspot-users/
│   │   ├── reports/
│   │   └── settings/
│   └── css/
│       └── app.css
└── routes/
    └── web.php
```

## Next Steps

1. ✅ Complete model relationships
2. 🔄 Implement MikroTik service layer
3. Build authentication and role middleware
4. Create controllers for all resources
5. Build main dashboard UI
6. Implement router management
7. Create voucher generation system
8. Build customer management interface
9. Implement hotspot user monitoring
10. Create reporting features
11. Add settings management
12. Polish UI with Tailwind CSS
13. Add Alpine.js interactivity
14. Testing and bug fixes

## Configuration Requirements

### Environment Variables
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=elgiotik
DB_USERNAME=root
DB_PASSWORD=

# MikroTik Router (Default/Primary)
MIKROTIK_HOST=
MIKROTIK_PORT=8728
MIKROTIK_USERNAME=
MIKROTIK_PASSWORD=
```

## Installation Steps (For Deployment)

1. Clone repository
2. Run `composer install`
3. Run `npm install`
4. Copy `.env.example` to `.env`
5. Configure database credentials
6. Run `php artisan key:generate`
7. Run `php artisan migrate`
8. Run `php artisan db:seed` (when seeders are ready)
9. Run `npm run build`
10. Configure web server
11. Add first admin user

## Business Logic

### Voucher Workflow
1. Admin creates bandwidth plans
2. Staff generates vouchers for specific plans
3. Cashier sells vouchers to customers
4. Customer uses voucher code to connect
5. System activates voucher and creates hotspot user
6. User connects to WiFi
7. System monitors usage and enforces limits
8. Voucher expires based on time/data limits

### Router Management
1. Admin adds router with connection details
2. System tests connection
3. Router status monitored periodically
4. All hotspot operations sent to appropriate router
5. Statistics collected from router

### Customer Journey
1. Customer visits operator
2. Operator (cashier) selects bandwidth plan
3. System generates/assigns voucher
4. Customer receives printed voucher
5. Customer connects using voucher code
6. System tracks all customer activity
7. Customer can purchase additional vouchers

## Security Considerations

- Router credentials encrypted in database
- Role-based access control
- Password hashing for users
- CSRF protection
- Input validation
- SQL injection prevention (Eloquent ORM)
- XSS protection (Blade templating)

## Performance Optimization

- Database indexing on foreign keys
- Query optimization with eager loading
- Caching for settings and statistics
- Background jobs for heavy operations
- Pagination for large datasets

---

**Project Status**: 🚧 In Active Development
**Current Phase**: Building Core Services
**Completion**: ~25%

Last Updated: 2026-02-06
