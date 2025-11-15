# Mini School Attendance System

A comprehensive full-stack web application for managing school attendance with real-time statistics, bulk recording, monthly reports, and an intuitive user interface.

## Project Overview

This project is a complete student attendance management system built with modern web technologies. It features a RESTful API backend (Laravel 11) and a reactive single-page application frontend (Vue 3), designed following SOLID principles and best practices.

### Key Highlights

- **Full-Stack Solution**: Laravel 11 + Vue 3 SPA architecture
- **Real-time Statistics**: Live attendance metrics with caching
- **Bulk Operations**: Record attendance for entire classes at once
- **Advanced Filtering**: Multi-criteria search and filtering
- **Monthly Reports**: Comprehensive attendance analytics
- **Professional UI**: Modern, responsive design with modal notifications
- **42 Tests**: Comprehensive test coverage for reliability
- **SOLID Principles**: Service layer, events, and clean architecture

## Architecture

```
student/
├── attendance-backend/         Laravel 11 REST API
│   ├── app/
│   │   ├── Console/
│   │   │   └── Commands/       Artisan commands
│   │   ├── Http/
│   │   │   ├── Controllers/    API controllers
│   │   │   ├── Requests/       Validation classes
│   │   │   └── Resources/      API response transformers
│   │   ├── Models/             Eloquent models
│   │   ├── Services/           Business logic layer
│   │   └── Events/             Event-driven features
│   ├── database/
│   │   ├── migrations/         Database schema
│   │   ├── factories/          Test data generators
│   │   └── seeders/            Sample data
│   ├── routes/
│   │   └── api.php             API route definitions
│   └── tests/                  PHPUnit tests (42 tests)
│
├── attendance-frontend/        Vue 3 SPA
│   ├── src/
│   │   ├── pages/              Vue page components
│   │   ├── stores/             Pinia state management
│   │   ├── services/           API integration
│   │   ├── router.js           Vue Router config
│   │   └── App.vue             Main application
│   └── public/                 Static assets
│
├── README.md                   This file
└── AI_WORKFLOW.md              AI assistance documentation
```

## Features

### Backend (Laravel 11)

- **Student Management**: CRUD operations with photo upload, search, and filtering
- **Class & Section Management**: Organize students into classes and sections
- **Attendance Recording**: Bulk recording with transaction support
- **Monthly Reports**: Generate comprehensive attendance statistics
- **Service Layer**: Separated business logic (`AttendanceService`)
- **Events & Listeners**: Notification system for attendance recording
- **Artisan Command**: `attendance:generate-report` with multiple export formats
- **Redis Caching**: Optimized statistics queries (1-hour cache)
- **API Resources**: Consistent JSON response formatting
- **Comprehensive Testing**: 42 PHPUnit tests covering all features

### Frontend (Vue 3)

- **Dashboard**: Real-time statistics with Chart.js visualizations
- **Students Page**: CRUD operations with advanced search and filtering
- **Classes Page**: Manage classes with capacity and student tracking
- **Sections Page**: Organize and manage sections
- **Attendance Page**: Bulk marking interface with live percentage calculation
- **Reports Page**: Generate and view monthly attendance reports
- **Modal Notifications**: Professional success/error/warning popups
- **Pinia State Management**: Reactive stores for all data
- **Responsive Design**: Mobile-first, works on all devices
- **Loading States**: User-friendly loading indicators

## Tech Stack

### Backend
- **Framework**: Laravel 11
- **Language**: PHP 8.2+
- **Database**: MySQL/PostgreSQL/SQLite
- **Authentication**: Laravel Sanctum
- **Caching**: Redis (optional)
- **Testing**: PHPUnit
- **API**: RESTful with Resource transformers

### Frontend
- **Framework**: Vue 3 (Composition API)
- **State Management**: Pinia
- **Routing**: Vue Router 4
- **HTTP Client**: Axios
- **Charts**: Chart.js + vue-chartjs
- **Build Tool**: Vite
- **Styling**: Custom CSS (Grid + Flexbox)

## Quick Start

### Prerequisites

- PHP 8.2+ with Composer
- Node.js 18+ with npm
- MySQL/PostgreSQL or SQLite
- Redis (optional, for caching)

### Installation

#### 1. Backend Setup

```bash
# Navigate to backend directory
cd attendance-backend

# Install dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Configure database in .env
# For quick setup, use SQLite:
DB_CONNECTION=sqlite

# Or configure MySQL:
DB_CONNECTION=mysql
DB_DATABASE=attendance_db
DB_USERNAME=root
DB_PASSWORD=your_password

# Set frontend URL for CORS
FRONTEND_URL=http://localhost:5173

# Run migrations and seeders
php artisan migrate --seed

# Start development server
php artisan serve
```

Backend will be available at: `http://localhost:8000`

#### 2. Frontend Setup

```bash
# Navigate to frontend directory
cd attendance-frontend

# Install dependencies
npm install

# Configure API URL
echo "VITE_API_URL=http://localhost:8000/api" > .env

# Start development server
npm run dev
```

Frontend will be available at: `http://localhost:5173`

### Default Login Credentials

After running seeders:
```
Email: admin@school.com
Password: password
```

## API Documentation

### Base URL
```
http://localhost:8000/api
```

### Main Endpoints

#### Authentication
```
POST   /login              Login user
POST   /logout             Logout user
GET    /user               Get authenticated user
```

#### Students
```
GET    /students           List students (paginated, searchable)
POST   /students           Create student
GET    /students/{id}      Get single student
PUT    /students/{id}      Update student
DELETE /students/{id}      Delete student
```

#### Classes
```
GET    /classes            List all classes
POST   /classes            Create class
GET    /classes/{id}       Get single class
PUT    /classes/{id}       Update class
DELETE /classes/{id}       Delete class
```

#### Sections
```
GET    /sections           List all sections
POST   /sections           Create section
GET    /sections/{id}      Get single section
PUT    /sections/{id}      Update section
DELETE /sections/{id}      Delete section
```

#### Attendance
```
POST   /attendance                      Record bulk attendance
GET    /attendance                      List attendance records (filtered)
GET    /attendance/today                Today's summary
GET    /attendance/statistics           Overall statistics (cached)
GET    /attendance/report/monthly       Monthly report by class
```

### Query Parameters

**Students Endpoint:**
- `search` - Search by name or student ID
- `class_id` - Filter by class
- `section_id` - Filter by section
- `per_page` - Results per page (default: 15)

**Attendance Endpoint:**
- `date` - Filter by specific date
- `status` - Filter by status (present/absent/late)
- `student_id` - Filter by student
- `class_id` - Filter by class
- `section_id` - Filter by section

For complete API documentation, see: [attendance-backend/README.md](attendance-backend/README.md)

## Artisan Commands

### Generate Attendance Report

```bash
# Generate report for a class
php artisan attendance:generate-report "Class 10" --month=11 --year=2025

# Specify section
php artisan attendance:generate-report "Class 10" --section="A"

# Export as CSV
php artisan attendance:generate-report "Class 10" --format=csv --output=report.csv

# Export as JSON
php artisan attendance:generate-report "Class 10" --format=json --output=report.json
```

## Testing

### Run Backend Tests

```bash
cd attendance-backend

# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/AttendanceTest.php
```

**Test Coverage:**
- 42 tests total
- Student CRUD operations (8 tests)
- Attendance recording and bulk operations (19 tests)
- Monthly reports and statistics (15 tests)

## Project Structure

### Database Schema

#### Users Table
- id, name, email, password, timestamps

#### Classes Table
- id, name, capacity, timestamps

#### Sections Table
- id, name, class_id, timestamps

#### Students Table
- id, name, student_id, date_of_birth, photo
- class_id, section_id, timestamps

#### Attendances Table
- id, student_id, date, status (present/absent/late)
- recorded_by_id, notes, timestamps

### Frontend Pages

1. **Dashboard** (`/`) - Statistics and charts
2. **Students** (`/students`) - Student management
3. **Classes** (`/classes`) - Class management
4. **Sections** (`/sections`) - Section management
5. **Attendance** (`/attendance`) - Bulk recording
6. **Reports** (`/reports`) - Monthly reports

For detailed frontend documentation, see: [attendance-frontend/README.md](attendance-frontend/README.md)

## Key Features Explained

### Bulk Attendance Recording

Record attendance for an entire class/section at once:
1. Select class and section
2. Students are loaded automatically
3. Use "Mark All Present/Absent/Late" shortcuts
4. Or set individual statuses
5. Add optional notes
6. Submit all at once with validation

### Service Layer Pattern

Business logic is separated into service classes:
- `AttendanceService`: Handles all attendance-related operations
- Database transactions for data integrity
- Cache invalidation after updates
- Query optimization with eager loading

### Modal Notifications

Custom modal system (no browser alerts):
- **Success Modal**: Green checkmark for successful operations
- **Error Modal**: Red X with specific error messages
- **Warning Modal**: Yellow triangle for delete confirmations
- Smooth animations (fade + slide)
- Click outside or OK to close

### Real-time Statistics

Dashboard shows live statistics:
- Total students, classes, sections
- Today's attendance count and percentage
- Overall attendance percentage (cached for 1 hour)
- Chart.js doughnut chart visualization
- Monthly attendance trends

## SOLID Principles Applied

1. **Single Responsibility**: Each controller handles one resource
2. **Open/Closed**: Service layer allows extension without modification
3. **Liskov Substitution**: Interfaces and abstractions used throughout
4. **Interface Segregation**: Small, focused interfaces
5. **Dependency Inversion**: Services injected via dependency injection

## Development with AI

This project was developed with significant AI assistance (OpenCode/Claude). See [AI_WORKFLOW.md](AI_WORKFLOW.md) for detailed documentation on:
- What AI generated vs. manual coding
- Specific prompts used and their outcomes
- Development speed improvements (9x faster)
- Lessons learned and best practices

**AI-Generated Code**: ~93%  
**Manual Coding**: ~7%  
**Development Time**: ~3 hours (vs. ~27 hours estimated without AI)

## Common Issues & Solutions

### Backend Issues

**Issue**: CORS errors when connecting from frontend
```bash
# Solution: Update FRONTEND_URL in .env
FRONTEND_URL=http://localhost:5173
```

**Issue**: Database connection failed
```bash
# Solution: For quick setup, use SQLite
DB_CONNECTION=sqlite
php artisan migrate:fresh --seed
```

**Issue**: Cache not working
```bash
# Solution: Clear cache
php artisan cache:clear
php artisan config:clear
```

### Frontend Issues

**Issue**: API requests failing
```bash
# Solution: Check API URL in .env
VITE_API_URL=http://localhost:8000/api
```

**Issue**: 401 Unauthorized errors
```bash
# Solution: Login again, token may have expired
# Or clear localStorage in browser DevTools
```

## Performance Optimizations

- **Redis Caching**: Statistics cached for 1 hour
- **Eager Loading**: Relationships loaded efficiently
- **Pagination**: Large datasets loaded in chunks
- **Query Optimization**: N+1 query problem avoided
- **API Resources**: Consistent, optimized responses
- **Vite Build**: Optimized production builds with code splitting

## Security Features

- **Sanctum Authentication**: Token-based API authentication
- **CSRF Protection**: Laravel's built-in CSRF protection
- **Input Validation**: Request validation classes
- **SQL Injection Prevention**: Eloquent ORM with parameter binding
- **XSS Protection**: Vue's automatic escaping
- **File Upload Validation**: Type and size restrictions

## Production Deployment

### Backend

```bash
# Install dependencies
composer install --optimize-autoloader --no-dev

# Set production environment
APP_ENV=production
APP_DEBUG=false

# Generate key and cache
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force
```

### Frontend

```bash
# Build for production
npm run build

# Output in dist/ directory
# Deploy to web server (Nginx, Apache, etc.)
```

## License

This project is created for educational purposes.

## Contact & Support

For issues, questions, or contributions:
- Review backend documentation: [attendance-backend/README.md](attendance-backend/README.md)
- Review frontend documentation: [attendance-frontend/README.md](attendance-frontend/README.md)
- Check AI workflow documentation: [AI_WORKFLOW.md](AI_WORKFLOW.md)

## Contributors

This project was developed as part of a programming assignment demonstrating:
- Full-stack web development skills
- Modern framework usage (Laravel 11 + Vue 3)
- SOLID principles and clean architecture
- Test-driven development
- AI-assisted development workflows
- Professional documentation practices

---

**Built with Laravel 11, Vue 3, and AI assistance**
