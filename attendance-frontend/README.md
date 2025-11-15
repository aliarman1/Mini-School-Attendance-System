# School Attendance System - Frontend

Modern Vue 3 SPA for managing school attendance with real-time statistics and interactive charts.

## Features

### 📊 Dashboard
- Real-time attendance statistics
- Today's attendance summary
- Interactive doughnut charts (Chart.js)
- Monthly attendance percentage
- Recent activity feed

### 🎓 Student Management
- List all students with pagination
- Search by name or student ID
- Filter by class and section
- Add/Edit/Delete students
- Responsive data table

### ✅ Attendance Recording
- Bulk attendance marking
- Select by class/section
- Real-time percentage calculator
- Mark all present/absent shortcuts
- Optional notes for each student
- Live attendance summary

## Tech Stack

- **Framework**: Vue 3 (Composition API)
- **State Management**: Pinia
- **Routing**: Vue Router
- **HTTP Client**: Axios
- **Charts**: Chart.js + vue-chartjs
- **Build Tool**: Vite

## Installation

### Prerequisites
- Node.js 18+
- npm or yarn

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

4. **Start development server**
```bash
npm run dev
```

Frontend will be available at `http://localhost:5173`

5. **Build for production**
```bash
npm run build
```

## Project Structure

```
src/
├── pages/              # Page components
│   ├── Dashboard.vue   # Dashboard with statistics & charts
│   ├── Students.vue    # Student management
│   └── Attendance.vue  # Attendance recording
├── stores/             # Pinia stores
│   ├── student.js      # Student state management
│   └── attendance.js   # Attendance state management
├── services/           # API services
│   └── api.js          # Axios configuration
├── components/         # Reusable components
├── App.vue             # Main app component
├── main.js             # App entry point
└── router.js           # Route definitions
```

## Features Overview

### Dashboard Page
- **Statistics Cards**: Total students, today's attendance breakdown
- **Chart Visualization**: Doughnut chart showing present/absent/late
- **Recent Records**: Latest 10 attendance entries
- **Auto-refresh**: Real-time data updates

### Students Page
- **CRUD Operations**: Full create, read, update, delete
- **Search**: Filter by name or ID with debounced search
- **Class Filter**: Dropdown filter for specific classes
- **Pagination**: Navigate through large student lists
- **Modal Forms**: Clean UI for add/edit operations

### Attendance Page
- **Class Selection**: Load students by class
- **Bulk Actions**: Mark all as present/absent
- **Individual Control**: Per-student status selection
- **Notes**: Optional comments for each record
- **Live Summary**: Real-time count and percentage
- **Validation**: Ensures all required fields filled

## State Management (Pinia)

### Student Store
- `fetchStudents()` - Get paginated student list
- `createStudent()` - Add new student
- `updateStudent()` - Modify existing student
- `deleteStudent()` - Remove student

### Attendance Store
- `recordBulkAttendance()` - Submit attendance records
- `fetchStatistics()` - Get attendance stats
- `fetchTodayAttendance()` - Today's records
- `generateMonthlyReport()` - Generate class report

## API Integration

The app uses Axios with interceptors for:
- **Base URL configuration**: Centralized API endpoint
- **Token management**: Auto-inject auth tokens
- **Error handling**: Global error interceptor
- **Request/Response transformation**: Consistent data format

## Styling

- **Clean & Modern**: Professional UI design
- **Responsive**: Mobile-friendly layout
- **Color-coded**: Status-based color system
  - Green: Present
  - Red: Absent
  - Yellow: Late
- **Card-based**: Material-inspired card layout

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Development

### Hot Module Replacement
Vite provides instant HMR during development.

### Code Organization
- **Composition API**: Modern Vue 3 syntax
- **Script Setup**: Cleaner component code
- **Reactive State**: Built-in reactivity
- **Computed Properties**: Optimized calculations

## Production Build

```bash
npm run build
```

Output will be in `dist/` directory.

### Preview Production Build
```bash
npm run preview
```

## Environment Variables

Create `.env` file:
```env
VITE_API_URL=http://localhost:8000/api
```

For production:
```env
VITE_API_URL=https://your-api-domain.com/api
```

## Troubleshooting

### CORS Errors
Ensure backend `.env` has:
```env
FRONTEND_URL=http://localhost:5173
```

### API Connection Failed
1. Check backend is running on port 8000
2. Verify VITE_API_URL in `.env`
3. Check browser console for errors

### Charts Not Showing
Ensure Chart.js is installed:
```bash
npm install chart.js vue-chartjs
```

## License

MIT License
