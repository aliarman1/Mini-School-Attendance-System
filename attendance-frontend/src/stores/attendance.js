import { defineStore } from 'pinia'
import api from '../services/api'

export const useAttendanceStore = defineStore('attendance', {
  state: () => ({
    attendances: [],
    statistics: null,
    todayAttendance: null,
    loading: false,
    error: null
  }),

  actions: {
    async recordBulkAttendance(data) {
      this.loading = true
      this.error = null
      try {
        const response = await api.post('/attendance/bulk', data)
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to record attendance'
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchStatistics() {
      this.loading = true
      this.error = null
      try {
        const response = await api.get('/attendance/statistics')
        this.statistics = response.data.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch statistics'
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchTodayAttendance() {
      this.loading = true
      this.error = null
      try {
        const response = await api.get('/attendance/today')
        this.todayAttendance = response.data.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch today\'s attendance'
        throw error
      } finally {
        this.loading = false
      }
    },

    async generateMonthlyReport(month, classname) {
      this.loading = true
      this.error = null
      try {
        const response = await api.get('/attendance/report/monthly', {
          params: { month, class: classname }
        })
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to generate report'
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})
