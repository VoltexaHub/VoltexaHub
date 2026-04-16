#!/usr/bin/env node
// Fails the build if a blade/php file references a Vite entry
// (@vite('resources/...') or Vite::asset('resources/...')) that is
// not declared in vite.config.js inputs[]. Catches bugs like a new
// JS file being loaded in a view but never getting a manifest entry.

import { readFileSync, readdirSync } from 'node:fs';
import { join, relative } from 'node:path';

const root = process.cwd();

const config = readFileSync(`${root}/vite.config.js`, 'utf8');
const inputBlock = config.match(/input\s*:\s*\[([\s\S]*?)\]/);
if (!inputBlock) {
    console.error('check-vite-inputs: could not find input: [...] block in vite.config.js');
    process.exit(2);
}
const declared = new Set(
    [...inputBlock[1].matchAll(/['"]([^'"]+)['"]/g)].map((m) => m[1]),
);

const SKIP_DIRS = new Set(['node_modules', 'vendor', 'storage', '.git', 'public', 'dist', 'build', 'tests']);
const SCAN_DIRS = ['app', 'resources', 'themes', 'routes', 'plugins'];

const files = [];
function walk(dir) {
    let entries;
    try { entries = readdirSync(dir, { withFileTypes: true }); } catch { return; }
    for (const entry of entries) {
        if (SKIP_DIRS.has(entry.name)) continue;
        const full = join(dir, entry.name);
        if (entry.isDirectory()) walk(full);
        else if (entry.isFile() && /\.(blade\.php|php)$/.test(entry.name)) {
            files.push(relative(root, full).replace(/\\/g, '/'));
        }
    }
}
for (const d of SCAN_DIRS) walk(join(root, d));

const viteCall = /@vite\s*\(\s*(\[[^\]]*\]|'[^']*'|"[^"]*")/g;
const missing = new Map();

for (const file of files) {
    const src = readFileSync(`${root}/${file}`, 'utf8');
    for (const match of src.matchAll(viteCall)) {
        const arg = match[1];
        const paths = [...arg.matchAll(/['"]([^'"]+\.(?:js|ts|css|scss))['"]/g)].map((m) => m[1]);
        for (const p of paths) {
            if (!declared.has(p)) {
                if (!missing.has(p)) missing.set(p, []);
                missing.get(p).push(file);
            }
        }
    }
}

if (missing.size > 0) {
    console.error('check-vite-inputs: entries referenced by views but missing from vite.config.js inputs[]:');
    for (const [path, refs] of missing) {
        console.error(`  - ${path}`);
        for (const ref of refs) console.error(`      used in ${ref}`);
    }
    console.error('\nAdd each path to the input array in vite.config.js.');
    process.exit(1);
}

console.log(`check-vite-inputs: OK (${declared.size} inputs, all @vite() references resolved)`);
