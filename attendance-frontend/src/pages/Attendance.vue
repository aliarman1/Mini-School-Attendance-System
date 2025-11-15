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
          <select v-model="selectedClass" class="form-control" @change="onClassChange">
            <option value="">Select Class</option>
            <option v-for="cls in classStore.classes" :key="cls.id" :value="cls.id">
              Class {{ cls.name }}
            </option>
          </select>
        </div>
        <div class="form-group">
          <label>Section</label>
          <select v-model="selectedSection" class="form-control" @change="loadStudents" :disabled="!selectedClass">
            <option value="">Select Section</option>
            <option v-for="section in sectionStore.sections" :key="section.id" :value="section.id">
              {{ section.name }}
            </option>
          </select>
        </div>
      </div>

      <div v-if="studentStore.loading" class="loading">Loading students...</div>
      <div v-else-if="studentStore.error" class="error">
        {{ studentStore.error }}
      </div>
      <div v-else-if="!selectedClass || !selectedSection" class="info-message">
        Please select both class and section to load students and record attendance.
      </div>
      <div v-else-if="studentStore.students.length === 0 && selectedClass && selectedSection" class="info-message">
        No students found in this class and section. Please add students first.
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

    <!-- Success/Error Modal -->
    <div v-if="showModal" class="modal-overlay" @click="closeModal">
      <div class="notification-modal" :class="modalType" @click.stop>
        <div class="modal-icon">
          <svg v-if="modalType === 'success'" xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
          </svg>
        </div>
        <h3>{{ modalTitle }}</h3>
        <p>{{ modalMessage }}</p>
        <button class="btn btn-primary" @click="closeModal">OK</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useStudentStore } from '../stores/student'
import { useAttendanceStore } from '../stores/attendance'
import { useClassStore } from '../stores/class'
import { useSectionStore } from '../stores/section'

const studentStore = useStudentStore()
const attendanceStore = useAttendanceStore()
const classStore = useClassStore()
const sectionStore = useSectionStore()

const attendanceDate = ref(new Date().toISOString().split('T')[0])
const selectedClass = ref('')
const selectedSection = ref('')
const attendance = ref({})
const showModal = ref(false)
const modalType = ref('success')
const modalTitle = ref('')
const modalMessage = ref('')

onMounted(async () => {
  // Clear any previous data and errors when component mounts
  studentStore.clearState()
  attendanceStore.clearState()
  await classStore.fetchAllClasses()
  await sectionStore.fetchAllSections()
})

const onClassChange = () => {
  // Reset section when class changes
  selectedSection.value = ''
  studentStore.students = []
  attendance.value = {}
}

const loadStudents = async () => {
  if (!selectedClass.value || !selectedSection.value) {
    studentStore.students = []
    attendance.value = {}
    return
  }
  
  try {
    await studentStore.fetchStudents({
      class_id: selectedClass.value,
      section_id: selectedSection.value,
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
    
    // Show success modal
    modalType.value = 'success'
    modalTitle.value = 'Success!'
    modalMessage.value = `Attendance recorded successfully for ${studentStore.students.length} students on ${attendanceDate.value}.`
    showModal.value = true
    
    // Reset form
    attendance.value = {}
    selectedClass.value = ''
    selectedSection.value = ''
    studentStore.students = []
  } catch (error) {
    // Show error modal
    modalType.value = 'error'
    modalTitle.value = 'Error!'
    modalMessage.value = error.response?.data?.message || error.message || 'Failed to record attendance. Please try again.'
    showModal.value = true
  }
}

const closeModal = () => {
  showModal.value = false
}
</script>

<style scoped>
.attendance-form {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
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

/* Notification Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.notification-modal {
  background: white;
  border-radius: 16px;
  padding: 2.5rem;
  width: 90%;
  max-width: 450px;
  text-align: center;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  animation: slideUp 0.3s ease;
}

@keyframes slideUp {
  from {
    transform: translateY(50px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.modal-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
}

.notification-modal.success .modal-icon {
  background: #d4edda;
  color: #28a745;
}

.notification-modal.error .modal-icon {
  background: #f8d7da;
  color: #dc3545;
}

.notification-modal h3 {
  font-size: 1.75rem;
  margin-bottom: 1rem;
  color: #1f2937;
}

.notification-modal.success h3 {
  color: #28a745;
}

.notification-modal.error h3 {
  color: #dc3545;
}

.notification-modal p {
  font-size: 1rem;
  color: #6b7280;
  margin-bottom: 2rem;
  line-height: 1.6;
}

.notification-modal .btn {
  min-width: 120px;
  padding: 0.75rem 2rem;
  font-size: 1rem;
}

@media (max-width: 768px) {
  .notification-modal {
    padding: 2rem;
    max-width: 90%;
  }
  
  .modal-icon {
    width: 64px;
    height: 64px;
  }
  
  .modal-icon svg {
    width: 36px;
    height: 36px;
  }
  
  .notification-modal h3 {
    font-size: 1.5rem;
  }
  
  .notification-modal p {
    font-size: 0.95rem;
  }
}
</style>
