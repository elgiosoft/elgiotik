# ElgioTik - MikroTik Management System

[![Laravel](https://img.shields.io/badge/Laravel-10-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

A comprehensive Laravel-based MikroTik router management system designed for managing village WiFi networks (WISP - Wireless ISP). ElgioTik provides complete hotspot user management, voucher generation, bandwidth control, customer management, and real-time monitoring capabilities.

## Features

### Core Functionality
- **Multi-Router Management**: Manage multiple MikroTik routers from a single interface
- **Voucher System**: Generate and sell WiFi vouchers with different bandwidth plans
- **Customer Management**: Track customer information, purchase history, and usage
- **Real-time Monitoring**: Monitor active hotspot users and sessions
- **Bandwidth Control**: Define and enforce bandwidth plans per user
- **Reports & Analytics**: Generate sales, revenue, and usage reports
- **Role-Based Access**: Admin, Staff, and Cashier roles with appropriate permissions

### Technical Features
- MikroTik RouterOS API integration
- Real-time connection status monitoring
- Batch voucher generation
- Voucher printing templates
- User session tracking
- Traffic statistics
- Automated expiration management

## Technology Stack

- **Framework**: Laravel 10
- **PHP**: 8.1+
- **Database**: MySQL/PostgreSQL
- **Frontend**:
  - Tailwind CSS (UI framework)
  - Alpine.js (JavaScript framework)
  - Blade Templates
- **MikroTik Integration**: RouterOS API PHP (`evilfreelancer/routeros-api-php`)

## Requirements

- PHP >= 8.1
- Composer
- Node.js & NPM
- MySQL >= 5.7 or PostgreSQL >= 12
- MikroTik Router with API access enabled

## Installation

### 1. Clone the repository
```bash
git clone https://github.com/elgiosoft/elgiotik.git
cd elgiotik
```

### 2. Install dependencies
```bash
composer install
npm install
```

### 3. Environment configuration
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure database
Edit `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=elgiotik
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Run migrations
```bash
php artisan migrate
```

### 6. Build assets
```bash
npm run build
```

### 7. Serve the application
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## Default Credentials

After running seeders (when available), use these credentials:

- **Admin**: admin@elgiotik.com / password
- **Staff**: staff@elgiotik.com / password
- **Cashier**: cashier@elgiotik.com / password

## Configuration

### MikroTik Router Setup

1. Enable API access on your MikroTik router:
```
/ip service enable api
/ip service set api port=8728
```

2. Create API user (recommended):
```
/user add name=api-user password=your-password group=full
```

3. Add router in ElgioTik:
   - Navigate to Routers → Add Router
   - Fill in router details (IP, port, username, password)
   - Test connection

## User Roles & Permissions

### Admin
- Full system access
- Manage routers
- Manage bandwidth plans
- View all reports
- Manage system settings
- Manage users

### Staff
- Manage customers
- Generate and manage vouchers
- View basic reports
- Monitor hotspot users

### Cashier
- Sell vouchers
- View customer information
- Basic dashboard access

## Main Features Guide

### Router Management
1. Add MikroTik routers with connection details
2. Test connection status
3. Sync hotspot users from router
4. Monitor online users
5. View router statistics

### Bandwidth Plans
1. Create bandwidth plans with speed limits
2. Set validity periods (hours/days)
3. Define data limits
4. Set pricing
5. Configure session and idle timeouts

### Voucher System
1. **Single Voucher**: Generate one voucher at a time
2. **Batch Generation**: Generate multiple vouchers with custom prefix
3. **Sell Voucher**: Assign voucher to customer and mark as sold
4. **Print Vouchers**: Print voucher cards for distribution
5. **Track Status**: Monitor active, used, expired, and disabled vouchers

### Customer Management
1. Add customer contact information
2. Track purchase history
3. View active vouchers
4. Monitor usage statistics
5. Add custom notes

### Hotspot Users
1. View all hotspot users across routers
2. Monitor online/offline status
3. Check usage statistics (data in/out, session time)
4. Disconnect users remotely
5. Enable/disable user accounts

### Dashboard
- Overview statistics (routers, customers, revenue, users)
- Revenue charts and trends
- Top selling bandwidth plans
- Top customers by spending
- Recent activity

## API Endpoints

The system includes API endpoints for external integration:

- `GET /api/dashboard/statistics` - Get dashboard statistics
- `GET /api/dashboard/revenue-chart` - Get revenue trend data
- `GET /api/dashboard/online-users` - Get online users per router

## Database Structure

### Main Tables
- `users` - System users (admin, staff, cashier)
- `routers` - MikroTik router information
- `bandwidth_plans` - Speed and pricing plans
- `customers` - Customer database
- `vouchers` - Voucher codes and status
- `hotspot_users` - Active hotspot accounts
- `user_sessions` - Historical session data
- `settings` - System configuration

## Development

### Running in development mode
```bash
# Terminal 1: Run Laravel development server
php artisan serve

# Terminal 2: Watch and compile assets
npm run dev
```

### Code Style
This project follows PSR-12 coding standards for PHP.

### Testing
```bash
php artisan test
```

## Troubleshooting

### MikroTik Connection Issues
- Ensure API service is enabled on the router
- Verify firewall rules allow API access
- Check credentials are correct
- Confirm router is reachable from server

### Voucher Activation Issues
- Ensure router is online
- Verify bandwidth plan exists on router
- Check hotspot server is running on router

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## Security

If you discover any security vulnerabilities, please email security@elgiosoft.com instead of using the issue tracker.

## Roadmap

- [ ] Complete all view templates
- [ ] Report generation (PDF/Excel export)
- [ ] SMS notification integration
- [ ] Email notification system
- [ ] Multi-language support
- [ ] Mobile app
- [ ] Payment gateway integration
- [ ] API documentation
- [ ] Automated backups
- [ ] Dark mode

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## Credits

- **Developer**: ElgioSoft
- **Framework**: [Laravel](https://laravel.com)
- **MikroTik API**: [RouterOS PHP Client](https://github.com/EvilFreelancer/routeros-api-php)
- **UI Framework**: [Tailwind CSS](https://tailwindcss.com)
- **JavaScript**: [Alpine.js](https://alpinejs.dev)

## Support

For support, email support@elgiosoft.com or create an issue in the repository.

---

**Version**: 1.0.0
**Last Updated**: 2026-02-06
**Status**: Active Development (~70% Complete)
