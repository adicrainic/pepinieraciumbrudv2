import { api } from './api'

export type TrafficPeriod = 'today' | '7d' | '30d' | '90d'

export interface TrafficSummary {
  unique_visitors: number
  total_pageviews: number
  pages_per_visitor: number
  orders_count: number
  conversion_rate: number
  previous_unique_visitors: number
  previous_total_pageviews: number
  today_visitors: number
  today_pageviews: number
}

export interface TrafficDailyItem {
  date: string
  label: string
  unique_visitors: number
  pageviews: number
}

export interface TrafficTopPage {
  page_path: string
  page_title: string
  pageviews: number
  unique_visitors: number
  share_percent: number
}

export interface TrafficReferrer {
  source: string
  pageviews: number
  unique_visitors: number
  share_percent: number
}

export interface TrafficDevice {
  device_type: 'desktop' | 'mobile' | 'tablet'
  label: string
  pageviews: number
  unique_visitors: number
  share_percent: number
}

export interface TrafficLocation {
  country_code: string
  country_name: string
  region_name: string
  city_name: string
  label: string
  pageviews: number
  unique_visitors: number
  share_percent: number
}

export interface TrafficStatsResponse {
  period: TrafficPeriod
  date_range: {
    start_date: string
    end_date: string
    days: number
  }
  summary: TrafficSummary
  daily: TrafficDailyItem[]
  top_pages: TrafficTopPage[]
  referrers: TrafficReferrer[]
  devices: TrafficDevice[]
  locations: TrafficLocation[]
}

export const trafficService = {
  async getStats(period: TrafficPeriod = '30d'): Promise<TrafficStatsResponse> {
    const res = await api.get<TrafficStatsResponse>('/admin/traffic', {
      params: { period }
    })
    return res.data
  }
}
