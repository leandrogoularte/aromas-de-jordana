# Publicar no HostGator (guia rápido)

## Pré-requisitos
- Domínio comprado `aromasdejordana.com.br` (ou `.com`) e plano de hospedagem ativo.
- Acesso ao **cPanel** (enviam por e-mail ao contratar).
- Este projeto já otimizado (imagens WebP, fonte local, `.htaccess` pronto).

## Passo a passo

1. **Apontar o domínio (DNS)**
   - No painel do HostGator, o domínio já vem apontado para os nameservers deles ao ativar a hospedagem → aguarde a propagação (até ~24h).

2. **Entrar no cPanel** → seção **"Gerenciador de Arquivos" (File Manager)**.

3. **Abrir a pasta `public_html`**
   - Apague o conteúdo de exemplo que vier por padrão (o `index.html`/landing do HostGator).

4. **Enviar os arquivos do site**
   - Faça upload de **todos** os itens da raiz do projeto:
     - `index.html`
     - `.htaccess`
     - pasta `assets/` (css, js, img, fonts)
   - Opcional: os PNGs originais ficam em `assets/img/original/` — **não** são necessários no ar; pode excluí-los ou deixá-los.

5. **Ativar o SSL (gratuito)**
   - No cPanel → seção **"SSL/TLS"** (ou **AutoSSL**) → ative o certificado para o domínio.
   - Depois, o `.htaccess` já força HTTPS automaticamente.

6. **Testar**
   - Acesse `https://aromasdejordana.com.br` (desktop e celular).
   - Confira: vídeo do hero, WebP, fonte Ananda e links de WhatsApp/Instagram.

## Configuração opcional
- **E-mail institucional:** cCPanel → "Email Accounts" → `contato@aromasdejordana.com.br`.
- **Backups:** o HostGator tem backup diário padrão; ative a proteção de backup se quiser reforço.

## Lembrete
- O GitHub Pages continua como **pré-visualização** durante o desenvolvimento.
- O `.htaccess` desta pasta já vem pronto: HTTPS forçado, www → sem-www, compressão e cache.