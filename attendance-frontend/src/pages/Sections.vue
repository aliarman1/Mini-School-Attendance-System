<template>
  <div class="sections-page">
    <div class="page-header">
      <h2>Section Management</h2>
      <button class="btn btn-primary" @click="showAddModal = true">+ Add Section</button>
    </div>

    <div class="card">
      <div class="filters">
        <input
          v-model="searchQuery"
          type="text"
          class="form-control"
          placeholder="Search by section name..."
          @input="debouncedSearch"
        />
      </div>

      <div v-if="sectionStore.loading" class="loading">Loading...</div>
      <div v-else-if="sectionStore.error" class="error">{{ sectionStore.error }}</div>
      <div v-else-if="sectionStore.sections.length === 0" class="info-message">
        No sections found. Click "Add Section" to create one.
      </div>
      
      <table v-else>
        <thead>
          <tr>
            <th>Section Name</th>
            <th>Students</th>
            <th>Description</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="section in sectionStore.sections" :key="section.id">
            <td><strong>{{ section.name }}</strong></td>
            <td>
              <span class="badge">{{ section.students_count || 0 }} students</span>
            </td>
            <td>{{ section.description || '-' }}</td>
            <td>
              <button class="btn btn-sm btn-primary" @click="editSection(section)">Edit</button>
              <button class="btn btn-sm btn-danger" @click="deleteSectionConfirm(section.id)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="sectionStore.pagination.last_page > 1" class="pagination">
        <button 
          @click="goToPage(sectionStore.pagination.current_page - 1)"
          :disabled="sectionStore.pagination.current_page === 1"
          class="btn">
          Previous
        </button>
        <span>Page {{ sectionStore.pagination.current_page }} of {{ sectionStore.pagination.last_page }}</span>
        <button 
          @click="goToPage(sectionStore.pagination.current_page + 1)"
          :disabled="sectionStore.pagination.current_page >= sectionStore.pagination.last_page"
          class="btn">
          Next
        </button>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showAddModal" class="modal" @click.self="closeModal">
      <div class="modal-content">
        <h3>{{ editMode ? 'Edit Section' : 'Add New Section' }}</h3>
        <form @submit.prevent="saveSection">
          <div class="form-body">
            <div class="form-group">
              <label>Section Name <span class="required">*</span></label>
              <input 
                v-model="formData.name" 
                class="form-control" 
                placeholder="e.g., A, B, Science, Commerce"
                required 
              />
              <small class="form-text">Unique identifier for the section</small>
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
            <button type="submit" class="btn btn-success" :disabled="sectionStore.loading">
              {{ sectionStore.loading ? 'Saving...' : 'Save Section' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useSectionStore } from '../stores/section'

const sectionStore = useSectionStore()
const searchQuery = ref('')
const showAddModal = ref(false)
const editMode = ref(false)
const formData = ref({
  name: '',
  description: ''
})

onMounted(async () => {
  await fetchSections()
})

const fetchSections = async () => {
  const params = {
    per_page: 15
  }
  
  if (searchQuery.value) {
    params.search = searchQuery.value
  }
  
  await sectionStore.fetchSections(params)
}

let debounceTimer
const debouncedSearch = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(fetchSections, 500)
}

const saveSection = async () => {
  try {
    const submitData = {
      name: formData.value.name,
      description: formData.value.description || null
    }

    if (editMode.value) {
      await sectionStore.updateSection(formData.value.id, submitData)
    } else {
      await sectionStore.createSection(submitData)
    }
    closeModal()
    await fetchSections()
  } catch (error) {
    const errorMsg = error.response?.data?.message || error.message
    alert('Failed to save section: ' + errorMsg)
  }
}

const editSection = (section) => {
  editMode.value = true
  formData.value = {
    id: section.id,
    name: section.name,
    description: section.description
  }
  showAddModal.value = true
}

const deleteSectionConfirm = async (id) => {
  if (confirm('Are you sure you want to delete this section? All students in this section will need to be reassigned.')) {
    try {
      await sectionStore.deleteSection(id)
      await fetchSections()
    } catch (error) {
      alert('Failed to delete section: ' + (error.response?.data?.message || error.message))
    }
  }
}

const closeModal = () => {
  showAddModal.value = false
  editMode.value = false
  formData.value = {
    name: '',
    description: ''
  }
}

const goToPage = async (page) => {
  const params = { page }
  
  if (searchQuery.value) {
    params.search = searchQuery.value
  }
  
  await sectionStore.fetchSections(params)
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

.form-text {
  display: block;
  margin-top: 0.25rem;
  font-size: 0.875rem;
  color: #6c757d;
}

.required {
  color: #ef4444;
}

.badge {
  background: #e9ecef;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.875rem;
  color: #495057;
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
</style>
