<template>
  <div class="students-page">
    <div class="page-header">
      <h2>Student Management</h2>
      <button class="btn btn-primary" @click="showAddModal = true">+ Add Student</button>
    </div>

    <div class="card">
      <div class="filters">
        <input
          v-model="searchQuery"
          type="text"
          class="form-control"
          placeholder="Search by name or student ID..."
          @input="debouncedSearch"
        />
        <select v-model="classFilter" class="form-control" @change="fetchStudents">
          <option value="">All Classes</option>
          <option v-for="cls in classStore.classes" :key="cls.id" :value="cls.id">
            Class {{ cls.name }}
          </option>
        </select>
        <select v-model="sectionFilter" class="form-control" @change="fetchStudents">
          <option value="">All Sections</option>
          <option v-for="section in sectionStore.sections" :key="section.id" :value="section.id">
            {{ section.name }}
          </option>
        </select>
      </div>

      <div v-if="studentStore.loading" class="loading">Loading...</div>
      <div v-else-if="studentStore.error" class="error">{{ studentStore.error }}</div>
      <div v-else-if="studentStore.students.length === 0" class="info-message">
        No students found. {{ classFilter || sectionFilter ? 'Try adjusting filters or ' : '' }}Click "Add Student" to create one.
      </div>
      
      <table v-else>
        <thead>
          <tr>
            <th>Photo</th>
            <th>Student ID</th>
            <th>Name</th>
            <th>Class</th>
            <th>Section</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="student in studentStore.students" :key="student.id">
            <td>
              <img 
                v-if="student.photo" 
                :src="student.photo" 
                alt="Student photo" 
                class="student-photo"
              />
              <div v-else class="no-photo">No Photo</div>
            </td>
            <td>{{ student.student_id }}</td>
            <td>{{ student.name }}</td>
            <td>{{ student.class?.name || 'N/A' }}</td>
            <td>{{ student.section?.name || 'N/A' }}</td>
            <td>
              <button class="btn btn-sm btn-primary" @click="editStudent(student)">Edit</button>
              <button class="btn btn-sm btn-danger" @click="deleteStudentConfirm(student.id)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="pagination">
        <button 
          @click="goToPage(studentStore.pagination.current_page - 1)"
          :disabled="studentStore.pagination.current_page === 1"
          class="btn">
          Previous
        </button>
        <span>Page {{ studentStore.pagination.current_page }} of {{ studentStore.pagination.last_page }}</span>
        <button 
          @click="goToPage(studentStore.pagination.current_page + 1)"
          :disabled="studentStore.pagination.current_page >= studentStore.pagination.last_page"
          class="btn">
          Next
        </button>
      </div>
    </div>

    <!-- Delete Warning Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click="closeDeleteModal">
      <div class="notification-modal warning" @click.stop>
        <div class="modal-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
          </svg>
        </div>
        <h3>Confirm Delete</h3>
        <p>Are you sure you want to delete <strong>{{ deleteTarget?.name }}</strong>? This action cannot be undone.</p>
        <div class="modal-actions-inline">
          <button class="btn btn-secondary" @click="closeDeleteModal">Cancel</button>
          <button class="btn btn-danger" @click="confirmDelete">Delete</button>
        </div>
      </div>
    </div>

    <!-- Success/Error Modal -->
    <div v-if="showNotificationModal" class="modal-overlay" @click="closeNotificationModal">
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
        <button class="btn btn-primary" @click="closeNotificationModal">OK</button>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showAddModal" class="modal" @click.self="closeModal">
      <div class="modal-content">
        <h3>{{ editMode ? 'Edit Student' : 'Add New Student' }}</h3>
        <form @submit.prevent="saveStudent">
          <div class="form-body">
            <div class="form-group">
              <label>Student ID <span class="required">*</span></label>
              <input v-model="formData.student_id" class="form-control" required />
            </div>
            <div class="form-group">
              <label>Name <span class="required">*</span></label>
              <input v-model="formData.name" class="form-control" required />
            </div>
            <div class="form-group">
              <label>Class <span class="required">*</span></label>
              <select v-model="formData.class_id" class="form-control" required>
                <option value="">Select a class</option>
                <option v-for="cls in classStore.classes" :key="cls.id" :value="cls.id">
                  Class {{ cls.name }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>Section <span class="required">*</span></label>
              <select v-model="formData.section_id" class="form-control" required>
                <option value="">Select a section</option>
                <option v-for="section in sectionStore.sections" :key="section.id" :value="section.id">
                  {{ section.name }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>Photo</label>
              <input 
                type="file" 
                @change="handleFileChange" 
                accept="image/jpeg,image/png,image/jpg"
                class="form-control"
                ref="fileInput"
              />
              <small class="form-text">Max size: 2MB (JPEG, PNG, JPG)</small>
              <div v-if="photoPreview" class="photo-preview">
                <img :src="photoPreview" alt="Preview" />
                <button type="button" @click="removePhoto" class="btn btn-sm btn-danger">Remove</button>
              </div>
              <div v-else-if="editMode && formData.existingPhoto" class="photo-preview">
                <img :src="formData.existingPhoto" alt="Current photo" />
                <p>Current photo</p>
              </div>
            </div>
          </div>
          <div class="modal-actions">
            <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
            <button type="submit" class="btn btn-success" :disabled="studentStore.loading">
              {{ studentStore.loading ? 'Saving...' : 'Save Student' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useStudentStore } from '../stores/student'
import { useClassStore } from '../stores/class'
import { useSectionStore } from '../stores/section'

const studentStore = useStudentStore()
const classStore = useClassStore()
const sectionStore = useSectionStore()
const searchQuery = ref('')
const classFilter = ref('')
const sectionFilter = ref('')
const showAddModal = ref(false)
const editMode = ref(false)
const photoFile = ref(null)
const photoPreview = ref(null)
const fileInput = ref(null)
const formData = ref({
  student_id: '',
  name: '',
  class_id: '',
  section_id: '',
  existingPhoto: null
})
const showNotificationModal = ref(false)
const modalType = ref('success')
const modalTitle = ref('')
const modalMessage = ref('')
const showDeleteModal = ref(false)
const deleteTarget = ref(null)

onMounted(async () => {
  await classStore.fetchAllClasses()
  await sectionStore.fetchAllSections()
  await fetchStudents()
})

const fetchStudents = async () => {
  const params = {
    search: searchQuery.value,
    per_page: 15
  }
  
  // Only add class_id if a class is selected
  if (classFilter.value) {
    params.class_id = classFilter.value
  }
  
  // Only add section_id if a section is selected
  if (sectionFilter.value) {
    params.section_id = sectionFilter.value
  }
  
  await studentStore.fetchStudents(params)
}

let debounceTimer
const debouncedSearch = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(fetchStudents, 500)
}

const handleFileChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    // Validate file size (2MB max)
    if (file.size > 2 * 1024 * 1024) {
      alert('File size must not exceed 2MB')
      event.target.value = ''
      return
    }
    
    // Validate file type
    if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
      alert('Only JPEG, PNG, and JPG files are allowed')
      event.target.value = ''
      return
    }
    
    photoFile.value = file
    
    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      photoPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const removePhoto = () => {
  photoFile.value = null
  photoPreview.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const saveStudent = async () => {
  try {
    // Create FormData for file upload
    const submitData = new FormData()
    submitData.append('student_id', formData.value.student_id)
    submitData.append('name', formData.value.name)
    submitData.append('class_id', formData.value.class_id)
    submitData.append('section_id', formData.value.section_id)
    
    if (photoFile.value) {
      submitData.append('photo', photoFile.value)
    }
    
    if (editMode.value) {
      await studentStore.updateStudent(formData.value.id, submitData)
      showNotification('success', 'Student Updated!', `${formData.value.name} has been updated successfully.`)
    } else {
      await studentStore.createStudent(submitData)
      showNotification('success', 'Student Created!', `${formData.value.name} has been added successfully.`)
    }
    closeModal()
    await fetchStudents()
  } catch (error) {
    const errorMsg = error.response?.data?.message || error.message
    showNotification('error', 'Operation Failed', `Failed to save student: ${errorMsg}`)
  }
}

const editStudent = (student) => {
  editMode.value = true
  formData.value = { 
    id: student.id,
    student_id: student.student_id,
    name: student.name,
    class_id: student.class_id,
    section_id: student.section_id,
    existingPhoto: student.photo
  }
  photoPreview.value = null
  photoFile.value = null
  showAddModal.value = true
}

const deleteStudentConfirm = (id) => {
  const student = studentStore.students.find(s => s.id === id)
  deleteTarget.value = student
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  try {
    const studentName = deleteTarget.value?.name || 'Student'
    await studentStore.deleteStudent(deleteTarget.value.id)
    closeDeleteModal()
    showNotification('success', 'Student Deleted!', `${studentName} has been deleted successfully.`)
    await fetchStudents()
  } catch (error) {
    closeDeleteModal()
    const errorMsg = error.response?.data?.message || error.message
    showNotification('error', 'Delete Failed', `Failed to delete student: ${errorMsg}`)
  }
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  deleteTarget.value = null
}

const showNotification = (type, title, message) => {
  modalType.value = type
  modalTitle.value = title
  modalMessage.value = message
  showNotificationModal.value = true
}

const closeNotificationModal = () => {
  showNotificationModal.value = false
}

const closeModal = () => {
  showAddModal.value = false
  editMode.value = false
  photoFile.value = null
  photoPreview.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
  formData.value = {
    student_id: '',
    name: '',
    class_id: '',
    section_id: '',
    existingPhoto: null
  }
}

const goToPage = async (page) => {
  const params = {
    search: searchQuery.value,
    page
  }
  
  // Only add class_id if a class is selected
  if (classFilter.value) {
    params.class_id = classFilter.value
  }
  
  // Only add section_id if a section is selected
  if (sectionFilter.value) {
    params.section_id = sectionFilter.value
  }
  
  await studentStore.fetchStudents(params)
}
</script>

<style scoped>
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.filters {
  display: grid;
  grid-template-columns: 1fr auto auto;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 1.5rem;
}

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
  margin-right: 0.5rem;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  overflow-y: auto;
  padding: 2rem 0;
}

.modal-content {
  background: white;
  padding: 0;
  border-radius: 12px;
  width: 500px;
  max-width: 90%;
  max-height: calc(100vh - 4rem);
  display: flex;
  flex-direction: column;
  margin: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.modal-content h3 {
  padding: 1.5rem 2rem;
  border-bottom: 1px solid #e5e7eb;
  margin: 0;
  font-size: 1.25rem;
  color: #111827;
  background: #f9fafb;
  border-radius: 12px 12px 0 0;
}

.modal-content form {
  display: flex;
  flex-direction: column;
  overflow: hidden;
  flex: 1;
}

.form-body {
  padding: 1.5rem 2rem;
  overflow-y: auto;
  flex: 1;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  padding: 1.5rem 2rem;
  border-top: 1px solid #e5e7eb;
  background: #f9fafb;
  border-radius: 0 0 12px 12px;
  position: sticky;
  bottom: 0;
}

.student-photo {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 50%;
  border: 2px solid #e0e0e0;
}

.no-photo {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: #f0f0f0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  color: #999;
  text-align: center;
}

.photo-preview {
  margin-top: 1rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
  text-align: center;
}

.photo-preview img {
  max-width: 200px;
  max-height: 200px;
  border-radius: 8px;
  margin-bottom: 0.5rem;
  object-fit: cover;
}

.photo-preview p {
  margin: 0.5rem 0;
  color: #666;
  font-size: 0.9rem;
}

.form-text {
  display: block;
  margin-top: 0.25rem;
  font-size: 0.875rem;
  color: #6c757d;
}

.required {
  color: #ef4444;
}

.btn-secondary {
  background: #6b7280;
  color: white;
}

.btn-secondary:hover:not(:disabled) {
  background: #4b5563;
}

.info-message {
  text-align: center;
  padding: 2rem;
  color: #6b7280;
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

.notification-modal.warning .modal-icon {
  background: #fff3cd;
  color: #ffc107;
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

.notification-modal.warning h3 {
  color: #ffc107;
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

.modal-actions-inline {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.modal-actions-inline .btn {
  min-width: 120px;
}

@media (max-width: 768px) {
  .notification-modal {
    padding: 2rem;
    max-width: 90%;
  }

  .modal-icon {
    width: 60px;
    height: 60px;
  }

  .notification-modal h3 {
    font-size: 1.5rem;
  }
}
</style>

