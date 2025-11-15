# School Attendance System - Backend API

Laravel REST API for managing student attendance with advanced features including bulk recording, monthly reports, class/section management, and real-time statistics.

## Features

### 🎓 Student Management
- CRUD operations for students with photo upload
- Advanced search and filtering (by name, student ID, class, section)
- Pagination support with customizable page size
- Class and Section relationship management
- Photo upload with validation (JPEG/PNG, max 2MB)
- Laravel Resource API responses

### 📚 Class & Section Management
- Manage multiple classes (e.g., 9, 10, 11, 12)
- Organize students into sections (e.g., A, B, C)
- Foreign key relationships for data integrity
- Student count tracking per class/section
- Capacity management for classes

### 📊 Attendance Module
- **Bulk attendance recording** - Mark multiple students at once
- **Date-based attendance** - Track daily attendance with status (present, absent, late)
- **Monthly reports** - Generate comprehensive attendance reports
- **Statistics dashboard** - Real-time attendance statistics with caching
- **Today's summary** - Quick view of today's attendance
- **Filtering** - Filter by date range, status, student, class, or section
- **Notes support** - Add optional notes for each attendance record

### 🚀 Advanced Features
- **Service Layer**: Separated business logic in `AttendanceService`
- **Artisan Command**: `attendance:generate-report` with multiple export formats
- **Events & Listeners**: `AttendanceRecorded` event for notifications
- **Redis Caching**: Optimized statistics queries (1-hour cache)
- **CORS Configured**: Ready for frontend communication
- **Comprehensive Validation**: Request validation for all inputs
- **Transaction Support**: Database transactions for data integrity
- **Query Optimization**: Eager loading for performance

## Tech Stack

- **Framework**: Laravel 11
- **Database**: MySQL/PostgreSQL/SQLite
- **Authentication**: Laravel Sanctum
- **Caching**: Redis (optional, falls back to database)
- **Testing**: PHPUnit with 42 feature tests
- **PHP Version**: 8.2+

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- MySQL/PostgreSQL or SQLite
- Redis (optional, for caching)
- Node.js & NPM (for frontend)

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

7. **Run migrations and seeders**
```bash
php artisan migrate:fresh --seed
```

This will create:
- 4 classes (9, 10, 11, 12)
- 3 sections (A, B, C)
- 25 sample students distributed across classes and sections

8. **Create storage link**
```bash
php artisan storage:link
```

9. **Start the development server**
```bash
php artisan serve
```

API will be available at `http://localhost:8000`

## Database Structure

### Tables

#### `classes`
- `id` - Primary key
- `name` - Class name (e.g., "9", "10", "11", "12")
- `capacity` - Maximum students (default: 40)
- `description` - Optional description
- `created_at`, `updated_at`

#### `sections`
- `id` - Primary key
- `name` - Section name (e.g., "A", "B", "C")
- `description` - Optional description
- `created_at`, `updated_at`

#### `students`
- `id` - Primary key
- `name` - Student full name
- `student_id` - Unique student identifier (e.g., "STU001")
- `class_id` - Foreign key to `classes.id`
- `section_id` - Foreign key to `sections.id`
- `photo` - Optional photo path
- `created_at`, `updated_at`

#### `attendances`
- `id` - Primary key
- `student_id` - Foreign key to `students.id`
- `date` - Attendance date
- `status` - Enum: 'present', 'absent', 'late'
- `note` - Optional notes (max 500 characters)
- `recorded_by_id` - Foreign key to `users.id` (nullable)
- `created_at`, `updated_at`
- Unique constraint on (`student_id`, `date`)

### Relationships

```
ClassModel → students (hasMany)
Section → students (hasMany)
Student → class (belongsTo)
Student → section (belongsTo)
Student → attendances (hasMany)
Attendance → student (belongsTo)
Attendance → recordedBy (belongsTo User)
```

## API Endpoints

### Authentication
All endpoints require authentication via Laravel Sanctum (except register/login).

```
POST /api/register    - Register new user
POST /api/login       - Login user
POST /api/logout      - Logout user
GET  /api/user        - Get authenticated user
```

### Classes
```
GET    /api/classes           - List all classes (with student count)
POST   /api/classes           - Create new class
GET    /api/classes/{id}      - Get class details
PUT    /api/classes/{id}      - Update class
DELETE /api/classes/{id}      - Delete class
```

**Query Parameters:**
- `search` - Search by class name
- `per_page` - Items per page (default: 15)

### Sections
```
GET    /api/sections          - List all sections (with student count)
POST   /api/sections          - Create new section
GET    /api/sections/{id}     - Get section details
PUT    /api/sections/{id}     - Update section
DELETE /api/sections/{id}     - Delete section
```

**Query Parameters:**
- `search` - Search by section name
- `per_page` - Items per page (default: 15)

### Students
```
GET    /api/students          - List students (paginated, filterable)
POST   /api/students          - Create student
GET    /api/students/{id}     - Get student details
PUT    /api/students/{id}     - Update student
DELETE /api/students/{id}     - Delete student
```

**Query Parameters:**
- `search` - Search by name or student ID
- `class_id` - Filter by class
- `section_id` - Filter by section
- `per_page` - Items per page (default: 15)

**Request Body (POST/PUT):**
```json
{
  "name": "John Doe",
  "student_id": "STU001",
  "class_id": 1,
  "section_id": 2,
  "photo": "base64_encoded_image or file upload"
}
```

### Attendance
```
POST   /api/attendance/bulk              - Record bulk attendance
GET    /api/attendance                   - List attendance records
GET    /api/attendance/{id}              - Get attendance record
PUT    /api/attendance/{id}              - Update attendance
DELETE /api/attendance/{id}              - Delete attendance
GET    /api/attendance/report/monthly    - Monthly attendance report
GET    /api/attendance/statistics        - Attendance statistics (cached)
GET    /api/attendance/today             - Today's attendance summary
```

**Bulk Attendance Request:**
```json
{
  "date": "2025-11-15",
  "attendances": [
    {
      "student_id": 1,
      "status": "present",
      "note": "On time"
    },
    {
      "student_id": 2,
      "status": "absent",
      "note": "Sick leave"
    },
    {
      "student_id": 3,
      "status": "late",
      "note": "Arrived 10 minutes late"
    }
  ]
}
```

**Monthly Report Request:**
```
GET /api/attendance/report/monthly?month=2025-11&class_id=1
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "student_id": 1,
      "student_name": "John Doe",
      "student_number": "STU001",
      "total_days": 20,
      "present": 18,
      "absent": 1,
      "late": 1,
      "attendance_percentage": 90.0
    }
  ],
  "meta": {
    "month": "2025-11",
    "class_id": 1
  }
}
```

**Query Parameters (List):**
- `start_date` - Filter from date (YYYY-MM-DD)
- `end_date` - Filter to date (YYYY-MM-DD)
- `status` - Filter by status (present/absent/late)
- `student_id` - Filter by student
- `class_id` - Filter by class
- `per_page` - Items per page (default: 15)

## Artisan Commands

### Generate Monthly Attendance Report

Generate comprehensive attendance reports with multiple export formats.

**Basic Usage:**
```bash
php artisan attendance:generate-report {month} {class} [options]
```

**Arguments:**
- `month` - Month in YYYY-MM format (e.g., 2025-11)
- `class` - Class name (e.g., 9, 10) or class ID

**Options:**
- `--section=` - Optional section name (e.g., A, B) or section ID
- `--format=` - Output format: table (default), csv, json
- `--output=` - Custom output file path

**Examples:**

1. **Table output (default):**
```bash
php artisan attendance:generate-report 2025-11 9
```

2. **Filter by section:**
```bash
php artisan attendance:generate-report 2025-11 10 --section=A
```

3. **Export to CSV:**
```bash
php artisan attendance:generate-report 2025-11 9 --format=csv
# Saves to: storage/app/reports/attendance_report_9_202511.csv
```

4. **Export to JSON:**
```bash
php artisan attendance:generate-report 2025-11 9 --format=json
# Saves to: storage/app/reports/attendance_report_9_202511.json
```

5. **Custom output path:**
```bash
php artisan attendance:generate-report 2025-11 9 --format=csv --output=/tmp/report.csv
```

6. **Using IDs instead of names:**
```bash
php artisan attendance:generate-report 2025-11 1 --section=2
```

**Output Example:**
```
Attendance Report
Class: 9 - Section: A
Month: 2025-11
====================================================================================================

+------------+---------------+------------+---------+--------+------+------------+
| Student ID | Name          | Total Days | Present | Absent | Late | Percentage |
+------------+---------------+------------+---------+--------+------+------------+
| STU001     | John Doe      | 20         | 18      | 2      | 0    | 90.00%     |
| STU002     | Jane Smith    | 20         | 20      | 0      | 0    | 100.00%    |
+------------+---------------+------------+---------+--------+------+------------+

Summary Statistics:
Total Students: 2
Total Days Recorded: 40
Total Present: 38
Total Absent: 2
Total Late: 0
Overall Attendance: 95.00%

✓ Report generated successfully!
```

## Testing

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suite
```bash
php artisan test tests/Feature/StudentTest.php
php artisan test tests/Feature/AttendanceTest.php
php artisan test tests/Feature/ReportTest.php
```

### Run Tests with Coverage
```bash
php artisan test --coverage
```

### Test Coverage

The system includes **42 comprehensive tests**:

- **StudentTest** (13 tests)
  - CRUD operations
  - Search and filtering
  - Validation tests
  - Class/section filtering

- **AttendanceTest** (19 tests)
  - Bulk attendance recording
  - Attendance validation
  - Date filtering
  - Status filtering
  - CRUD operations
  - Today's attendance
  - Statistics

- **ReportTest** (15 tests)
  - Monthly report generation
  - Percentage calculations
  - Section filtering
  - Data accuracy
  - Edge cases

## Architecture

### SOLID Principles

- **Single Responsibility**: Controllers handle HTTP, Services handle business logic
- **Open/Closed**: Service layer extensible without modifying controllers
- **Liskov Substitution**: Interface-based design
- **Interface Segregation**: Specific request validation classes
- **Dependency Inversion**: Dependency injection throughout

### Service Layer

`AttendanceService` encapsulates:
- Bulk attendance recording with database transactions
- Monthly report generation with eager loading
- Statistics calculation with Redis caching
- Cache invalidation on data changes
- Business logic separation from controllers

### Events & Listeners

```php
Event: AttendanceRecorded
├── Listener: SendAttendanceNotification (queued)
└── Easily extensible for email/SMS notifications
```

When attendance is recorded:
1. `AttendanceRecorded` event fires
2. Listeners process notifications asynchronously
3. Cache is automatically invalidated

### Caching Strategy

- Statistics cached for 1 hour
- Automatic cache invalidation on new records
- Supports both Redis and database caching
- Cache key: `attendance_statistics`

### Request Validation

All inputs are validated using Form Request classes:

- `BulkAttendanceRequest` - Bulk attendance validation
- Student/Class/Section validation in controllers
- Custom error messages
- Array validation for bulk operations

## Code Quality

- ✅ Request validation for all inputs
- ✅ Resource transformers for consistent API responses
- ✅ Query optimization with eager loading
- ✅ Transaction support for data integrity
- ✅ Error handling with try-catch blocks
- ✅ PHPDoc comments throughout
- ✅ SOLID principles followed
- ✅ Factory and seeders for testing
- ✅ Comprehensive test coverage

## Sample Data

After running seeders, you'll have:

**Classes:**
- Class 9 (capacity: 40)
- Class 10 (capacity: 40)
- Class 11 (capacity: 40)
- Class 12 (capacity: 40)

**Sections:**
- Section A
- Section B
- Section C

**Students:**
- 25 students distributed across classes 9-12 and sections A-C
- Sample student IDs: STU001, STU002, STU003, etc.

## Project Structure

```
attendance-backend/
├── app/
│   ├── Console/Commands/
│   │   └── AttendanceGenerateReport.php    # Report generation command
│   ├── Events/
│   │   └── AttendanceRecorded.php          # Attendance event
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AttendanceController.php    # Attendance endpoints
│   │   │   ├── ClassController.php         # Class management
│   │   │   ├── SectionController.php       # Section management
│   │   │   └── StudentController.php       # Student management
│   │   ├── Requests/
│   │   │   └── BulkAttendanceRequest.php   # Validation
│   │   └── Resources/
│   │       ├── AttendanceResource.php      # API resource
│   │       └── StudentResource.php         # API resource
│   ├── Listeners/
│   │   └── SendAttendanceNotification.php  # Event listener
│   ├── Models/
│   │   ├── Attendance.php                  # Attendance model
│   │   ├── ClassModel.php                  # Class model
│   │   ├── Section.php                     # Section model
│   │   ├── Student.php                     # Student model
│   │   └── User.php                        # User model
│   └── Services/
│       └── AttendanceService.php           # Business logic
├── database/
│   ├── factories/
│   │   ├── AttendanceFactory.php           # Test data factory
│   │   ├── StudentFactory.php              # Test data factory
│   │   └── UserFactory.php                 # Test data factory
│   ├── migrations/                         # Database migrations
│   │   ├── xxxx_create_classes_table.php
│   │   ├── xxxx_create_sections_table.php
│   │   ├── xxxx_create_students_table.php
│   │   └── xxxx_create_attendances_table.php
│   └── seeders/
│       ├── ClassSeeder.php                 # Seed classes
│       ├── SectionSeeder.php               # Seed sections
│       ├── StudentSeeder.php               # Seed students
│       └── DatabaseSeeder.php              # Master seeder
├── routes/
│   └── api.php                             # API routes
├── tests/
│   └── Feature/
│       ├── AttendanceTest.php              # 19 tests
│       ├── ReportTest.php                  # 15 tests
│       └── StudentTest.php                 # 13 tests
├── storage/
│   └── app/
│       └── reports/                        # Generated reports
└── .env.example                            # Environment template
```

## API Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { /* resource data */ }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "error": "Detailed error description"
}
```

### Paginated Response
```json
{
  "success": true,
  "data": [ /* items */ ],
  "pagination": {
    "total": 100,
    "per_page": 15,
    "current_page": 1,
    "last_page": 7
  }
}
```

## Performance Optimization

- **Eager Loading**: Prevents N+1 queries
  ```php
  Attendance::with(['student.class', 'recordedBy'])
  ```

- **Redis Caching**: Statistics cached for 1 hour
  ```php
  Cache::remember('attendance_statistics', 3600, function() {...})
  ```

- **Database Indexing**: 
  - Foreign keys indexed
  - Unique constraint on (student_id, date)

- **Query Optimization**:
  - `whereBetween` for date ranges
  - `whereHas` for relationship filtering

## Security

- ✅ Laravel Sanctum authentication
- ✅ CORS protection with configurable origins
- ✅ Input validation on all endpoints
- ✅ SQL injection protection (Eloquent ORM)
- ✅ XSS protection
- ✅ CSRF protection
- ✅ Password hashing (bcrypt)
- ✅ File upload validation (size, type)

## Troubleshooting

### Database Connection Error
```bash
# Check database credentials in .env
php artisan config:clear
php artisan migrate
```

### Redis Connection Error
```bash
# Disable Redis caching in .env
CACHE_STORE=database
```

### Storage Link Issue
```bash
php artisan storage:link
chmod -R 775 storage
```

### Permission Errors
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## License

MIT License - see LICENSE file for details

## Support

For issues and questions:
- Create an issue on GitHub
- Check existing documentation
- Review test files for usage examples

## Changelog

### Version 2.0.0 (Current)
- ✅ Complete database restructure with foreign keys
- ✅ Class and Section management
- ✅ Updated Artisan command with multiple export formats
- ✅ 42 comprehensive tests
- ✅ Enhanced API endpoints
- ✅ Improved error handling

### Version 1.0.0
- Initial release with basic attendance tracking
