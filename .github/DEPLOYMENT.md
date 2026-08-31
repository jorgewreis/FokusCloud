# Deploy de produção

O workflow `deploy.yml` publica a aplicacao Laravel quando há um push na
branch `main` ou quando e executado manualmente pela aba **Actions**.

Configure os secrets no ambiente GitHub `production`:

- `DEPLOY_HOST`: host SSH, sem protocolo.
- `DEPLOY_PORT`: porta SSH.
- `DEPLOY_USER`: usuário SSH com acesso ao diretório publicado.
- `DEPLOY_PATH`: caminho absoluto do diretório público no servidor.
- `DEPLOY_SSH_KEY`: chave privada do usuário de deploy, em texto puro.
- `DEPLOY_KNOWN_HOSTS`: chave pública do servidor no formato `known_hosts`.

Para obter `DEPLOY_KNOWN_HOSTS`, valide a impressão digital do servidor por um
canal confiável e então use, por exemplo, `ssh-keyscan -p PORTA -H HOST`.

Os secrets abaixo são opcionais e habilitam a limpeza do cache após o deploy:

- `CLOUDFLARE_API_TOKEN`: token com permissão de limpar o cache da zona.
- `CLOUDFLARE_ZONE_ID`: identificador da zona Cloudflare.

Nunca versione chaves privadas ou valores reais de secrets em `.env`.

O deploy limpa caches gerados em `bootstrap/cache`, executa
`composer install`, `php artisan migrate --force` e `php artisan optimize` no
servidor. O diretorio `resources/views` deve existir mesmo quando a aplicacao
servir HTML estatico por `public/`, pois o cache de views do Laravel valida
esse caminho durante a otimizacao.
