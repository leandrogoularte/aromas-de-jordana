document.addEventListener('DOMContentLoaded', function () {
  var nav = document.getElementById('nav');
  var toggle = document.getElementById('navToggle');

  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      var isOpen = nav.classList.toggle('nav--open');
      toggle.setAttribute('aria-expanded', String(isOpen));
    });

    nav.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        nav.classList.remove('nav--open');
        toggle.setAttribute('aria-expanded', 'false');
      });
    });
  }

  var year = document.getElementById('year');
  if (year) {
    year.textContent = new Date().getFullYear();
  }

  document.querySelectorAll('.thumb').forEach(function (thumb) {
    thumb.addEventListener('click', function () {
      var gallery = document.getElementById(this.dataset.gallery);
      var group = this.closest('.thumbs');

      if (gallery && this.dataset.src) {
        gallery.src = this.dataset.src;
      }

      if (group) {
        group.querySelectorAll('.thumb').forEach(function (item) {
          item.classList.remove('is-active');
        });
        this.classList.add('is-active');
      }
    });
  });
});