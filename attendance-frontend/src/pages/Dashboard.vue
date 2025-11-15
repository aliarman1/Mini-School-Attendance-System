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
        
        <div class="stat-card stat-success">
          <div class="stat-value">{{ stats.today.present }}</div>
          <div class="stat-label">Present Today</div>
        </div>
        
        <div class="stat-card stat-danger">
          <div class="stat-value">{{ stats.today.absent }}</div>
          <div class="stat-label">Absent Today</div>
        </div>
        
        <div class="stat-card stat-warning">
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
        <h3>Recent Attendance Records</h3>
        <table v-if="todayData && todayData.records && todayData.records.length > 0">
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
            <tr v-for="record in todayData.records.slice(0, 10)" :key="record.id">
              <td>{{ record.student?.name || 'N/A' }}</td>
              <td>
                <span class="class-badge">{{ record.student?.class || 'N/A' }}</span>
              </td>
              <td>
                <span :class="'status-badge status-' + record.status">
                  {{ record.status }}
                </span>
              </td>
              <td>{{ record.recorded_by }}</td>
              <td>{{ formatTime(record.created_at) }}</td>
            </tr>
          </tbody>
        </table>
        <p v-else class="text-muted">No records yet</p>
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

.text-success { color: #2ecc71; }
.text-danger { color: #e74c3c; }
.text-warning { color: #f39c12; }
.text-muted { color: #7f8c8d; }
</style>
