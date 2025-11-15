<template>
  <div class="dashboard">
    <h2>Dashboard</h2>

    <div v-if="attendanceStore.loading" class="loading">Loading...</div>
    
    <div v-else-if="attendanceStore.error" class="error">{{ attendanceStore.error }}</div>
    
    <div v-else>
      <!-- Statistics Cards -->
      <div class="stats-grid" v-if="stats">
        <div class="stat-card">
          <div class="stat-value">{{ stats.total_students }}</div>
          <div class="stat-label">Total Students</div>
        </div>
        
        <div 
          class="stat-card stat-success clickable" 
          :class="{ active: statusFilter === 'present' }"
          @click="filterByStatus('present')">
          <div class="stat-value">{{ stats.today.present }}</div>
          <div class="stat-label">Present Today</div>
        </div>
        
        <div 
          class="stat-card stat-danger clickable" 
          :class="{ active: statusFilter === 'absent' }"
          @click="filterByStatus('absent')">
          <div class="stat-value">{{ stats.today.absent }}</div>
          <div class="stat-label">Absent Today</div>
        </div>
        
        <div 
          class="stat-card stat-warning clickable" 
          :class="{ active: statusFilter === 'late' }"
          @click="filterByStatus('late')">
          <div class="stat-value">{{ stats.today.late }}</div>
          <div class="stat-label">Late Today</div>
        </div>
        
        <div class="stat-card stat-info">
          <div class="stat-value">{{ stats.today.percentage }}%</div>
          <div class="stat-label">Today's Attendance</div>
        </div>
        
        <div class="stat-card stat-primary">
          <div class="stat-value">{{ stats.monthly.percentage }}%</div>
          <div class="stat-label">Monthly Average</div>
        </div>
      </div>

      <!-- Today's Attendance -->
      <div class="card">
        <h3>Today's Attendance Summary</h3>
        <div v-if="todayData">
          <div class="today-summary">
            <p><strong>Date:</strong> {{ todayData.date }}</p>
            <p><strong>Total Recorded:</strong> {{ todayData.total }}</p>
            <p><strong>Present:</strong> <span class="text-success">{{ todayData.present }}</span></p>
            <p><strong>Absent:</strong> <span class="text-danger">{{ todayData.absent }}</span></p>
            <p><strong>Late:</strong> <span class="text-warning">{{ todayData.late }}</span></p>
          </div>

          <div class="chart-container">
            <canvas id="attendanceChart"></canvas>
          </div>
        </div>
        <p v-else class="text-muted">No attendance recorded today</p>
      </div>

      <!-- Recent Activity -->
      <div class="card">
        <div class="records-header">
          <h3>Recent Attendance Records</h3>
          <div v-if="statusFilter" class="filter-badge">
            <span>Showing: {{ statusFilter }}</span>
            <button @click="clearFilter" class="clear-filter">✕</button>
          </div>
        </div>
        <table v-if="filteredRecords.length > 0">
          <thead>
            <tr>
              <th>Student</th>
              <th>Class</th>
              <th>Status</th>
              <th>Recorded By</th>
              <th>Time</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="record in filteredRecords.slice(0, 10)" :key="record.id">
              <td>{{ record.student?.name || 'N/A' }}</td>
              <td>
                <span class="class-badge">
                  {{ record.student?.class?.name || 'N/A' }}
                </span>
              </td>
              <td>
                <span :class="'status-badge status-' + record.status">
                  {{ record.status }}
                </span>
              </td>
              <td>{{ record.recorded_by?.name || 'N/A' }}</td>
              <td>{{ formatTime(record.created_at) }}</td>
            </tr>
          </tbody>
        </table>
        <p v-else class="text-muted">
          {{ statusFilter ? `No ${statusFilter} records today` : 'No records yet' }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue'
import { useAttendanceStore } from '../stores/attendance'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const attendanceStore = useAttendanceStore()
const stats = computed(() => attendanceStore.statistics)
const todayData = computed(() => attendanceStore.todayAttendance)
const chartInstance = ref(null)
const statusFilter = ref(null)

// Computed property for filtered records
const filteredRecords = computed(() => {
  if (!todayData.value || !todayData.value.records) return []
  
  if (!statusFilter.value) {
    return todayData.value.records
  }
  
  return todayData.value.records.filter(record => record.status === statusFilter.value)
})

// Filter by status when clicking on stat cards
const filterByStatus = (status) => {
  if (statusFilter.value === status) {
    // Toggle off if clicking the same filter
    statusFilter.value = null
  } else {
    statusFilter.value = status
  }
}

// Clear the filter
const clearFilter = () => {
  statusFilter.value = null
}

onMounted(async () => {
  await Promise.all([
    attendanceStore.fetchStatistics(),
    attendanceStore.fetchTodayAttendance()
  ])
  
  await nextTick()
  renderChart()
})

const renderChart = () => {
  const ctx = document.getElementById('attendanceChart')
  if (!ctx || !todayData.value) return
  
  if (chartInstance.value) {
    chartInstance.value.destroy()
  }
  
  chartInstance.value = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Present', 'Absent', 'Late'],
      datasets: [{
        data: [
          todayData.value.present,
          todayData.value.absent,
          todayData.value.late
        ],
        backgroundColor: [
          '#2ecc71',
          '#e74c3c',
          '#f39c12'
        ]
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    }
  })
}

const formatTime = (datetime) => {
  if (!datetime) return 'N/A'
  return new Date(datetime).toLocaleTimeString()
}
</script>

<style scoped>
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  text-align: center;
  transition: all 0.3s;
}

.stat-card.clickable {
  cursor: pointer;
}

.stat-card.clickable:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.stat-card.clickable.active {
  transform: scale(1.05);
  box-shadow: 0 6px 20px rgba(0,0,0,0.2);
  border: 2px solid currentColor;
}

.stat-value {
  font-size: 2.5rem;
  font-weight: bold;
  color: #2c3e50;
}

.stat-label {
  color: #7f8c8d;
  margin-top: 0.5rem;
}

.stat-success { border-left: 4px solid #2ecc71; }
.stat-danger { border-left: 4px solid #e74c3c; }
.stat-warning { border-left: 4px solid #f39c12; }
.stat-info { border-left: 4px solid #3498db; }
.stat-primary { border-left: 4px solid #9b59b6; }

.today-summary {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin-bottom: 2rem;
}

.chart-container {
  height: 300px;
  margin: 2rem auto;
  max-width: 400px;
}

.class-badge {
  padding: 0.25rem 0.6rem;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  background: #e3f2fd;
  color: #1976d2;
  display: inline-block;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 500;
}

.status-present {
  background: #d4edda;
  color: #155724;
}

.status-absent {
  background: #f8d7da;
  color: #721c24;
}

.status-late {
  background: #fff3cd;
  color: #856404;
}

.records-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.records-header h3 {
  margin: 0;
}

.filter-badge {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #e3f2fd;
  border-radius: 20px;
  font-size: 0.9rem;
  color: #1976d2;
  font-weight: 500;
}

.clear-filter {
  background: none;
  border: none;
  color: #1976d2;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 0;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: background 0.2s;
}

.clear-filter:hover {
  background: rgba(25, 118, 210, 0.1);
}

.text-success { color: #2ecc71; }
.text-danger { color: #e74c3c; }
.text-warning { color: #f39c12; }
.text-muted { color: #7f8c8d; }
</style>
