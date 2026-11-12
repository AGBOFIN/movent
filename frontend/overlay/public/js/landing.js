(function () {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
  var KINDS = { INSTITUTION: 'Institution', PRIVATE: 'Privé' };
  var VTYPES = { HOTEL: 'Hôtel', PALACE: 'Palais / congrès', STADIUM: 'Stade', CULTURAL_CENTER: 'Centre culturel', BEACH: 'Plage / site', OTHER: 'Lieu' };
  var GR = ['var(--g1)', 'var(--g2)', 'var(--g3)', 'var(--g4)', 'var(--g5)', 'var(--g6)'];
  var grad = function (n) { var h = 0; String(n || '').split('').forEach(function (c) { h += c.charCodeAt(0); }); return GR[h % GR.length]; };
  var money = function (n) { return Number(n) === 0 ? 'Gratuit' : new Intl.NumberFormat('fr-FR').format(Number(n)) + ' FCFA'; };
  var when = function (d) { return d ? new Date(d).toLocaleString('fr-FR', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) : ''; };
  var api = function (p) { return fetch('/api' + p, { headers: { Accept: 'application/json' } }).then(function (r) { return r.json(); }); };

  /* ---------- catalogue d'événements ---------- */
  var grid = $('#grid'), more = $('#more'), q = $('#q'), kind = $('#kind'), free = $('#free');
  var catId = '', page = 1, lastPage = 1;

  function card(e) {
    var pic = e.image
      ? ' style="background:url(\'' + esc(e.image) + '\') center/cover"'
      : ' style="background:' + grad(e.category && e.category.name) + '"';
    return '<a class="lcard" href="/events/' + esc(e.id) + '">' +
      '<div class="pic"' + pic + '><span>' + esc(e.category ? e.category.name : 'Événement') + '</span></div>' +
      '<div class="info"><b style="font-size:16px">' + esc(e.title) + '</b>' +
      '<span class="meta">' + when(e.start_at) + ' · ' + esc(e.location) + '</span>' +
      (e.venue ? '<span class="meta">' + esc(e.venue.name) + ' · ' + esc(e.venue.city) + '</span>' : '') +
      '<span class="price">' + money(e.price) + '</span>' +
      '<span class="meta">' + e.available_places + ' place(s) · <span class="badge ' + (e.organizer_kind || '') + '">' + esc(KINDS[e.organizer_kind] || '') + '</span></span>' +
      '</div></a>';
  }

  function params() {
    var p = new URLSearchParams({ page: page });
    if (q.value.trim()) p.set('q', q.value.trim());
    if (catId) p.set('category_id', catId);
    if (kind.value) p.set('organizer_kind', kind.value);
    if (free.checked) p.set('free', '1');
    return p;
  }

  function skeletons(n) {
    var h = '';
    for (var i = 0; i < n; i++) h += '<div class="skel skel-card"></div>';
    grid.innerHTML = h;
  }

  function load(reset) {
    if (reset) { page = 1; skeletons(6); }
    return api('/events?' + params())
      .then(function (d) {
        if (reset) grid.innerHTML = '';
        var items = d.data || [];
        if (!items.length && page === 1) {
          grid.innerHTML = '<div class="empty"><b>Aucun événement trouvé</b><span>Essayez d\'autres critères de recherche.</span></div>';
        } else {
          grid.insertAdjacentHTML('beforeend', items.map(card).join(''));
        }
        lastPage = d.last_page || 1;
        more.hidden = page >= lastPage;
      })
      .catch(function () {
        if (reset) grid.innerHTML = '<div class="empty"><b>Catalogue indisponible</b><span>Réessayez dans un instant.</span></div>';
      });
  }

  var t;
  function reload() { clearTimeout(t); t = setTimeout(function () { load(true); }, 300); }

  /* catégories → puces */
  api('/categories')
    .then(function (list) {
      var h = '<button type="button" class="chip on" data-c="">Tous</button>';
      (list || []).forEach(function (c) { h += '<button type="button" class="chip" data-c="' + esc(c.id) + '">' + esc(c.name) + '</button>'; });
      $('#chips').innerHTML = h;
    })
    .catch(function () {});

  $('#chips').addEventListener('click', function (e) {
    var b = e.target.closest('.chip'); if (!b) return;
    $('#chips').querySelectorAll('.chip').forEach(function (x) { x.classList.toggle('on', x === b); });
    catId = b.dataset.c || '';
    load(true);
  });
  $('#searchbar').addEventListener('submit', function (e) { e.preventDefault(); load(true); });
  q.addEventListener('input', reload);
  kind.addEventListener('change', reload);
  free.addEventListener('change', reload);
  more.addEventListener('click', function () { page++; load(false); });

  load(true);

  /* ---------- lieux populaires ---------- */
  var vgrid = $('#venues');
  if (vgrid) {
    api('/venues')
      .then(function (list) {
        var shown = (list || []).slice(0, 6);
        vgrid.innerHTML = shown.length ? shown.map(function (v) {
          var pic = v.image ? ' style="background:url(\'' + esc(v.image) + '\') center/cover"' : ' style="background:' + grad(v.name) + '"';
          return '<a class="lcard" href="/lieux/' + esc(v.slug) + '">' +
            '<div class="pic"' + pic + '><span>' + esc(VTYPES[v.type] || 'Lieu') + '</span></div>' +
            '<div class="info"><b style="font-size:16px">' + esc(v.name) + '</b>' +
            '<span class="meta">' + esc(v.city) + (v.capacity ? ' · jusqu\'à ' + v.capacity.toLocaleString('fr-FR') + ' pers.' : '') + '</span>' +
            '</div></a>';
        }).join('') : '<div class="empty"><b>Aucun lieu pour le moment</b><span>Les lieux partenaires apparaîtront ici.</span></div>';
      })
      .catch(function () { vgrid.innerHTML = ''; });
  }

  /* ---------- prochains événements par ville ---------- */
  var cityrow = $('#cityrow');
  if (cityrow) {
    api('/venues/cities')
      .then(function (cities) {
        return Promise.all((cities || []).map(function (c) {
          return api('/events?city=' + encodeURIComponent(c) + '&per_page=1')
            .then(function (d) {
              var e = d.data && d.data[0];
              return e ? '<a class="citycard" href="/events/' + esc(e.id) + '"><b>' + esc(c) + '</b><span>' + esc(e.title) + '</span><em>' + when(e.start_at) + '</em></a>' : null;
            })
            .catch(function () { return null; });
        }));
      })
      .then(function (cards) {
        var html = cards.filter(Boolean).join('');
        cityrow.innerHTML = html || '<div class="empty"><b>Aucun événement à venir</b><span>Revenez bientôt.</span></div>';
      })
      .catch(function () { cityrow.innerHTML = ''; });
  }
})();
