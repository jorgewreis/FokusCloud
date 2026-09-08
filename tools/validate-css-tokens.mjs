import fs from 'node:fs';
import path from 'node:path';
import process from 'node:process';

const root = process.cwd();
const tokenDir = path.join(root, 'public', 'assets', 'css', 'shared', 'tokens');
const files = fs.readdirSync(tokenDir).filter((name) => name.endsWith('.css')).sort();
const definitions = new Map();
const references = [];
const failures = [];
const warnings = [];

for (const file of files) {
    const fullPath = path.join(tokenDir, file);
    const source = fs.readFileSync(fullPath, 'utf8');
    const lines = source.split(/\r?\n/);
    let braceDepth = 0;
    let mediaDepth = null;

    lines.forEach((line, index) => {
        const isMediaStart = /@media\b/i.test(line);
        if (isMediaStart) mediaDepth = braceDepth + 1;
        const insideMedia = mediaDepth !== null && braceDepth >= mediaDepth;
        const definition = line.match(/--([a-z0-9-]+)\s*:/i);
        if (definition) {
            const name = `--${definition[1]}`;
            if (definitions.has(name) && !insideMedia) {
                failures.push(`${file}:${index + 1}: duplicate token ${name}; first defined in ${definitions.get(name)}`);
            } else {
                if (!definitions.has(name)) definitions.set(name, `${file}:${index + 1}`);
            }
        }

        for (const match of line.matchAll(/var\((--[a-z0-9-]+)/gi)) {
            references.push({ name: match[1], file, line: index + 1 });
        }

        braceDepth += (line.match(/{/g) || []).length;
        braceDepth -= (line.match(/}/g) || []).length;
        if (mediaDepth !== null && braceDepth < mediaDepth) mediaDepth = null;
    });
}

for (const reference of references) {
    if (!definitions.has(reference.name)) {
        failures.push(`${reference.file}:${reference.line}: undefined token ${reference.name}`);
    }
}

const extractClasses = (directory) => {
    if (!fs.existsSync(directory)) return new Map();
    const result = new Map();
    for (const file of fs.readdirSync(directory).filter((name) => name.endsWith('.css'))) {
        const source = fs.readFileSync(path.join(directory, file), 'utf8')
            .replace(/\/\*[\s\S]*?\*\//g, '')
            .replace(/^\s*@import[^;]+;/gm, '');
        for (const match of source.matchAll(/\.([a-zA-Z_][a-zA-Z0-9_-]*)/g)) {
            const name = match[1];
            if (!result.has(name)) result.set(name, file);
        }
    }
    return result;
};

const sharedClasses = extractClasses(path.join(root, 'public', 'assets', 'css', 'shared', 'layout'));
const backofficeClasses = extractClasses(path.join(root, 'public', 'backoffice', 'assets', 'css', 'layout'));
for (const [name, file] of sharedClasses) {
    if (backofficeClasses.has(name)) {
        warnings.push(`class overlap .${name}: shared/layout/${file} and backoffice/layout/${backofficeClasses.get(name)}`);
    }
}

const report = [
    `Checked ${files.length} token files`,
    `Definitions: ${definitions.size}`,
    `References: ${references.length}`,
];

if (failures.length) {
    console.error(`${report.join('\n')}\n\nToken validation failed:`);
    console.error(failures.map((failure) => `- ${failure}`).join('\n'));
    process.exitCode = 1;
} else {
    console.log(`${report.join('\n')}\nToken validation passed.`);
    if (warnings.length) {
        console.warn(`\nLayout overlap warnings:\n${warnings.map((warning) => `- ${warning}`).join('\n')}`);
    }
}
