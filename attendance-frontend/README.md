# School Attendance System - Frontend

Modern Vue 3 SPA for managing school attendance with real-time statistics, interactive charts, and comprehensive class/section management.

## Features

### 📊 Dashboard
- **Real-time statistics** - Live attendance metrics with auto-refresh
- **Today's attendance summary** - Quick overview of daily attendance
- **Interactive charts** - Beautiful doughnut charts (Chart.js)
- **Monthly trends** - Monthly attendance percentage visualization
- **Recent activity feed** - Latest attendance records
- **Responsive cards** - Clean, card-based layout

### 🏫 Class Management
- **CRUD Operations** - Create, read, update, delete classes
- **Capacity management** - Set maximum students per class
- **Student count tracking** - Real-time student enrollment counts
- **Search functionality** - Quick search by class name
- **Pagination** - Navigate through large class lists
- **Modal notifications** - Success/error/warning popups

### 📚 Section Management
- **Section organization** - Manage sections (A, B, C, etc.)
- **CRUD Operations** - Full section management
- **Student tracking** - View students per section
- **Search & filter** - Quick section lookup
- **Modal notifications** - Professional feedback modals

### 🎓 Student Management
- **Complete CRUD** - Add, edit, view, delete students
- **Photo upload** - Student photo support (JPEG/PNG, max 2MB)
- **Advanced search** - Search by name or student ID
- **Multi-filter** - Filter by class AND section simultaneously
- **Real-time validation** - Form validation with error messages
- **Pagination** - Efficient data loading
- **Responsive table** - Mobile-friendly student list
- **Modal notifications** - Success/error/delete confirmation modals

### ✅ Attendance Recording
- **Bulk attendance marking** - Mark entire class at once
- **Class & Section selection** - Filter students precisely
- **Real-time percentage calculator** - Live attendance statistics
- **Quick actions** - Mark all present/absent/late shortcuts
- **Individual control** - Per-student status selection
- **Optional notes** - Add comments for each attendance record
- **Live summary** - Real-time count and percentage display
- **Modal notifications** - Success/error feedback on submission
- **Date selection** - Record attendance for any date
- **Validation** - Ensures complete data before submission

### 📈 Reports
- **Monthly reports** - Generate class-based attendance reports
- **Detailed statistics** - Individual student performance
- **Percentage calculations** - Automatic attendance percentages
- **Export options** - Download reports in various formats

## Tech Stack

- **Framework**: Vue 3 (Composition API with `<script setup>`)
- **State Management**: Pinia (modern Vuex alternative)
- **Routing**: Vue Router 4
- **HTTP Client**: Axios with interceptors
- **Charts**: Chart.js + vue-chartjs
- **Build Tool**: Vite (fast HMR & optimized builds)
- **Styling**: Custom CSS with CSS Grid & Flexbox
- **Icons**: SVG icons (inline)

## Installation

### Prerequisites
- Node.js 18+ (LTS recommended)
- npm or yarn package manager
- Backend API running on port 8000

### Setup Steps

1. **Navigate to frontend directory**
```bash
cd attendance-frontend
```

2. **Install dependencies**
```bash
npm install
```

3. **Configure environment**
```bash
# Create .env file
echo "VITE_API_URL=http://localhost:8000/api" > .env
```

Or manually create `.env`:
```env
VITE_API_URL=http://localhost:8000/api
```

4. **Start development server**
```bash
npm run dev
```

Frontend will be available at `http://localhost:5173`

5. **Build for production**
```bash
npm run build
```

Output will be in `dist/` directory.

6. **Preview production build**
```bash
npm run preview
```

## Project Structure

```
attendance-frontend/
├── public/                      # Static assets
├── src/
│   ├── pages/                   # Page components
│   │   ├── Dashboard.vue        # Dashboard with stats & charts
│   │   ├── Students.vue         # Student management (CRUD)
│   │   ├── Classes.vue          # Class management
│   │   ├── Sections.vue         # Section management
│   │   ├── Attendance.vue       # Attendance recording
│   │   └── Reports.vue          # Attendance reports
│   ├── stores/                  # Pinia stores
│   │   ├── student.js           # Student state management
│   │   ├── attendance.js        # Attendance state management
│   │   ├── class.js             # Class state management
│   │   └── section.js           # Section state management
│   ├── services/                # API services
│   │   └── api.js               # Axios configuration & interceptors
│   ├── components/              # Reusable components
│   ├── assets/                  # Images, fonts, etc.
│   ├── App.vue                  # Main app component with navigation
│   ├── main.js                  # App entry point
│   ├── router.js                # Route definitions
│   └── style.css                # Global styles
├── index.html                   # HTML entry point
├── vite.config.js               # Vite configuration
├── package.json                 # Dependencies
└── .env                         # Environment variables
```

## Features Overview

### 📊 Dashboard Page (`/`)
**Statistics Cards:**
- Total students enrolled
- Today's present count
- Today's absent count
- Today's late count

**Chart Visualization:**
- Doughnut chart showing present/absent/late distribution
- Color-coded segments (green/red/yellow)
- Animated transitions

**Monthly Statistics:**
- Current month attendance percentage
- Total records for the month

**Recent Activity:**
- Latest 10 attendance entries
- Student name, class, status, and date
- Color-coded status badges

### 🏫 Classes Page (`/classes`)
**Features:**
- Create new classes (9, 10, 11, 12)
- Edit existing classes
- Delete classes with confirmation modal
- Set class capacity (max students)
- Add optional descriptions
- View student count per class
- Search by class name

**Modal System:**
- ✅ Success modal (green) - Class created/updated/deleted
- ❌ Error modal (red) - Operation failed
- ⚠️ Warning modal (yellow) - Delete confirmation

### 📚 Sections Page (`/sections`)
**Features:**
- Create new sections (A, B, C, etc.)
- Edit existing sections
- Delete sections with confirmation modal
- Add optional descriptions
- View student count per section
- Search by section name

**Modal System:**
- ✅ Success modal - Section created/updated/deleted
- ❌ Error modal - Operation failed
- ⚠️ Warning modal - Delete confirmation

### 🎓 Students Page (`/students`)
**Student List:**
- Paginated table view (15 per page)
- Search bar (debounced, 500ms)
- Class dropdown filter
- Section dropdown filter
- Combined class + section filtering
- Student photo thumbnails
- Action buttons (Edit/Delete)

**Add/Edit Student Modal:**
- Student ID input (required, unique)
- Full name input (required)
- Class selection (required)
- Section selection (required)
- Photo upload (optional, max 2MB)
- Photo preview before upload
- Validation with error messages
- Scrollable modal body
- Sticky header and footer

**Modal System:**
- ✅ Success modal - Student created/updated/deleted
- ❌ Error modal - Operation failed with details
- ⚠️ Warning modal - Delete confirmation with student name

### ✅ Attendance Page (`/attendance`)
**Recording Interface:**
- Date picker (defaults to today)
- Class dropdown selection
- Section dropdown selection (loads after class)
- Student list loads automatically
- Status selection per student (present/absent/late)
- Optional notes field per student
- Bulk actions: Mark all present/absent/late

**Live Summary:**
- Total students displayed
- Present count (green)
- Absent count (red)
- Late count (yellow)
- Attendance percentage

**Validation:**
- Ensures class and section selected
- Ensures date is set
- Visual feedback on selection

**Modal System:**
- ✅ Success modal - Attendance recorded successfully
- ❌ Error modal - Submission failed with error details

### 📈 Reports Page (`/reports`)
**Monthly Reports:**
- Select month (YYYY-MM format)
- Select class
- Generate button
- Detailed student-wise breakdown
- Individual percentages
- Total days, present, absent, late counts

## State Management (Pinia Stores)

### Student Store (`stores/student.js`)
```javascript
State:
- students: []
- loading: false
- error: null
- pagination: {}

Actions:
- fetchStudents(params)      // Get paginated list
- createStudent(data)         // Add new student
- updateStudent(id, data)     // Modify student
- deleteStudent(id)           // Remove student
- clearState()                // Reset state
```

### Class Store (`stores/class.js`)
```javascript
State:
- classes: []
- loading: false
- error: null
- pagination: {}

Actions:
- fetchClasses(params)        // Get paginated list
- fetchAllClasses()           // Get all (for dropdowns)
- createClass(data)           // Add new class
- updateClass(id, data)       // Modify class
- deleteClass(id)             // Remove class
```

### Section Store (`stores/section.js`)
```javascript
State:
- sections: []
- loading: false
- error: null
- pagination: {}

Actions:
- fetchSections(params)       // Get paginated list
- fetchAllSections()          // Get all (for dropdowns)
- createSection(data)         // Add new section
- updateSection(id, data)     // Modify section
- deleteSection(id)           // Remove section
```

### Attendance Store (`stores/attendance.js`)
```javascript
State:
- attendances: []
- statistics: {}
- loading: false
- error: null

Actions:
- recordBulkAttendance(data)      // Submit attendance
- fetchStatistics()               // Get dashboard stats
- fetchTodayAttendance()          // Today's records
- generateMonthlyReport(params)   // Generate report
- clearState()                    // Reset state
```

## API Integration

### Axios Configuration (`services/api.js`)

**Base Configuration:**
```javascript
baseURL: import.meta.env.VITE_API_URL
timeout: 30000
headers: {
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}
```

**Interceptors:**
- **Request Interceptor**: Auto-inject auth tokens
- **Response Interceptor**: Handle errors globally
- **Error Handling**: Consistent error messages

**API Endpoints:**
```javascript
// Classes
GET    /classes
POST   /classes
PUT    /classes/:id
DELETE /classes/:id

// Sections
GET    /sections
POST   /sections
PUT    /sections/:id
DELETE /sections/:id

// Students
GET    /students
POST   /students
PUT    /students/:id
DELETE /students/:id

// Attendance
POST   /attendance/bulk
GET    /attendance
GET    /attendance/statistics
GET    /attendance/today
GET    /attendance/report/monthly
```

## UI/UX Features

### Navigation Bar
- **Full-width responsive design** - CSS Grid layout
- **5 breakpoints** - 1400px, 1024px, 768px, 480px
- **Modern gradient** - Professional color scheme
- **Active page highlighting** - Visual feedback
- **Mobile hamburger menu** - Collapsible navigation
- **Smooth transitions** - Animated interactions

### Modal System
All modals include:
- **Smooth animations** - Fade in + slide up
- **Click outside to close** - Better UX
- **Responsive design** - Works on mobile
- **Visual feedback** - Color-coded icons
- **Clear messaging** - Action confirmation

**Modal Types:**
1. **Success Modal (Green)** - ✓ Checkmark icon
2. **Error Modal (Red)** - ✗ X icon  
3. **Warning Modal (Yellow)** - ⚠ Triangle icon

### Form Modals
- **Fixed header** - Always visible
- **Scrollable body** - Long forms supported
- **Sticky footer** - Buttons always accessible
- **Max height** - Fits in viewport
- **Validation** - Real-time error display

### Responsive Design
**Mobile (<768px):**
- Stacked navigation items
- Full-width buttons
- Horizontal scroll tables
- Collapsible filters
- Touch-friendly controls

**Tablet (768px-1024px):**
- Grid layout adjustments
- Optimized spacing
- Readable font sizes

**Desktop (>1024px):**
- Full grid layouts
- Multi-column forms
- Wide tables
- Sidebar navigation

### Color Scheme
```css
Primary: #2563eb (Blue)
Success: #28a745 (Green)
Warning: #ffc107 (Yellow)
Danger: #dc3545 (Red)
Info: #17a2b8 (Cyan)
Gray: #6b7280

Status Colors:
- Present: #28a745
- Absent: #dc3545
- Late: #ffc107
```

### Typography
- **Font Family**: System fonts for best performance
- **Headings**: Bold, clear hierarchy
- **Body**: 16px base size
- **Responsive scaling**: Viewport-based

## Browser Support

✅ **Fully Supported:**
- Chrome 90+ (latest)
- Firefox 88+ (latest)
- Safari 14+ (latest)
- Edge 90+ (latest)

⚠️ **Limited Support:**
- IE 11 (not recommended)
- Older mobile browsers

## Development

### Hot Module Replacement (HMR)
Vite provides instant updates during development without full page reload.

### Code Organization
- **Composition API**: Modern Vue 3 syntax
- **`<script setup>`**: Cleaner, more concise code
- **Reactive State**: Built-in reactivity with `ref()` and `reactive()`
- **Computed Properties**: Optimized calculations
- **Watchers**: Side effects on data changes
- **Lifecycle Hooks**: Component lifecycle management

### Best Practices
- ✅ Single Responsibility Principle
- ✅ Reusable components
- ✅ Centralized state management
- ✅ Error handling throughout
- ✅ Loading states for async operations
- ✅ Form validation
- ✅ Debounced search
- ✅ Optimistic UI updates

## Production Build

### Build for Production
```bash
npm run build
```

**Output:**
- Minified JavaScript bundles
- Optimized CSS
- Compressed assets
- Source maps (optional)
- Tree-shaking applied

**Build Optimizations:**
- Code splitting
- Lazy loading routes
- Asset optimization
- Gzip compression ready

### Preview Production Build
```bash
npm run preview
```

Serves the production build locally for testing.

### Deployment

**Static Hosting (Recommended):**
- Netlify
- Vercel
- GitHub Pages
- AWS S3 + CloudFront
- Firebase Hosting

**Configuration for SPA Routing:**
Create `dist/_redirects` (Netlify) or equivalent:
```
/*    /index.html   200
```

## Environment Variables

### Development (`.env`)
```env
VITE_API_URL=http://localhost:8000/api
```

### Production (`.env.production`)
```env
VITE_API_URL=https://your-api-domain.com/api
```

**Note:** Vite only exposes variables prefixed with `VITE_`

## Troubleshooting

### CORS Errors
**Issue:** Browser blocks API requests

**Solution:** Ensure backend `.env` has:
```env
FRONTEND_URL=http://localhost:5173
```

### API Connection Failed
**Issue:** Cannot connect to backend

**Solutions:**
1. Check backend is running: `php artisan serve`
2. Verify `VITE_API_URL` in `.env`
3. Check browser console for errors
4. Test API endpoint: `curl http://localhost:8000/api/students`

### Charts Not Showing
**Issue:** Dashboard charts are blank

**Solution:** Ensure Chart.js is installed:
```bash
npm install chart.js vue-chartjs
```

### Build Errors
**Issue:** `npm run build` fails

**Solutions:**
1. Clear node_modules: `rm -rf node_modules && npm install`
2. Clear Vite cache: `rm -rf node_modules/.vite`
3. Check Node.js version: `node --version` (should be 18+)

### Port Already in Use
**Issue:** Port 5173 is already taken

**Solution:** Use different port:
```bash
npm run dev -- --port 3000
```

### Hot Reload Not Working
**Issue:** Changes don't appear automatically

**Solutions:**
1. Restart dev server: `Ctrl+C` then `npm run dev`
2. Clear browser cache
3. Check file watchers limit (Linux): `echo fs.inotify.max_user_watches=524288 | sudo tee -a /etc/sysctl.conf`

## Performance Tips

### Optimize Bundle Size
- Use lazy loading for routes
- Import only needed components
- Tree-shake unused code
- Compress images before upload

### Improve Load Time
- Enable HTTP/2
- Use CDN for static assets
- Implement service workers
- Add caching headers

### Enhance User Experience
- Add loading skeletons
- Implement infinite scroll (instead of pagination)
- Add pull-to-refresh (mobile)
- Cache API responses locally

## Security

✅ **Implemented:**
- XSS protection (Vue sanitizes by default)
- CSRF protection (API handles)
- Secure HTTP headers
- Input validation
- File upload restrictions

⚠️ **Recommended:**
- Use HTTPS in production
- Implement rate limiting
- Add CSP headers
- Regular dependency updates

## Contributing

1. Fork the repository
2. Create feature branch: `git checkout -b feature/AmazingFeature`
3. Commit changes: `git commit -m 'Add AmazingFeature'`
4. Push to branch: `git push origin feature/AmazingFeature`
5. Open Pull Request

## Scripts

```bash
npm run dev       # Start development server
npm run build     # Build for production
npm run preview   # Preview production build
npm run lint      # Run linter (if configured)
```

## Dependencies

**Production:**
- vue: ^3.3.0
- vue-router: ^4.2.0
- pinia: ^2.1.0
- axios: ^1.4.0
- chart.js: ^4.3.0
- vue-chartjs: ^5.2.0

**Development:**
- vite: ^4.4.0
- @vitejs/plugin-vue: ^4.2.0

## License

MIT License - See LICENSE file for details

## Support

For issues and questions:
- Create an issue on GitHub
- Check existing documentation
- Review code examples in components

## Changelog

### Version 2.0.0 (Current)
- ✅ Complete UI overhaul with modal notifications
- ✅ Class and Section management pages
- ✅ Enhanced student management with photo upload
- ✅ Delete confirmation modals (warning)
- ✅ Success/error feedback modals
- ✅ Responsive navigation with mobile hamburger menu
- ✅ Full-screen layout (removed max-width constraint)
- ✅ Improved form modals (scrollable body, sticky footer)
- ✅ Advanced filtering (class + section)
- ✅ Real-time attendance summary

### Version 1.0.0
- Initial release with basic attendance tracking
- Dashboard with statistics
- Student CRUD operations
- Bulk attendance recording
