import { mkdir, readFile, writeFile } from 'node:fs/promises';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const projectRoot = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const source = resolve(projectRoot, 'node_modules/fokus-styles/dist/css/fokus.css');
const target = resolve(projectRoot, 'public/assets/css/shared/fokus.css');

await mkdir(dirname(target), { recursive: true });
const css = await readFile(source, 'utf8');
const sanitizedCss = css
    .replace(/[→←↑↓➜➝➞➤⟶⟹↗↘↙↖↕]/gu, '')
    .replace(/text-decoration:\s*underline(?:\s+dotted)?/gu, 'text-decoration: none');
const linkPolicy = `

/* Product-wide link presentation policy. */
:where(a, a:hover, a:focus-visible, a:active) {
    text-decoration: none !important;
}
`;
const directionalIconPolicy = `

/* Directional icons are intentionally disabled across published interfaces. */
.fs-tooltip-arrow,
.fs-popover-arrow,
.fs-dropdown-rich-chevron,
.fs-tree-toggle,
.fs-datatable-sort-btn::after,
.fs-accordion-button::after {
    display: none !important;
    content: none !important;
}
`;
await writeFile(target, `${sanitizedCss}${linkPolicy}${directionalIconPolicy}`, 'utf8');
console.log(`Synced fokus-styles to ${target}`);
