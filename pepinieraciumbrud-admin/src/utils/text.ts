/**
 * Strips HTML tags and decodes HTML entities into clean plain text for editing.
 * Preserves paragraphs and line breaks appropriately.
 */
export function stripHtml(html?: string | null): string {
  if (!html) return ''

  // Replace <br> and variants with newline
  let text = html.replace(/<br\s*[\/]?>/gi, '\n')

  // Replace closing block tags with newlines
  text = text.replace(/<\/(p|div|h[1-6]|tr)>/gi, '\n\n')

  // Format list items nicely
  text = text.replace(/<li[^>]*>/gi, '• ').replace(/<\/li>/gi, '\n')

  // Strip all remaining HTML tags
  text = text.replace(/<[^>]+>/g, '')

  // Decode HTML entities using DOMParser in browser if available
  if (typeof DOMParser !== 'undefined') {
    try {
      const doc = new DOMParser().parseFromString(text, 'text/html')
      text = doc.body.textContent || text
    } catch {
      // fallback
    }
  }

  // Also support manual entity decoding for any residual entities
  const entities: Record<string, string> = {
    '&nbsp;': ' ',
    '&amp;': '&',
    '&lt;': '<',
    '&gt;': '>',
    '&quot;': '"',
    '&#039;': "'",
    '&apos;': "'",
    '&#8211;': '–',
    '&#8212;': '—',
    '&#8216;': '‘',
    '&#8217;': '’',
    '&#8220;': '“',
    '&#8221;': '”',
    '&hellip;': '…'
  }
  text = text.replace(/&[a-z0-9#]+;/gi, match => entities[match] || match)
  text = text.replace(/&#(\d+);/g, (_, dec) => String.fromCharCode(Number(dec)))
  text = text.replace(/&#x([0-9a-f]+);/gi, (_, hex) => String.fromCharCode(parseInt(hex, 16)))

  // Normalize lines and excessive newlines
  return text
    .split('\n')
    .map(line => line.trim())
    .join('\n')
    .replace(/\n{3,}/g, '\n\n')
    .trim()
}

