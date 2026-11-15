import { build } from 'esbuild'
import { spawnSync } from 'node:child_process'
import { mkdtempSync, rmSync } from 'node:fs'
import { tmpdir } from 'node:os'
import { join, resolve, dirname } from 'node:path'
import { fileURLToPath } from 'node:url'

const __dirname = dirname(fileURLToPath(import.meta.url))
const rootDir = resolve(__dirname, '..')
const outDir = mkdtempSync(join(tmpdir(), 'pcs-storefront-tests-'))
const outFile = join(outDir, 'order-placement.test.cjs')

try {
  await build({
    entryPoints: [join(__dirname, 'order-placement.test.ts')],
    outfile: outFile,
    bundle: true,
    platform: 'node',
    format: 'cjs',
    target: 'node20',
    alias: {
      '~': rootDir,
      '@': rootDir
    },
    define: {
      'import.meta.client': 'true'
    }
  })

  const res = spawnSync(process.execPath, ['--test', outFile], {
    stdio: 'inherit',
    cwd: rootDir
  })

  process.exit(res.status ?? 1)
} finally {
  rmSync(outDir, { recursive: true, force: true })
}
