# Publicar no HostGator (guia rápido)

## Pré-requisitos
- Domínio comprado `aromasdejordana.com.br` (ou `.com`) e plano de hospedagem ativo.
- Acesso ao **cPanel** (enviam por e-mail ao contratar).
- Este projeto já otimizado (imagens WebP, fonte local, `.htaccess`, painel de admin pronto).

## Passo a passo

1. **Apontar o domínio (DNS)**
   - No painel do HostGator, o domínio já vem apontado para os nameservers deles ao ativar a hospedagem → aguarde a propagação (até ~24h).

2. **Entrar no cPanel** → seção **"Gerenciador de Arquivos" (File Manager)**.

3. **Abrir a pasta `public_html`**
   - Apague o conteúdo de exemplo que vier por padrão (o `index.html`/landing do HostGator).

4. **Enviar os arquivos do site**
   - No File Manager, ative **"Show Hidden Files" (mostrar arquivos ocultos)** para ver e enviar os arquivos que começam com ponto (`.htaccess`).
   - Faça upload de **todos** os itens da raiz do projeto:
     - `index.html`
     - `.htaccess`
     - `.nojekyll`
     - pasta `assets/` (css, js, img, fonts, data)
     - pasta `admin/` (o painel de administração)
   - Opcional: os PNGs originais ficam em `assets/img/original/` — **não** são necessários no ar; pode excluí-los ou deixá-los.

5. **Ativar o SSL (gratuito)**
   - No cPanel → seção **"SSL/TLS"** (ou **AutoSSL**) → ative o certificado para o domínio.
   - Depois, o `.htaccess` já força HTTPS automaticamente.

6. **Testar o site**
   - Acesse `https://aromasdejordana.com.br` (desktop e celular).
   - Confira: vídeo do hero, WebP, fonte Ananda e links de WhatsApp/Instagram.

7. **Ativar o painel de administração**
   - Acesse `https://aromasdejordana.com.br/admin`.
   - No **primeiro acesso** ele pede para criar uma **senha** (guarde com você).
   - Depois é só entrar com a senha para **adicionar, editar e excluir produtos** sem mexer em nada técnico.

## Como o dono da loja publica um novo produto
1. Abrir `https://aromasdejordana.com.br/admin` e entrar com a senha.
2. Clicar em **"+ Adicionar produto"**.
3. Preencher: nome, descrição, preço (ex.: 69,90), escolher a **categoria** (Sagradas / Decorativas / Lembrancinhas) e **enviar as fotos**.
4. Clicar em **"Adicionar produto"** → pronto, já aparece no site (na aba da categoria escolhida).

O painel salva tudo em `assets/data/produtos.json` e as fotos vão para `assets/img/produtos/` (reduzidas automaticamente).

## Configuração opcional
- **E-mail institucional:** cPanel → "Email Accounts" → `contato@aromasdejordana.com.br`.
- **Backups:** o HostGator tem backup diário padrão; ative a proteção de backup se quiser reforço.

## Segurança do painel
- A senha fica criptografada em `admin/.passwd`, e um `.htaccess` dentro de `admin/` impede que a internet acesse o arquivo.
- Não confie a senha a terceiros e mude-a se precisar (basta apagar `admin/.passwd` e acessar `/admin` de novo para criar outra).

## Lembrete
- O GitHub Pages continua como **pré-visualização** durante o desenvolvimento (a versão lá usa os produtos padrão; no HostGator vale o conteúdo do painel).
- O `.htaccess` da raiz vem pronto: HTTPS forçado, www → sem-www, compressão e cache.