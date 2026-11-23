export interface SiteSettings {
  site_title: string
  site_tagline: string
  site_description: string
  logo_url: string
  contact_phone: string
  contact_email: string
  contact_address: string
  schedule_weekdays: string
  schedule_saturday: string
  schedule_sunday: string
  schedule_notes: string
  footer_copyright: string
  shipping_cost?: string
  company_legal_name?: string
  company_cui?: string
  company_reg_com?: string
  company_authorization?: string
}

export interface EmailConfigStatus {
  enabled: boolean
  host: string
  port: number
  auth: boolean
  user: string
  secure: string
  from: string
  from_name: string
}

export interface EmailTestResult {
  success: boolean
  message: string
  error?: string | null
  config: EmailConfigStatus
}
