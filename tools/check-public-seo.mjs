import { execFileSync } from 'node:child_process';
import { readFileSync } from 'node:fs';

const pages = JSON.parse(readFileSync(new URL('../resources/seo/pages.json', import.meta.url), 'utf8'));
const curl = process.platform === 'win32' ? 'curl.exe' : 'curl';
const fetchPage = (url) => {
  const result = execFileSync(curl, ['--silent', '--show-error', '--fail-with-body', '--max-time', '30', '--location', '--write-out', '\n%{http_code}\n%{url_effective}', url], { encoding: 'utf8', maxBuffer: 4 * 1024 * 1024 });
  const lines = result.trimEnd().split('\n');
  const finalUrl = lines.pop();
  const status = lines.pop();
  if (status !== '200') throw new Error(`${url}: HTTP ${status}`);
  return { body: lines.join('\n'), finalUrl };
};
for (const page of pages) {
  const { body, finalUrl } = fetchPage(page.url);
  if (finalUrl !== page.url) throw new Error(`${page.url}: unexpected redirect to ${finalUrl}`);
  for (const expected of [`<title>${page.title}</title>`, `<link rel="canonical" href="${page.url}"`, page.description]) {
    if (!body.includes(expected)) throw new Error(`${page.url}: metadata not published: ${expected}`);
  }
  if (/<meta[^>]+name="robots"[^>]+noindex/i.test(body)) throw new Error(`${page.url}: noindex`);
  const schemas = [...body.matchAll(/<script type="application\/ld\+json">([\s\S]*?)<\/script>/g)];
  if (schemas.length !== 1) throw new Error(`${page.url}: expected one JSON-LD graph`);
  JSON.parse(schemas[0][1]);
  console.log(`OK ${page.url}: HTTP 200, canonical, metadata, JSON-LD`);
}
for (const host of ['www.fokuscloud.com.br', 'styles.fokuscloud.com.br']) {
  const sitemapUrl = `https://${host}/sitemap.xml`;
  const { body: xml } = fetchPage(sitemapUrl);
  const actual = [...xml.matchAll(/<loc>(.*?)<\/loc>/g)].map((match) => match[1]).sort();
  const expected = pages.filter((page) => new URL(page.url).host === host).map((page) => page.url).sort();
  if (JSON.stringify(actual) !== JSON.stringify(expected)) throw new Error(`${host}: incorrect sitemap URLs`);
  const { body: robots } = fetchPage(`https://${host}/robots.txt`);
  if (!robots.includes(`Sitemap: ${sitemapUrl}`)) throw new Error(`${host}: missing sitemap in robots.txt`);
  console.log(`OK ${host}: robots.txt and ${actual.length} sitemap URLs`);
}
if (fetchPage('https://fokuscloud.com.br/').finalUrl !== 'https://www.fokuscloud.com.br/') {
  throw new Error('Apex domain does not redirect to the canonical www host');
}
console.log('Production SEO publication verified. This does not assert indexing or ranking.');
