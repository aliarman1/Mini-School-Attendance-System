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
          <option value="9A">Class 9A</option>
          <option value="9B">Class 9B</option>
          <option value="10A">Class 10A</option>
          <option value="10B">Class 10B</option>
        </select>
      </div>

      <div v-if="studentStore.loading" class="loading">Loading...</div>
      <div v-else-if="studentStore.error" class="error">{{ studentStore.error }}</div>
      
      <table v-else>
        <thead>
          <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Class</th>
            <th>Section</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="student in studentStore.students" :key="student.id">
            <td>{{ student.student_id }}</td>
            <td>{{ student.name }}</td>
            <td>{{ student.class }}</td>
            <td>{{ student.section }}</td>
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

    <!-- Add/Edit Modal -->
    <div v-if="showAddModal" class="modal">
      <div class="modal-content">
        <h3>{{ editMode ? 'Edit Student' : 'Add New Student' }}</h3>
        <form @submit.prevent="saveStudent">
          <div class="form-group">
            <label>Student ID</label>
            <input v-model="formData.student_id" class="form-control" required />
          </div>
          <div class="form-group">
            <label>Name</label>
            <input v-model="formData.name" class="form-control" required />
          </div>
          <div class="form-group">
            <label>Class</label>
            <input v-model="formData.class" class="form-control" required />
          </div>
          <div class="form-group">
            <label>Section</label>
            <input v-model="formData.section" class="form-control" required />
          </div>
          <div class="modal-actions">
            <button type="submit" class="btn btn-success">Save</button>
            <button type="button" class="btn" @click="closeModal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useStudentStore } from '../stores/student'

const studentStore = useStudentStore()
const searchQuery = ref('')
const classFilter = ref('')
const showAddModal = ref(false)
const editMode = ref(false)
const formData = ref({
  student_id: '',
  name: '',
  class: '',
  section: ''
})

onMounted(() => {
  fetchStudents()
})

const fetchStudents = async () => {
  await studentStore.fetchStudents({
    search: searchQuery.value,
    class: classFilter.value,
    per_page: 15
  })
}

let debounceTimer
const debouncedSearch = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(fetchStudents, 500)
}

const saveStudent = async () => {
  try {
    if (editMode.value) {
      await studentStore.updateStudent(formData.value.id, formData.value)
    } else {
      await studentStore.createStudent(formData.value)
    }
    closeModal()
    await fetchStudents()
  } catch (error) {
    alert('Failed to save student')
  }
}

const editStudent = (student) => {
  editMode.value = true
  formData.value = { ...student }
  showAddModal.value = true
}

const deleteStudentConfirm = async (id) => {
  if (confirm('Are you sure you want to delete this student?')) {
    try {
      await studentStore.deleteStudent(id)
      await fetchStudents()
    } catch (error) {
      alert('Failed to delete student')
    }
  }
}

const closeModal = () => {
  showAddModal.value = false
  editMode.value = false
  formData.value = {
    student_id: '',
    name: '',
    class: '',
    section: ''
  }
}

const goToPage = async (page) => {
  await studentStore.fetchStudents({
    search: searchQuery.value,
    class: classFilter.value,
    page
  })
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
  grid-template-columns: 1fr auto;
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
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  width: 500px;
  max-width: 90%;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 1.5rem;
}
</style>
