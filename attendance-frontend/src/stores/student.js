import { defineStore } from 'pinia'
import api from '../services/api'

export const useStudentStore = defineStore('student', {
  state: () => ({
    students: [],
    currentStudent: null,
    pagination: {
      total: 0,
      per_page: 15,
      current_page: 1,
      last_page: 1
    },
    loading: false,
    error: null
  }),

  actions: {
    async fetchStudents(params = {}) {
      this.loading = true
      this.error = null
      try {
        const response = await api.get('/students', { params })
        this.students = response.data.data
        this.pagination = response.data.pagination
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch students'
        throw error
      } finally {
        this.loading = false
      }
    },

    async createStudent(studentData) {
      this.loading = true
      this.error = null
      try {
        const response = await api.post('/students', studentData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        this.students.unshift(response.data.data)
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create student'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateStudent(id, studentData) {
      this.loading = true
      this.error = null
      try {
        // Laravel doesn't support PUT with multipart/form-data, so we use POST with _method
        if (studentData instanceof FormData) {
          studentData.append('_method', 'PUT')
          const response = await api.post(`/students/${id}`, studentData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          })
          const index = this.students.findIndex(s => s.id === id)
          if (index !== -1) {
            this.students[index] = response.data.data
          }
          return response.data
        } else {
          const response = await api.put(`/students/${id}`, studentData)
          const index = this.students.findIndex(s => s.id === id)
          if (index !== -1) {
            this.students[index] = response.data.data
          }
          return response.data
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update student'
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteStudent(id) {
      this.loading = true
      this.error = null
      try {
        await api.delete(`/students/${id}`)
        this.students = this.students.filter(s => s.id !== id)
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete student'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Clear store state
    clearState() {
      this.students = []
      this.currentStudent = null
      this.error = null
      this.loading = false
    }
  }
})
