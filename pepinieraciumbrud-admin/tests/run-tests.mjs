import { build } from 'esbuild'
import { spawnSync } from 'node:child_process'
import { mkdtempSync, rmSync } from 'node:fs'
import { tmpdir } from 'node:os'
import { join, resolve, dirname } from 'node:path'
import { fileURLToPath } from 'node:url'

const __dirname = dirname(fileURLToPath(import.meta.url))
const rootDir = resolve(__dirname, '..')
const outDir = mkdtempSync(join(tmpdir(), 'pcs-admin-tests-'))
const outFile = join(outDir, 'order-lifecycle.test.cjs')

try {
  await build({
    entryPoints: [join(__dirname, 'order-lifecycle.test.ts')],
    outfile: outFile,
    bundle: true,
    platform: 'node',
    format: 'cjs',
    target: 'node20',
    alias: {
      '@': join(rootDir, 'src')
    },
    define: {
      'import.meta.env.VITE_API_BASE': JSON.stringify('http://localhost/wp-json/pcs/v1')
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
