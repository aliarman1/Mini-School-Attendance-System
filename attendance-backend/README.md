# School Attendance System - Backend API

Laravel REST API for managing student attendance with advanced features including bulk recording, reports, events, and caching.

## Features

### 🎓 Student Management
- CRUD operations for students
- Search and filter by class/section
- Photo upload support
- Laravel Resource API responses
- Pagination support

### 📊 Attendance Module
- Bulk attendance recording
- Query-optimized attendance reports
- Monthly attendance reports with eager loading
- Statistics with Redis caching
- Today's attendance summary

### 🚀 Advanced Features
- **Service Layer**: Separated business logic in AttendanceService
- **Artisan Command**: `attendance:generate-report {month} {class}`
- **Events & Listeners**: AttendanceRecorded event for notifications
- **Redis Caching**: Optimized statistics queries
- **CORS**: Configured for frontend communication
- **Validation**: Request validation for all inputs

## Tech Stack

- **Framework**: Laravel 11
- **Database**: MySQL/PostgreSQL/SQLite
- **Authentication**: Laravel Sanctum
- **Caching**: Redis (optional, defaults to database)
- **Testing**: PHPUnit

## Installation

### Prerequisites
- PHP 8.2+
- Composer
- MySQL/PostgreSQL or SQLite
- Redis (optional)

### Setup Steps

1. **Clone the repository**
```bash
cd attendance-backend
```

2. **Install dependencies**
```bash
composer install
```

3. **Environment configuration**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure database in .env**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=attendance_db
DB_USERNAME=root
DB_PASSWORD=your_password

# Or use SQLite for quick setup
DB_CONNECTION=sqlite
```

5. **Configure frontend URL for CORS**
```env
FRONTEND_URL=http://localhost:5173
```

6. **Optional: Enable Redis caching**
```env
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

7. **Run migrations**
```bash
php artisan migrate
```

8. **Seed database with test data**
```bash
php artisan db:seed
```

9. **Create storage link**
```bash
php artisan storage:link
```

10. **Start the development server**
```bash
php artisan serve
```

API will be available at `http://localhost:8000`

## API Endpoints

### Students
- `GET /api/students` - List students (with pagination, search, filter)
- `POST /api/students` - Create student
- `GET /api/students/{id}` - Get student
- `PUT /api/students/{id}` - Update student
- `DELETE /api/students/{id}` - Delete student

### Attendance
- `POST /api/attendance/bulk` - Record bulk attendance
- `GET /api/attendance` - List attendance records
- `GET /api/attendance/{id}` - Get attendance record
- `PUT /api/attendance/{id}` - Update attendance
- `DELETE /api/attendance/{id}` - Delete attendance
- `GET /api/attendance/report/monthly?month=YYYY-MM&class=10A` - Monthly report
- `GET /api/attendance/statistics` - Attendance statistics (cached)
- `GET /api/attendance/today` - Today's attendance

## Artisan Commands

### Generate Monthly Report
```bash
php artisan attendance:generate-report 2025-11 10A
```

## Testing

Run the test suite:
```bash
php artisan test
```

Run specific test:
```bash
php artisan test --filter StudentTest
```

## Architecture

### SOLID Principles
- **Single Responsibility**: Controllers handle HTTP, Services handle business logic
- **Open/Closed**: Service layer extensible without modifying controllers
- **Liskov Substitution**: Interface-based design
- **Interface Segregation**: Specific request validation classes
- **Dependency Inversion**: Dependency injection throughout

### Service Layer
`AttendanceService` encapsulates:
- Bulk attendance recording with transactions
- Report generation with eager loading
- Statistics calculation with caching
- Business logic separation

### Events & Listeners
- `AttendanceRecorded` event fires on each attendance record
- `SendAttendanceNotification` listener (queued) logs/sends notifications
- Easily extensible for email/SMS notifications

### Caching Strategy
- Statistics cached for 1 hour
- Automatic cache invalidation on new records
- Supports both Redis and database caching

## Code Quality

- Request validation for all inputs
- Resource transformers for consistent API responses
- Query optimization with eager loading
- Transaction support for data integrity
- Error handling with try-catch blocks

## Test Credentials

After seeding, use these sample students:
- STU001 - John Doe (Class 10A)
- STU002 - Jane Smith (Class 10A)
- STU003 - Mike Johnson (Class 10A)
- ...and 7 more

## Project Structure

```
app/
├── Console/Commands/      # Artisan commands
├── Events/               # Event classes
├── Http/
│   ├── Controllers/      # API controllers
│   ├── Requests/         # Form validation
│   └── Resources/        # API resources
├── Listeners/            # Event listeners
├── Models/               # Eloquent models
└── Services/             # Business logic
database/
├── migrations/           # Database migrations
└── seeders/              # Database seeders
tests/
└── Feature/              # Feature tests
```

## License

MIT License
