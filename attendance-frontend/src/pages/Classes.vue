<template>
  <div class="classes-page">
    <div class="page-header">
      <h2>Class Management</h2>
      <button class="btn btn-primary" @click="showAddModal = true">+ Add Class</button>
    </div>

    <div class="card">
      <div class="filters">
        <input
          v-model="searchQuery"
          type="text"
          class="form-control"
          placeholder="Search by class name..."
          @input="debouncedSearch"
        />
      </div>

      <div v-if="classStore.loading" class="loading">Loading...</div>
      <div v-else-if="classStore.error" class="error">{{ classStore.error }}</div>
      <div v-else-if="classStore.classes.length === 0" class="info-message">
        No classes found. Click "Add Class" to create one.
      </div>
      
      <table v-else>
        <thead>
          <tr>
            <th>Class Name</th>
            <th>Capacity</th>
            <th>Students</th>
            <th>Description</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cls in classStore.classes" :key="cls.id">
            <td><strong>Class {{ cls.name }}</strong></td>
            <td>{{ cls.capacity }}</td>
            <td>
              <span class="badge">{{ cls.students_count || 0 }} students</span>
            </td>
            <td>{{ cls.description || '-' }}</td>
            <td>
              <button class="btn btn-sm btn-primary" @click="editClass(cls)">Edit</button>
              <button class="btn btn-sm btn-danger" @click="deleteClassConfirm(cls.id)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="classStore.pagination.last_page > 1" class="pagination">
        <button 
          @click="goToPage(classStore.pagination.current_page - 1)"
          :disabled="classStore.pagination.current_page === 1"
          class="btn">
          Previous
        </button>
        <span>Page {{ classStore.pagination.current_page }} of {{ classStore.pagination.last_page }}</span>
        <button 
          @click="goToPage(classStore.pagination.current_page + 1)"
          :disabled="classStore.pagination.current_page >= classStore.pagination.last_page"
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
        <p>Are you sure you want to delete <strong>Class {{ deleteTarget?.name }}</strong>? All students in this class will need to be reassigned.</p>
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
        <h3>{{ editMode ? 'Edit Class' : 'Add New Class' }}</h3>
        <form @submit.prevent="saveClass">
          <div class="form-body">
            <div class="form-group">
              <label>Class Name <span class="required">*</span></label>
              <input 
                v-model="formData.name" 
                class="form-control" 
                placeholder="e.g., 9, 10, 11, 12"
                required 
              />
              <small class="form-text">Grade level (unique identifier)</small>
            </div>
            <div class="form-group">
              <label>Capacity</label>
              <input 
                v-model.number="formData.capacity" 
                type="number" 
                class="form-control" 
                min="1"
                max="100"
                placeholder="Default: 30"
              />
              <small class="form-text">Maximum number of students (1-100)</small>
            </div>
            <div class="form-group">
              <label>Description</label>
              <textarea 
                v-model="formData.description" 
                class="form-control" 
                rows="3"
                placeholder="Optional description..."
              ></textarea>
            </div>
          </div>
          <div class="modal-actions">
            <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
            <button type="submit" class="btn btn-success" :disabled="classStore.loading">
              {{ classStore.loading ? 'Saving...' : 'Save Class' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useClassStore } from '../stores/class'

const classStore = useClassStore()
const searchQuery = ref('')
const showAddModal = ref(false)
const editMode = ref(false)
const formData = ref({
  name: '',
  capacity: 30,
  description: ''
})
const showNotificationModal = ref(false)
const modalType = ref('success')
const modalTitle = ref('')
const modalMessage = ref('')
const showDeleteModal = ref(false)
const deleteTarget = ref(null)

onMounted(async () => {
  await fetchClasses()
})

const fetchClasses = async () => {
  const params = {
    per_page: 15
  }
  
  if (searchQuery.value) {
    params.search = searchQuery.value
  }
  
  await classStore.fetchClasses(params)
}

let debounceTimer
const debouncedSearch = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(fetchClasses, 500)
}

const saveClass = async () => {
  try {
    const submitData = {
      name: formData.value.name,
      capacity: formData.value.capacity || 30,
      description: formData.value.description || null
    }

    if (editMode.value) {
      await classStore.updateClass(formData.value.id, submitData)
      showNotification('success', 'Class Updated!', `Class ${formData.value.name} has been updated successfully.`)
    } else {
      await classStore.createClass(submitData)
      showNotification('success', 'Class Created!', `Class ${formData.value.name} has been added successfully.`)
    }
    closeModal()
    await fetchClasses()
  } catch (error) {
    const errorMsg = error.response?.data?.message || error.message
    showNotification('error', 'Operation Failed', `Failed to save class: ${errorMsg}`)
  }
}

const editClass = (cls) => {
  editMode.value = true
  formData.value = {
    id: cls.id,
    name: cls.name,
    capacity: cls.capacity,
    description: cls.description
  }
  showAddModal.value = true
}

const deleteClassConfirm = (id) => {
  const cls = classStore.classes.find(c => c.id === id)
  deleteTarget.value = cls
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  try {
    const className = deleteTarget.value?.name || ''
    await classStore.deleteClass(deleteTarget.value.id)
    closeDeleteModal()
    showNotification('success', 'Class Deleted!', `Class ${className} has been deleted successfully.`)
    await fetchClasses()
  } catch (error) {
    closeDeleteModal()
    const errorMsg = error.response?.data?.message || error.message
    showNotification('error', 'Delete Failed', `Failed to delete class: ${errorMsg}`)
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
  formData.value = {
    name: '',
    capacity: 30,
    description: ''
  }
}

const goToPage = async (page) => {
  const params = { page }
  
  if (searchQuery.value) {
    params.search = searchQuery.value
  }
  
  await classStore.fetchClasses(params)
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
  margin-bottom: 1.5rem;
}

.badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  background: #e3f2fd;
  color: #1976d2;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 500;
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

.required {
  color: #dc3545;
}

.form-text {
  display: block;
  margin-top: 0.25rem;
  font-size: 0.875rem;
  color: #6c757d;
}

textarea.form-control {
  resize: vertical;
  min-height: 80px;
}

.info-message {
  text-align: center;
  padding: 2rem;
  color: #666;
  font-size: 1rem;
}

.btn-secondary {
  background: #6b7280;
  color: white;
}

.btn-secondary:hover:not(:disabled) {
  background: #4b5563;
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
