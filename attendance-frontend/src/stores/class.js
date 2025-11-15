import { defineStore } from 'pinia'
import api from '../services/api'

export const useClassStore = defineStore('class', {
  state: () => ({
    classes: [],
    currentClass: null,
    loading: false,
    error: null,
    pagination: {
      total: 0,
      currentPage: 1,
      lastPage: 1,
      perPage: 15
    }
  }),

  getters: {
    allClasses: (state) => state.classes,
    isLoading: (state) => state.loading,
    hasError: (state) => state.error !== null,
  },

  actions: {
    async fetchClasses(params = {}) {
      this.loading = true
      this.error = null
      try {
        const response = await api.get('/classes', { params })
        
        if (response.data.success) {
          this.classes = response.data.data
          if (response.data.pagination) {
            this.pagination = response.data.pagination
          }
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch classes'
        console.error('Error fetching classes:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchAllClasses() {
      // Fetch all classes without pagination (useful for dropdowns)
      this.loading = true
      this.error = null
      try {
        const response = await api.get('/classes', {
          params: {
            per_page: 100 // Fetch more for dropdowns
          }
        })
        
        if (response.data.success) {
          this.classes = response.data.data
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch classes'
        console.error('Error fetching classes:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchClass(id) {
      this.loading = true
      this.error = null
      try {
        const response = await api.get(`/classes/${id}`)
        
        if (response.data.success) {
          this.currentClass = response.data.data
          return response.data.data
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch class'
        console.error('Error fetching class:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async createClass(classData) {
      this.loading = true
      this.error = null
      try {
        const response = await api.post('/classes', classData)
        
        if (response.data.success) {
          await this.fetchClasses()
          return response.data.data
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create class'
        console.error('Error creating class:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateClass(id, classData) {
      this.loading = true
      this.error = null
      try {
        const response = await api.put(`/classes/${id}`, classData)
        
        if (response.data.success) {
          await this.fetchClasses()
          return response.data.data
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update class'
        console.error('Error updating class:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteClass(id) {
      this.loading = true
      this.error = null
      try {
        const response = await api.delete(`/classes/${id}`)
        
        if (response.data.success) {
          await this.fetchClasses()
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete class'
        console.error('Error deleting class:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    clearError() {
      this.error = null
    },

    clearState() {
      this.classes = []
      this.currentClass = null
      this.loading = false
      this.error = null
      this.pagination = {
        total: 0,
        currentPage: 1,
        lastPage: 1,
        perPage: 15
      }
    }
  }
})
