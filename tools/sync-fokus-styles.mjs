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
    .replace(/text-decoration:\s*underline(?:\s+dotted)?/gu, 'text-decoration: none')
    .replace(/background-image:\s*url\("data:image\/svg\+xml,[^"]+"\);/gu, 'background-image: none;')
    .replace(/,\s*\n\s*[^{}\n]*svg[^{}\n]*/giu, '')
    .replace(/^[^{}\n]*svg[^{}\n]*\{[^{}]*\}\s*/gimu, '');
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
const sharedPageHeaderPolicy = `

/* Shared internal page header contract. */
.page-header {
    display: flex;
    flex-direction: row;
    align-items: flex-end;
    justify-content: space-between;
    margin: 0 20px 30px;
}
.page-header .header-title {
    display: flex;
    flex-direction: column;
}
.page-header .header-title .title-heading {
    margin-left: 2px;
    color: #ff6000;
    font-family: "Bebas Neue", sans-serif;
    font-size: 22px;
    font-weight: 400;
}
.page-header .header-title .title-page {
    margin: -16px 0;
    font-family: "Bebas Neue", sans-serif;
    font-size: 64px;
    font-weight: 500;
}
.page-header .header-title .title-description {
    margin-left: 2px;
    color: var(--fs-color-text, #102a43);
    font-family: "Google Sans", sans-serif;
    font-size: 14px;
    font-weight: 400;
    opacity: .5;
}
.fs-btn {
    padding: 0 15px;
    font-family: "Google Sans", sans-serif;
    font-size: 14px;
    font-weight: 500;
}
.fs-btn-primary {
    height: 38px;
    border: 1px solid var(--fs-color-text, #102a43);
    border-radius: 4px;
    background: var(--fs-color-text, #102a43);
    color: #fff;
}
`;
await writeFile(target, `${sanitizedCss}${linkPolicy}${directionalIconPolicy}${sharedPageHeaderPolicy}`, 'utf8');
console.log(`Synced fokus-styles to ${target}`);
