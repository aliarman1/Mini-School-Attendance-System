import { defineStore } from 'pinia'
import api from '../services/api'

export const useSectionStore = defineStore('section', {
  state: () => ({
    sections: [],
    currentSection: null,
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
    allSections: (state) => state.sections,
    isLoading: (state) => state.loading,
    hasError: (state) => state.error !== null,
  },

  actions: {
    async fetchSections(params = {}) {
      this.loading = true
      this.error = null
      try {
        const response = await api.get('/sections', { params })
        
        if (response.data.success) {
          this.sections = response.data.data
          if (response.data.pagination) {
            this.pagination = response.data.pagination
          }
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch sections'
        console.error('Error fetching sections:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchAllSections() {
      // Fetch all sections without pagination (useful for dropdowns)
      this.loading = true
      this.error = null
      try {
        const response = await api.get('/sections', {
          params: {
            per_page: 100 // Fetch more for dropdowns
          }
        })
        
        if (response.data.success) {
          this.sections = response.data.data
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch sections'
        console.error('Error fetching sections:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchSection(id) {
      this.loading = true
      this.error = null
      try {
        const response = await api.get(`/sections/${id}`)
        
        if (response.data.success) {
          this.currentSection = response.data.data
          return response.data.data
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch section'
        console.error('Error fetching section:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async createSection(sectionData) {
      this.loading = true
      this.error = null
      try {
        const response = await api.post('/sections', sectionData)
        
        if (response.data.success) {
          await this.fetchSections()
          return response.data.data
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create section'
        console.error('Error creating section:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateSection(id, sectionData) {
      this.loading = true
      this.error = null
      try {
        const response = await api.put(`/sections/${id}`, sectionData)
        
        if (response.data.success) {
          await this.fetchSections()
          return response.data.data
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update section'
        console.error('Error updating section:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteSection(id) {
      this.loading = true
      this.error = null
      try {
        const response = await api.delete(`/sections/${id}`)
        
        if (response.data.success) {
          await this.fetchSections()
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete section'
        console.error('Error deleting section:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    clearError() {
      this.error = null
    },

    clearState() {
      this.sections = []
      this.currentSection = null
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
