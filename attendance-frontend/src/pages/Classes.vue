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
    } else {
      await classStore.createClass(submitData)
    }
    closeModal()
    await fetchClasses()
  } catch (error) {
    const errorMsg = error.response?.data?.message || error.message
    alert('Failed to save class: ' + errorMsg)
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

const deleteClassConfirm = async (id) => {
  if (confirm('Are you sure you want to delete this class? All students in this class will need to be reassigned.')) {
    try {
      await classStore.deleteClass(id)
      await fetchClasses()
    } catch (error) {
      alert('Failed to delete class: ' + (error.response?.data?.message || error.message))
    }
  }
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
</style>
