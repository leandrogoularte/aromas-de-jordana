/* Aromas de Jordana — dados da vitrine e render dinâmico
 * Os produtos vêm de assets/data/produtos.json (publicados pelo admin em /admin).
 * Se o arquivo não carregar (ex.: computador sem rede), usa a lista padrão abaixo. */

var PRODUTOS_DEFAULT = [
  {
    id: 'nossa-senhora',
    nome: 'Vela Decorativa Nossa Senhora Aparecida',
    descricao: 'Vela decorativa inspirada em Nossa Senhora Aparecida, perfeita para presentear, decorar e celebrar a fé.',
    preco: 69.9,
    categoria: 'sagradas',
    imagens: [
      { src: 'assets/img/vela-nossa-senhora.webp', alt: 'Vela decorativa de Nossa Senhora Aparecida' }
    ],
    ctaTexto: 'Tenho interesse',
    ctaHref: '#contato'
  },
  {
    id: 'sao-miguel',
    nome: 'Velas de São Miguel Arcanjo',
    descricao: 'Em gel azul, personalizada ou com perfume de fé — escolha a sua versão favorita de São Miguel.',
    preco: 69.9,
    categoria: 'sagradas',
    imagens: [
      { src: 'assets/img/vela-sao-miguel-gel.webp', alt: 'Vela de São Miguel Arcanjo em gel azul' },
      { src: 'assets/img/vela-sao-miguel-personalizada.webp', alt: 'Vela personalizada de São Miguel' },
      { src: 'assets/img/vela-sao-miguel-fe.webp', alt: 'Vela São Miguel, fé que perfuma' }
    ],
    ctaTexto: 'Como personalizar',
    ctaHref: '#personalizacao'
  }
];

var PRODUTOS = [];
var PRODUTOS_PRONTOS = false;

var CATEGORIAS = [
  { id: 'todas', rotulo: 'Todas' },
  { id: 'sagradas', rotulo: 'Sagradas' },
  { id: 'decorativas', rotulo: 'Decorativas' },
  { id: 'lembrancinhas', rotulo: 'Lembrancinhas' }
];

function esc(texto) {
  return String(texto == null ? '' : texto)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function caminhoSeguro(src) {
  src = String(src || '');
  if (/^assets\//i.test(src)) return src;
  if (/^data:image\//i.test(src)) return src;
  return '';
}

function normalizarProduto(p) {
  return {
    id: esc(p.id ? p.id : 'produto-' + Math.random().toString(36).slice(2, 8)),
    nome: esc(p.nome || 'Produto'),
    descricao: esc(p.descricao || ''),
    preco: Number(p.preco) || 0,
    categoria: esc(p.categoria || 'sagradas'),
    imagens: Array.isArray(p.imagens) && p.imagens.length
      ? p.imagens.map(function (img) {
          var src = typeof img === 'string' ? img : (img && img.src);
          var alt = typeof img === 'string' ? String(p.nome || '') : String((img && img.alt) || p.nome || '');
          return { src: caminhoSeguro(src), alt: esc(alt) };
        }).filter(function (img) { return img.src !== ''; })
      : [],
    ctaTexto: esc(p.ctaTexto || 'Tenho interesse'),
    ctaHref: /^#/.test(String(p.ctaHref || '')) ? esc(p.ctaHref) : esc('#contato')
  };
}

function carregarProdutos(callback) {
  var url = 'assets/data/produtos.json?v=' + Date.now();
  fetch(url, { cache: 'no-store' })
    .then(function (res) {
      if (!res.ok) throw new Error('HTTP ' + res.status);
      return res.json();
    })
    .then(function (json) {
      var lista = Array.isArray(json) ? json : (Array.isArray(json.produtos) ? json.produtos : []);
      PRODUTOS = lista.map(normalizarProduto);
      PRODUTOS_PRONTOS = true;
      callback();
    })
    .catch(function () {
      PRODUTOS = PRODUTOS_DEFAULT.map(normalizarProduto);
      PRODUTOS_PRONTOS = true;
      callback();
    });
}

function formatarPreco(valor) {
  return valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function cardHTML(p) {
  var galeria = p.imagens
    .map(function (img, i) {
      var ativo = i === 0 ? ' is-active' : '';
      return (
        '<button class="thumb' + ativo + '" type="button" data-gallery="gal-' + p.id +
        '" data-src="' + img.src + '" aria-label="' + img.alt + '">' +
        '<img loading="lazy" src="' + img.src + '" alt="" /></button>'
      );
    })
    .join('');

  var thumbs = p.imagens.length > 1 ? '<div class="thumbs">' + galeria + '</div>' : '';

  return (
    '<article class="product-card">' +
    '<div class="product-card__image-wrap">' +
    '<span class="badge">Artesanal</span>' +
    '<img loading="lazy" class="product-card__image" id="gal-' + p.id + '" src="' + p.imagens[0].src + '" alt="' + p.imagens[0].alt + '" />' +
    '</div>' +
    thumbs +
    '<div class="product-card__body">' +
    '<h3 class="product-card__name">' + p.nome + '</h3>' +
    '<p class="product-card__desc">' + p.descricao + '</p>' +
    '<p class="product-card__price">' + formatarPreco(p.preco) + '</p>' +
    '<a class="ghost-link" href="' + p.ctaHref + '">' + p.ctaTexto + ' <span class="arrow">→</span></a>' +
    '</div>' +
    '</article>'
  );
}

function renderizarProdutos(filtro) {
  var grid = document.getElementById('catalog-grid');
  var vazio = document.getElementById('catalog-empty');
  var lista = filtro === 'todas' ? PRODUTOS : PRODUTOS.filter(function (p) { return p.categoria === filtro; });

  grid.innerHTML = '';
  if (lista.length === 0) {
    grid.hidden = true;
    vazio.hidden = false;
    return;
  }
  grid.hidden = false;
  vazio.hidden = true;
  lista.forEach(function (p) {
    grid.insertAdjacentHTML('beforeend', cardHTML(p));
  });
}

function renderizarAbas() {
  var wrap = document.getElementById('catalog-tabs');
  var count = 0;
  CATEGORIAS.forEach(function (cat) {
    count = cat.id === 'todas' ? PRODUTOS.length : PRODUTOS.filter(function (p) { return p.categoria === cat.id; }).length;
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'catalog-tab' + (cat.id === 'todas' ? ' is-active' : '');
    btn.dataset.categoria = cat.id;
    btn.setAttribute('aria-pressed', cat.id === 'todas' ? 'true' : 'false');
    btn.innerHTML = '<span class="catalog-tab__label">' + cat.rotulo + '</span> <span class="catalog-tab__count">' + count + '</span>';
    wrap.appendChild(btn);
  });

  wrap.addEventListener('click', function (e) {
    var tab = e.target.closest('.catalog-tab');
    if (!tab || tab.classList.contains('is-active')) return;
    wrap.querySelectorAll('.catalog-tab').forEach(function (t) {
      var ativo = t === tab;
      t.classList.toggle('is-active', ativo);
      t.setAttribute('aria-pressed', String(ativo));
    });
    renderizarProdutos(tab.dataset.categoria);
  });
}

document.addEventListener('DOMContentLoaded', function () {
  carregarProdutos(function () {
    renderizarAbas();
    renderizarProdutos('todas');
  });

  var grid = document.getElementById('catalog-grid');
  grid.addEventListener('click', function (e) {
    var thumb = e.target.closest('.thumb');
    if (!thumb) return;
    var galeria = document.getElementById(thumb.dataset.gallery);
    if (galeria && thumb.dataset.src) {
      galeria.src = thumb.dataset.src;
    }
    thumb.parentElement.querySelectorAll('.thumb').forEach(function (t) {
      t.classList.remove('is-active');
    });
    thumb.classList.add('is-active');
  });
});