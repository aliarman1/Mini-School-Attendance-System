<template>
  <div class="reports-page">
    <div class="page-header">
      <h2>Attendance Reports</h2>
    </div>

    <div class="card">
      <h3>Monthly Attendance Report</h3>
      <p class="description">Generate detailed attendance reports by class and month with CSV export option.</p>
      
      <div class="filters">
        <div class="form-group">
          <label>Select Month</label>
          <input 
            v-model="filters.month" 
            type="month" 
            class="form-control"
            :max="currentMonth"
          />
        </div>
        
        <div class="form-group">
          <label>Select Class</label>
          <select v-model="filters.class_id" class="form-control">
            <option value="">Choose a class</option>
            <option v-for="cls in classStore.classes" :key="cls.id" :value="cls.id">
              Class {{ cls.name }}
            </option>
          </select>
        </div>
        
        <div class="form-group">
          <label>&nbsp;</label>
          <button 
            @click="generateReport" 
            class="btn btn-primary"
            :disabled="loading || !filters.month || !filters.class_id"
          >
            {{ loading ? 'Generating...' : 'Generate Report' }}
          </button>
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="error-message">
        {{ error }}
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading">
        <div class="spinner"></div>
        <p>Generating report...</p>
      </div>

      <!-- Report Results -->
      <div v-else-if="reportData.length > 0" class="report-results">
        <div class="report-header">
          <div class="report-info">
            <h4>Report for {{ formatMonth(filters.month) }} - Class {{ selectedClassName }}</h4>
            <p>Total Students: {{ reportData.length }}</p>
          </div>
          <div class="export-actions">
            <button @click="exportToCSV" class="btn btn-success">
              <span class="icon">📥</span> Export to CSV
            </button>
            <button @click="printReport" class="btn btn-secondary">
              <span class="icon">🖨️</span> Print
            </button>
          </div>
        </div>

        <!-- Summary Stats -->
        <div class="summary-stats">
          <div class="stat-card">
            <div class="stat-value">{{ calculateAverageAttendance() }}%</div>
            <div class="stat-label">Average Attendance</div>
          </div>
          <div class="stat-card">
            <div class="stat-value">{{ countStudentsAbove(75) }}</div>
            <div class="stat-label">Above 75%</div>
          </div>
          <div class="stat-card warning">
            <div class="stat-value">{{ countStudentsBelow(75) }}</div>
            <div class="stat-label">Below 75%</div>
          </div>
        </div>

        <!-- Report Table -->
        <div class="table-container">
          <table class="report-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Total Days</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Late</th>
                <th>Attendance %</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(record, index) in reportData" :key="record.student_id" :class="getRowClass(record.attendance_percentage)">
                <td>{{ index + 1 }}</td>
                <td>{{ record.student_number }}</td>
                <td><strong>{{ record.student_name }}</strong></td>
                <td>{{ record.total_days }}</td>
                <td><span class="badge badge-success">{{ record.present }}</span></td>
                <td><span class="badge badge-danger">{{ record.absent }}</span></td>
                <td><span class="badge badge-warning">{{ record.late }}</span></td>
                <td>
                  <div class="percentage-cell">
                    <span class="percentage-value">{{ record.attendance_percentage }}%</span>
                    <div class="progress-bar">
                      <div 
                        class="progress-fill" 
                        :style="{ width: record.attendance_percentage + '%' }"
                        :class="getProgressClass(record.attendance_percentage)"
                      ></div>
                    </div>
                  </div>
                </td>
                <td>
                  <span 
                    class="status-badge" 
                    :class="getStatusClass(record.attendance_percentage)"
                  >
                    {{ getStatusText(record.attendance_percentage) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="!loading && reportGenerated" class="empty-state">
        <p>No attendance data found for the selected month and class.</p>
      </div>

      <!-- Initial State -->
      <div v-else class="info-message">
        <p>Select a month and class to generate an attendance report.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useClassStore } from '../stores/class'
import api from '../services/api'

const classStore = useClassStore()
const loading = ref(false)
const error = ref(null)
const reportData = ref([])
const reportGenerated = ref(false)
const filters = ref({
  month: '',
  class_id: ''
})

// Get current month in YYYY-MM format
const currentMonth = computed(() => {
  const now = new Date()
  return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`
})

// Set default month to current month
filters.value.month = currentMonth.value

const selectedClassName = computed(() => {
  const selectedClass = classStore.classes.find(c => c.id === parseInt(filters.value.class_id))
  return selectedClass ? selectedClass.name : ''
})

onMounted(async () => {
  await classStore.fetchAllClasses()
})

const generateReport = async () => {
  if (!filters.value.month || !filters.value.class_id) {
    error.value = 'Please select both month and class'
    return
  }

  loading.value = true
  error.value = null
  reportData.value = []
  reportGenerated.value = false

  try {
    const response = await api.get('/attendance/report/monthly', {
      params: {
        month: filters.value.month,
        class_id: filters.value.class_id
      }
    })

    if (response.data.success) {
      reportData.value = response.data.data
      reportGenerated.value = true
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to generate report'
    console.error('Error generating report:', err)
  } finally {
    loading.value = false
  }
}

const formatMonth = (monthStr) => {
  if (!monthStr) return ''
  const [year, month] = monthStr.split('-')
  const date = new Date(year, month - 1)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long' })
}

const calculateAverageAttendance = () => {
  if (reportData.value.length === 0) return 0
  const sum = reportData.value.reduce((acc, record) => acc + record.attendance_percentage, 0)
  return (sum / reportData.value.length).toFixed(2)
}

const countStudentsAbove = (threshold) => {
  return reportData.value.filter(record => record.attendance_percentage >= threshold).length
}

const countStudentsBelow = (threshold) => {
  return reportData.value.filter(record => record.attendance_percentage < threshold).length
}

const getRowClass = (percentage) => {
  if (percentage < 75) return 'row-warning'
  return ''
}

const getProgressClass = (percentage) => {
  if (percentage >= 90) return 'progress-excellent'
  if (percentage >= 75) return 'progress-good'
  if (percentage >= 60) return 'progress-average'
  return 'progress-poor'
}

const getStatusClass = (percentage) => {
  if (percentage >= 90) return 'status-excellent'
  if (percentage >= 75) return 'status-good'
  if (percentage >= 60) return 'status-average'
  return 'status-poor'
}

const getStatusText = (percentage) => {
  if (percentage >= 90) return 'Excellent'
  if (percentage >= 75) return 'Good'
  if (percentage >= 60) return 'Average'
  return 'Poor'
}

const exportToCSV = () => {
  if (reportData.value.length === 0) return

  // CSV Headers
  const headers = [
    'Student ID',
    'Student Name',
    'Total Days',
    'Present',
    'Absent',
    'Late',
    'Attendance %',
    'Status'
  ]

  // CSV Rows
  const rows = reportData.value.map(record => [
    record.student_number,
    record.student_name,
    record.total_days,
    record.present,
    record.absent,
    record.late,
    record.attendance_percentage,
    getStatusText(record.attendance_percentage)
  ])

  // Create CSV content
  let csvContent = headers.join(',') + '\n'
  rows.forEach(row => {
    csvContent += row.map(cell => {
      // Escape commas and quotes in cell values
      const cellStr = String(cell)
      if (cellStr.includes(',') || cellStr.includes('"') || cellStr.includes('\n')) {
        return '"' + cellStr.replace(/"/g, '""') + '"'
      }
      return cellStr
    }).join(',') + '\n'
  })

  // Add summary at the bottom
  csvContent += '\n'
  csvContent += `Summary\n`
  csvContent += `Month,${formatMonth(filters.value.month)}\n`
  csvContent += `Class,${selectedClassName.value}\n`
  csvContent += `Total Students,${reportData.value.length}\n`
  csvContent += `Average Attendance,${calculateAverageAttendance()}%\n`
  csvContent += `Students Above 75%,${countStudentsAbove(75)}\n`
  csvContent += `Students Below 75%,${countStudentsBelow(75)}\n`

  // Create download link
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const link = document.createElement('a')
  const url = URL.createObjectURL(blob)
  
  const fileName = `attendance_report_${filters.value.month}_class_${selectedClassName.value}.csv`
  link.setAttribute('href', url)
  link.setAttribute('download', fileName)
  link.style.visibility = 'hidden'
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

const printReport = () => {
  window.print()
}
</script>

<style scoped>
.page-header {
  margin-bottom: 2rem;
}

.card h3 {
  margin-bottom: 0.5rem;
  color: #2c3e50;
}

.description {
  color: #666;
  margin-bottom: 2rem;
}

.filters {
  display: grid;
  grid-template-columns: 1fr 1fr auto;
  gap: 1rem;
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: #f8f9fa;
  border-radius: 8px;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #495057;
}

.loading {
  text-align: center;
  padding: 3rem;
}

.spinner {
  width: 50px;
  height: 50px;
  margin: 0 auto 1rem;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.report-results {
  margin-top: 2rem;
}

.report-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #e9ecef;
}

.report-info h4 {
  margin-bottom: 0.5rem;
  color: #2c3e50;
}

.report-info p {
  color: #6c757d;
  margin: 0;
}

.export-actions {
  display: flex;
  gap: 1rem;
}

.icon {
  margin-right: 0.5rem;
}

.summary-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 8px;
  text-align: center;
  border-left: 4px solid #28a745;
}

.stat-card.warning {
  border-left-color: #ffc107;
}

.stat-value {
  font-size: 2rem;
  font-weight: bold;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.stat-label {
  color: #6c757d;
  font-size: 0.9rem;
}

.table-container {
  overflow-x: auto;
  border-radius: 8px;
  border: 1px solid #dee2e6;
}

.report-table {
  width: 100%;
  border-collapse: collapse;
}

.report-table thead {
  background: #f8f9fa;
}

.report-table th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #495057;
  border-bottom: 2px solid #dee2e6;
}

.report-table td {
  padding: 1rem;
  border-bottom: 1px solid #dee2e6;
}

.report-table tbody tr:hover {
  background: #f8f9fa;
}

.row-warning {
  background: #fff3cd !important;
}

.badge {
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.875rem;
  font-weight: 600;
}

.badge-success {
  background: #d4edda;
  color: #155724;
}

.badge-danger {
  background: #f8d7da;
  color: #721c24;
}

.badge-warning {
  background: #fff3cd;
  color: #856404;
}

.percentage-cell {
  min-width: 120px;
}

.percentage-value {
  display: block;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.progress-bar {
  height: 6px;
  background: #e9ecef;
  border-radius: 3px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  transition: width 0.3s ease;
}

.progress-excellent {
  background: #28a745;
}

.progress-good {
  background: #17a2b8;
}

.progress-average {
  background: #ffc107;
}

.progress-poor {
  background: #dc3545;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 600;
}

.status-excellent {
  background: #d4edda;
  color: #155724;
}

.status-good {
  background: #d1ecf1;
  color: #0c5460;
}

.status-average {
  background: #fff3cd;
  color: #856404;
}

.status-poor {
  background: #f8d7da;
  color: #721c24;
}

.error-message {
  background: #f8d7da;
  color: #721c24;
  padding: 1rem;
  border-radius: 4px;
  border: 1px solid #f5c6cb;
  margin-bottom: 1rem;
}

.empty-state {
  text-align: center;
  padding: 3rem;
  color: #6c757d;
}

.info-message {
  text-align: center;
  padding: 3rem;
  background: #e7f3ff;
  border-radius: 8px;
  color: #004085;
}

/* Print Styles */
@media print {
  .filters,
  .export-actions,
  .navbar {
    display: none !important;
  }

  .report-table {
    font-size: 12px;
  }

  .report-table th,
  .report-table td {
    padding: 0.5rem;
  }
}
</style>
