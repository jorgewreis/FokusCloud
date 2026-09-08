import { copyFile, mkdir } from 'node:fs/promises';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const projectRoot = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const source = resolve(projectRoot, 'node_modules/fokus-styles/dist/css/fokus.css');
const target = resolve(projectRoot, 'public/assets/vendor/fokus-styles/fokus.css');

await mkdir(dirname(target), { recursive: true });
await copyFile(source, target);
console.log(`Synced fokus-styles to ${target}`);
