<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# Student Welfare Fund API

A production-ready Laravel API for managing student welfare fund applications, donations, and programs.

## Features

- **Authentication**: Laravel Sanctum with phone-based login
- **Role-Based Access Control**: Admin, Reviewer, and User roles
- **Public Catalog**: Browse categories and programs
- **Donations**: Quick and gift donations with payment integration
- **Student Applications**: Comprehensive application system with document uploads
- **Admin Panel**: Full CRUD operations for categories, programs, and applications
- **API Documentation**: Auto-generated Swagger/OpenAPI documentation
- **Rate Limiting**: Configurable rate limits for sensitive endpoints
- **Queue System**: Async processing for notifications and payments
- **Audit Logging**: Comprehensive logging for security and compliance

## Tech Stack

- **Framework**: Laravel 12
- **PHP**: 8.2+
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **Authorization**: Spatie Laravel Permission
- **API Documentation**: L5-Swagger
- **Queue**: Redis
- **File Storage**: Local/Public disk

## Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL 8.0 or higher
- Redis (for queues)
- Node.js & NPM (for frontend assets)

### Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd student_welfare_fund_backend
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure environment variables**
   Edit `.env` file with your database and other configurations:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=student_welfare_fund
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   
   REDIS_HOST=127.0.0.1
   REDIS_PORT=6379
   
   QUEUE_CONNECTION=redis
   ```

5. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Create storage link**
   ```bash
   php artisan storage:link
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```

8. **Start queue worker (in separate terminal)**
   ```bash
   php artisan queue:work
   ```

## API Documentation

Once the application is running, you can access the API documentation at:
- **Swagger UI**: `http://localhost:8000/docs`
- **OpenAPI JSON**: `http://localhost:8000/docs.json`

## API Endpoints

### Public Endpoints (No Authentication Required)

#### Categories
- `GET /api/v1/categories` - Get all active categories

#### Programs
- `GET /api/v1/programs` - Get programs with filtering and pagination
- `GET /api/v1/programs/{id}` - Get specific program details
- `GET /api/v1/donations/recent` - Get recent donations

#### Donations
- `POST /api/v1/donations` - Create a quick donation
- `POST /api/v1/donations/gift` - Create a gift donation
- `GET /api/v1/donations/{id}/status` - Check donation status
- `GET /api/v1/payments/callback` - Payment callback endpoint
- `POST /api/v1/payments/webhook` - Payment webhook endpoint

#### Authentication
- `POST /api/v1/auth/register` - Register new user
- `POST /api/v1/auth/login` - User login

### Protected Endpoints (Authentication Required)

#### User Profile
- `GET /api/v1/auth/me` - Get current user profile
- `POST /api/v1/auth/logout` - User logout
- `GET /api/v1/me/settings` - Get user settings
- `PATCH /api/v1/me/settings` - Update user settings
- `GET /api/v1/me/donations` - Get user's donations

#### Student Applications
- `POST /api/v1/students/applications` - Create application
- `GET /api/v1/students/applications` - Get user's applications
- `GET /api/v1/students/applications/{id}` - Get specific application
- `POST /api/v1/students/applications/{id}/documents` - Upload documents

### Admin Endpoints (Admin Role Required)

#### Categories Management
- `GET /api/v1/admin/categories` - List categories
- `POST /api/v1/admin/categories` - Create category
- `PATCH /api/v1/admin/categories/{id}` - Update category
- `DELETE /api/v1/admin/categories/{id}` - Delete category

#### Programs Management
- `GET /api/v1/admin/programs` - List programs
- `POST /api/v1/admin/programs` - Create program
- `PATCH /api/v1/admin/programs/{id}` - Update program
- `DELETE /api/v1/admin/programs/{id}` - Delete program

#### Applications Management
- `GET /api/v1/admin/applications` - List applications
- `PATCH /api/v1/admin/applications/{id}/status` - Update application status

#### Donations Management
- `GET /api/v1/admin/donations` - List donations

## Database Structure

### Core Tables
- `users` - User accounts with phone-based authentication
- `categories` - Program categories (soft deletes)
- `programs` - Welfare programs (soft deletes)
- `donations` - Donation records with UUIDs
- `gift_meta` - Gift donation metadata
- `student_applications` - Student applications with JSON fields
- `audit_logs` - System audit trail

### Permission Tables
- `roles` - User roles (admin, reviewer, user)
- `permissions` - System permissions
- `role_has_permissions` - Role-permission relationships
- `model_has_roles` - User-role relationships

## Testing

Run the test suite:
```bash
php artisan test
```

Run specific test:
```bash
php artisan test --filter CatalogTest
```

## Queue Jobs

The application uses Redis queues for:
- Payment webhook processing
- WhatsApp notifications
- Email notifications
- Document processing

## Security Features

- **Rate Limiting**: Configurable limits on auth and donation endpoints
- **Idempotency**: Prevents duplicate donations using Idempotency-Key header
- **Webhook Verification**: Secure payment webhook processing
- **Audit Logging**: Comprehensive event logging
- **Soft Deletes**: Safe deletion for admin-managed entities
- **Input Validation**: Comprehensive form request validation

## Configuration

### Rate Limiting
Configure rate limits in `.env`:
```env
RATE_LIMIT_AUTH=60,1    # 60 requests per minute
RATE_LIMIT_DONATIONS=10,1  # 10 requests per minute
```

### Payment Integration
Configure payment provider settings:
```env
PAYMENT_PROVIDER=stripe
PAYMENT_WEBHOOK_SECRET=your_webhook_secret
PAYMENT_PUBLIC_KEY=your_public_key
PAYMENT_SECRET_KEY=your_secret_key
```

### Notifications
Configure WhatsApp and email notifications:
```env
WHATSAPP_ENABLED=true
WHATSAPP_API_URL=https://api.whatsapp.com
WHATSAPP_API_TOKEN=your_token
WHATSAPP_PHONE_NUMBER=your_phone
```

## Deployment

### Production Checklist

1. **Environment**
   - Set `APP_ENV=production`
   - Set `APP_DEBUG=false`
   - Configure production database
   - Set up Redis for queues

2. **Security**
   - Generate strong `APP_KEY`
   - Configure HTTPS
   - Set up proper file permissions
   - Configure firewall rules

3. **Performance**
   - Enable OPcache
   - Configure Redis for caching
   - Set up CDN for static assets
   - Configure queue workers

4. **Monitoring**
   - Set up logging
   - Configure health checks
   - Set up error tracking
   - Monitor queue performance

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## License

This project is licensed under the MIT License.

## Support

For support and questions, please contact the development team.
