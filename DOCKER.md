# Docker Setup Guide

Complete Docker containerization for the Mini School Attendance System with MySQL, Redis, Laravel backend, and Vue frontend.

## 🐳 What's Included

- **MySQL 8.0** - Production database
- **Redis 7** - Caching layer for performance
- **Laravel Backend** - PHP 8.2 with Nginx + PHP-FPM
- **Vue Frontend** - Production-optimized build with Nginx
- **Docker Compose** - Full orchestration with health checks
- **Development Mode** - Hot-reload for rapid development

## 📋 Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+
- 4GB+ RAM available
- Ports 3306, 5173, 6379, 8000 available

## 🚀 Quick Start (Production)

### Start All Services

```bash
# From project root
docker-compose up -d
```

This will:
1. Build backend and frontend images
2. Start MySQL and Redis
3. Wait for database health checks
4. Run migrations and seeders
5. Start backend API on port 8000
6. Start frontend on port 5173

### Access Applications

- **Frontend**: http://localhost:5173
- **Backend API**: http://localhost:8000/api
- **MySQL**: localhost:3306
- **Redis**: localhost:6379

### Default Credentials

```
Email: admin@school.com
Password: password
```

### Stop Services

```bash
docker-compose down
```

### Stop and Remove Volumes

```bash
docker-compose down -v
```

## 🔧 Development Mode (Hot Reload)

For active development with instant code changes:

```bash
# Use development compose file
docker-compose -f docker-compose.dev.yml up
```

**Development Features:**
- Hot module replacement (HMR) for Vue
- Laravel development server with auto-reload
- Debug mode enabled
- Volume mounting for live code changes
- No image rebuilds needed

## 📦 Individual Services

### Build Images

```bash
# Build backend only
docker-compose build backend

# Build frontend only
docker-compose build frontend

# Build all
docker-compose build
```

### View Logs

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f backend
docker-compose logs -f frontend
docker-compose logs -f mysql
docker-compose logs -f redis
```

### Execute Commands

```bash
# Laravel commands
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan test
docker-compose exec backend php artisan cache:clear

# Access backend shell
docker-compose exec backend sh

# Access MySQL
docker-compose exec mysql mysql -u attendance_user -p attendance_db
```

## 🏗️ Architecture

```
┌─────────────────────────────────────────────┐
│              Docker Network                  │
│         (attendance-network)                 │
│                                              │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐ │
│  │  MySQL   │  │  Redis   │  │ Backend  │ │
│  │  :3306   │  │  :6379   │  │  :8000   │ │
│  └──────────┘  └──────────┘  └────┬─────┘ │
│       ▲              ▲              │       │
│       │              │              │       │
│       └──────────────┴──────────────┘       │
│                      ▲                       │
│                      │                       │
│               ┌──────┴─────┐               │
│               │  Frontend  │               │
│               │   :5173    │               │
│               └────────────┘               │
└─────────────────────────────────────────────┘
```

## 📊 Service Details

### MySQL Container
- **Image**: mysql:8.0
- **Port**: 3306
- **Database**: attendance_db
- **User**: attendance_user
- **Password**: attendance_pass
- **Volume**: mysql_data (persistent)
- **Health Check**: mysqladmin ping

### Redis Container
- **Image**: redis:7-alpine
- **Port**: 6379
- **Volume**: redis_data (persistent)
- **Health Check**: redis-cli ping
- **Use**: Statistics caching, session storage

### Backend Container
- **Base**: php:8.2-fpm-alpine
- **Port**: 8000
- **Services**: Nginx + PHP-FPM (Supervisor)
- **PHP Extensions**: pdo, mysqli, mbstring, zip, gd, bcmath
- **Features**:
  - Composer dependencies pre-installed
  - Automatic migrations on startup
  - Database seeding
  - Configuration caching
  - SQLite fallback support

### Frontend Container
- **Build Stage**: node:18-alpine
- **Production Stage**: nginx:alpine
- **Port**: 5173
- **Features**:
  - Multi-stage build (optimized size)
  - Gzip compression enabled
  - Static asset caching (1 year)
  - SPA routing support
  - Health check endpoint

## 🔐 Environment Variables

### Backend (.env)
```env
APP_ENV=production
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=attendance_db
DB_USERNAME=attendance_user
DB_PASSWORD=attendance_pass
CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PORT=6379
FRONTEND_URL=http://localhost:5173
```

### Frontend (.env)
```env
VITE_API_URL=http://localhost:8000/api
```

## 🛠️ Troubleshooting

### Services Won't Start

```bash
# Check service status
docker-compose ps

# Check logs for errors
docker-compose logs backend
docker-compose logs mysql

# Restart specific service
docker-compose restart backend
```

### Database Connection Issues

```bash
# Wait for MySQL health check
docker-compose ps mysql

# Verify database exists
docker-compose exec mysql mysql -u root -proot_password -e "SHOW DATABASES;"

# Recreate database
docker-compose down -v
docker-compose up -d
```

### Port Already in Use

```bash
# Find process using port
lsof -i :8000
lsof -i :5173

# Change ports in docker-compose.yml
ports:
  - "8001:8000"  # Backend on 8001
  - "5174:5173"  # Frontend on 5174
```

### Permission Issues

```bash
# Fix storage permissions
docker-compose exec backend chown -R www-data:www-data /var/www/html/storage
docker-compose exec backend chmod -R 755 /var/www/html/storage
```

### Clear Everything and Restart

```bash
# Stop all containers
docker-compose down -v

# Remove images
docker-compose down --rmi all

# Rebuild from scratch
docker-compose build --no-cache
docker-compose up -d
```

## 📈 Performance Optimization

### Production Checklist

- ✅ Redis caching enabled
- ✅ Configuration caching (config:cache)
- ✅ Route caching (route:cache)
- ✅ Composer optimized (--optimize-autoloader --no-dev)
- ✅ Frontend assets minified
- ✅ Gzip compression enabled
- ✅ Static asset caching (1 year)
- ✅ Health checks configured

### Resource Limits

Add to docker-compose.yml:

```yaml
services:
  backend:
    deploy:
      resources:
        limits:
          cpus: '1'
          memory: 512M
        reservations:
          cpus: '0.5'
          memory: 256M
```

## 🔒 Security Considerations

### Production Hardening

1. **Change Default Passwords**
   ```yaml
   environment:
     MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
     MYSQL_PASSWORD: ${MYSQL_PASSWORD}
   ```

2. **Remove Debug Mode**
   ```env
   APP_DEBUG=false
   APP_ENV=production
   ```

3. **Use Secrets**
   ```bash
   docker secret create mysql_password ./mysql_password.txt
   ```

4. **Network Isolation**
   - Only expose necessary ports
   - Use internal networks for service communication

5. **Regular Updates**
   ```bash
   docker-compose pull
   docker-compose up -d
   ```

## 🎯 Use Cases

### Local Development
```bash
docker-compose -f docker-compose.dev.yml up
```

### Production Deployment
```bash
docker-compose up -d
```

### CI/CD Testing
```bash
docker-compose run backend php artisan test
```

### Database Backup
```bash
docker-compose exec mysql mysqldump -u attendance_user -p attendance_db > backup.sql
```

### Database Restore
```bash
docker-compose exec -T mysql mysql -u attendance_user -p attendance_db < backup.sql
```

## 📚 Additional Resources

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Laravel Deployment](https://laravel.com/docs/deployment)
- [Vue.js Production Deployment](https://vuejs.org/guide/best-practices/production-deployment.html)

## ✅ Docker Setup Complete

You now have a fully containerized attendance system with:
- Production-ready configuration
- Development mode with hot-reload
- Database persistence
- Redis caching
- Health checks
- Multi-stage builds
- Security best practices

**Ready to deploy anywhere Docker runs!** 🚀
