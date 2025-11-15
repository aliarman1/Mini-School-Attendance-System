<template>
  <div class="attendance-page">
    <h2>Record Attendance</h2>
    
    <div class="card">
      <div class="attendance-form">
        <div class="form-group">
          <label>Date</label>
          <input v-model="attendanceDate" type="date" class="form-control" />
        </div>
        <div class="form-group">
          <label>Class</label>
          <select v-model="selectedClass" class="form-control" @change="loadStudents">
            <option value="">Select Class</option>
            <option value="9A">Class 9A</option>
            <option value="9B">Class 9B</option>
            <option value="10A">Class 10A</option>
            <option value="10B">Class 10B</option>
          </select>
        </div>
      </div>

      <div v-if="studentStore.loading" class="loading">Loading students...</div>
      <div v-else-if="studentStore.error" class="error">
        {{ studentStore.error }}
      </div>
      <div v-else-if="!selectedClass" class="info-message">
        Please select a class to load students and record attendance.
      </div>
      <div v-else-if="studentStore.students.length === 0 && selectedClass" class="info-message">
        No students found in this class. Please add students first.
      </div>
      <div v-else-if="studentStore.students.length > 0">
        <div class="bulk-actions">
          <button class="btn" @click="markAll('present')">Mark All Present</button>
          <button class="btn" @click="markAll('absent')">Mark All Absent</button>
        </div>

        <table>
          <thead>
            <tr>
              <th>Student ID</th>
              <th>Name</th>
              <th>Status</th>
              <th>Note</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="student in studentStore.students" :key="student.id">
              <td>{{ student.student_id }}</td>
              <td>{{ student.name }}</td>
              <td>
                <select 
                  v-model="attendance[student.id].status" 
                  class="form-control form-control-sm"
                  @change="updatePercentage">
                  <option value="present">Present</option>
                  <option value="absent">Absent</option>
                  <option value="late">Late</option>
                </select>
              </td>
              <td>
                <input 
                  v-model="attendance[student.id].note"
                  class="form-control form-control-sm"
                  placeholder="Optional note"
                />
              </td>
            </tr>
          </tbody>
        </table>

        <div class="attendance-summary">
          <div class="summary-item">
            <strong>Total:</strong> {{ studentStore.students.length }}
          </div>
          <div class="summary-item">
            <strong>Present:</strong> {{ presentCount }}
          </div>
          <div class="summary-item">
            <strong>Absent:</strong> {{ absentCount }}
          </div>
          <div class="summary-item">
            <strong>Late:</strong> {{ lateCount }}
          </div>
          <div class="summary-item">
            <strong>Percentage:</strong> {{ attendancePercentage }}%
          </div>
        </div>

        <button 
          class="btn btn-success btn-lg"
          @click="submitAttendance"
          :disabled="attendanceStore.loading">
          {{ attendanceStore.loading ? 'Saving...' : 'Submit Attendance' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useStudentStore } from '../stores/student'
import { useAttendanceStore } from '../stores/attendance'

const studentStore = useStudentStore()
const attendanceStore = useAttendanceStore()

const attendanceDate = ref(new Date().toISOString().split('T')[0])
const selectedClass = ref('')
const attendance = ref({})

onMounted(() => {
  // Clear any previous data and errors when component mounts
  studentStore.clearState()
  attendanceStore.clearState()
})

const loadStudents = async () => {
  if (!selectedClass.value) {
    studentStore.students = []
    attendance.value = {}
    return
  }
  
  try {
    await studentStore.fetchStudents({
      class: selectedClass.value,
      per_page: 100
    })
    
    // Initialize attendance records
    attendance.value = {}
    studentStore.students.forEach(student => {
      attendance.value[student.id] = {
        student_id: student.id,
        status: 'present',
        note: ''
      }
    })
  } catch (error) {
    console.error('Failed to load students:', error)
    attendance.value = {}
  }
}

const markAll = (status) => {
  Object.keys(attendance.value).forEach(id => {
    attendance.value[id].status = status
  })
  updatePercentage()
}

const presentCount = computed(() => {
  return Object.values(attendance.value).filter(a => a.status === 'present').length
})

const absentCount = computed(() => {
  return Object.values(attendance.value).filter(a => a.status === 'absent').length
})

const lateCount = computed(() => {
  return Object.values(attendance.value).filter(a => a.status === 'late').length
})

const attendancePercentage = computed(() => {
  const total = Object.keys(attendance.value).length
  if (total === 0) return 0
  return Math.round((presentCount.value / total) * 100)
})

const updatePercentage = () => {
  // Trigger reactivity
}

const submitAttendance = async () => {
  try {
    await attendanceStore.recordBulkAttendance({
      date: attendanceDate.value,
      attendances: Object.values(attendance.value)
    })
    
    alert('Attendance recorded successfully!')
    attendance.value = {}
    selectedClass.value = ''
  } catch (error) {
    alert('Failed to record attendance: ' + (error.message || 'Unknown error'))
  }
}
</script>

<style scoped>
.attendance-form {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-bottom: 2rem;
}

.bulk-actions {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
}

.form-control-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}

.attendance-summary {
  display: flex;
  gap: 2rem;
  justify-content: center;
  margin: 2rem 0;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 4px;
}

.summary-item {
  text-align: center;
}

.btn-lg {
  width: 100%;
  padding: 1rem;
  font-size: 1.125rem;
}

.info-message {
  text-align: center;
  padding: 2rem;
  color: #7f8c8d;
  background: #ecf0f1;
  border-radius: 4px;
  font-size: 1.1rem;
}
</style>
