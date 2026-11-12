(() => {
  const $ = (s, r = document) => r.querySelector(s);
  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const T = 'movent_token', U = 'movent_user';
  const auth = {
    get token() { return localStorage.getItem(T); },
    get user() { try { return JSON.parse(localStorage.getItem(U)); } catch (e) { return null; } },
    set(t, u) { localStorage.setItem(T, t); localStorage.setItem(U, JSON.stringify(u)); },
    clear() { localStorage.removeItem(T); localStorage.removeItem(U); },
  };
  const HOME = { ADMIN: '/admin', ORGANIZER: '/organizer', PARTICIPANT: '/account', NATIONAL_AUTHORITY: '/supervision' };
  const LABELS = { DRAFT: 'Brouillon', PENDING: 'En attente', APPROVED: 'Validé', REJECTED: 'Rejeté', CANCELLED: 'Annulé', CONFIRMED: 'Confirmée', FAILED: 'Échouée', EXPIRED: 'Expirée', SUCCESS: 'Réussi', VALID: 'Valide', USED: 'Utilisé', NATIONAL_AUTHORITY: 'Autorité nationale' };
  const KINDS = { INSTITUTION: 'Institution', PRIVATE: 'Privé' };
  const kindBadge = (k) => (k ? `<span class="badge ${k === 'INSTITUTION' ? 'APPROVED' : 'DRAFT'}">${esc(KINDS[k] || k)}</span>` : '');
  const money = (n) => (Number(n) === 0 ? 'Gratuit' : new Intl.NumberFormat('fr-FR').format(Number(n)) + ' FCFA');
  const when = (d) => (d ? new Date(d).toLocaleString('fr-FR', { dateStyle: 'medium', timeStyle: 'short' }) : '');
  const badge = (s) => `<span class="badge ${esc(s)}">${esc(LABELS[s] || s)}</span>`;
  const GR = ['linear-gradient(160deg,#4a3a96,#1c1236)', 'linear-gradient(160deg,#f4a259,#8e2a35)', 'linear-gradient(160deg,#4f7fc0,#1f3a64)', 'linear-gradient(160deg,#d6ae69,#3a2a12)', 'linear-gradient(160deg,#6fb38a,#245a3a)', 'linear-gradient(160deg,#8e2a35,#3a1020)'];
  const grad = (n) => GR[[...String(n)].reduce((a, c) => a + c.charCodeAt(0), 0) % GR.length];
  const debounce = (f, ms) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => f(...a), ms); }; };

  function toast(msg, ok = true) {
    const el = $('#toast'); el.textContent = msg; el.className = 'toast ' + (ok ? 'ok' : 'ko'); el.hidden = false;
    clearTimeout(toast.t); toast.t = setTimeout(() => (el.hidden = true), 4000);
  }
  async function api(path, { method = 'GET', body, raw } = {}) {
    const h = { Accept: 'application/json' };
    if (body) h['Content-Type'] = 'application/json';
    if (auth.token) h.Authorization = 'Bearer ' + auth.token;
    const r = await fetch('/api' + path, { method, headers: h, body: body ? JSON.stringify(body) : undefined });
    if (raw) return r;
    let d = null; try { d = await r.json(); } catch (e) { /* vide */ }
    if (!r.ok) {
      if (r.status === 401 && auth.token) { auth.clear(); location.href = '/login'; }
      const first = d && d.errors ? Object.values(d.errors)[0][0] : null;
      throw new Error((d && d.message && !first ? d.message : first) || 'Erreur ' + r.status);
    }
    return d;
  }
  function need(...roles) {
    const u = auth.user;
    if (!u) { location.href = '/login?next=' + encodeURIComponent(location.pathname); return false; }
    if (roles.length && !roles.includes(u.role)) { location.href = HOME[u.role] || '/'; return false; }
    return true;
  }
  function nav() {
    const u = auth.user;
    $('#nav').innerHTML = `<a class="brand" href="/">MOVENT</a><nav><a href="/events">Catalogue</a>` +
      (u ? `<a href="${HOME[u.role]}">Mon espace</a><a href="#" id="lo">Déconnexion</a>` : `<a href="/login">Connexion</a><a class="btn gold sm" href="/register">S’inscrire</a>`) + '</nav>';
    const lo = $('#lo');
    if (lo) lo.onclick = async (e) => { e.preventDefault(); try { await api('/auth/logout', { method: 'POST' }); } catch (_) { /* ignore */ } auth.clear(); location.href = '/'; };
  }
  function modal(html) {
    const m = document.createElement('div'); m.className = 'modal';
    m.innerHTML = `<div class="box">${html}</div>`; document.body.appendChild(m);
    m.close = () => m.remove(); m.addEventListener('click', (e) => { if (e.target === m) m.close(); });
    return m;
  }
  function tabs(onShow) {
    const bar = $('#tabs');
    bar.onclick = (e) => {
      const b = e.target.closest('button'); if (!b) return;
      bar.querySelectorAll('button').forEach((x) => x.classList.toggle('on', x === b));
      document.querySelectorAll('section[id^="t-"]').forEach((s) => (s.hidden = s.id !== 't-' + b.dataset.t));
      onShow(b.dataset.t);
    };
  }
  const statBox = (items) => items.map(([v, l]) => `<div class="stat"><b>${esc(v)}</b><span>${esc(l)}</span></div>`).join('');

  /* ---------- paiement simulé (réutilisé par détail + espace participant) ---------- */
  function payModal(b, done) {
    const m = modal(`<h2 style="margin:0">Paiement simulé</h2>
      <p class="muted">${esc(b.event_title)} — ${b.quantity} place(s)<br><b style="color:#10172a;font-size:22px">${money(b.total_amount)}</b></p>
      <p class="muted small">Ceci est une simulation : choisissez le résultat à tester. Les places sont bloquées quelques minutes.</p>
      <button class="btn ok" data-o="success">Simuler un paiement réussi</button>
      <button class="btn danger" data-o="failed">Simuler un paiement échoué</button>
      <button class="btn ghost" data-o="">Payer plus tard</button>`);
    m.onclick = async (e) => {
      const btn = e.target.closest('button'); if (!btn) return;
      if (!btn.dataset.o) { m.close(); done && done(); return; }
      m.querySelectorAll('button').forEach((x) => (x.disabled = true));
      try {
        const r = await api(`/bookings/${b.id}/pay`, { method: 'POST', body: { outcome: btn.dataset.o } });
        toast(r.status === 'CONFIRMED' ? 'Paiement réussi, vos billets sont prêts !' : 'Paiement échoué : places libérées.', r.status === 'CONFIRMED');
      } catch (err) { toast(err.message, false); }
      m.close(); done && done();
    };
  }

  const pages = {};

  /* ---------- catalogue ---------- */
  const card = (e) => `<a class="ev" href="/events/${esc(e.id)}"><div class="pic" style="background:${e.image ? `url('${esc(e.image)}') center/cover` : grad(e.category?.name)}">${esc(e.category?.name)}</div>
    <div class="info"><b>${esc(e.title)}</b><span class="muted small">${when(e.start_at)}</span><span class="muted small">${esc(e.location)}</span>
    <span class="price">${money(e.price)}</span><span class="small muted">${e.available_places} place(s) restante(s) ${kindBadge(e.organizer_kind)}</span></div></a>`;
  pages.events = async () => {
    const grid = $('#grid'), more = $('#more'); let page = 1;
    const cats = await api('/categories');
    $('#cat').innerHTML = '<option value="">Toutes les catégories</option>' + cats.map((c) => `<option value="${esc(c.id)}">${esc(c.name)}</option>`).join('');
    async function load(reset) {
      if (reset) { page = 1; grid.innerHTML = ''; }
      const p = new URLSearchParams({ page });
      if ($('#q').value) p.set('q', $('#q').value);
      if ($('#cat').value) p.set('category_id', $('#cat').value);
      if ($('#kind').value) p.set('organizer_kind', $('#kind').value);
      if ($('#free').checked) p.set('free', '1');
      const d = await api('/events?' + p);
      grid.insertAdjacentHTML('beforeend', d.data.map(card).join('') || (reset ? '<p class="muted">Aucun événement trouvé.</p>' : ''));
      more.hidden = page >= d.last_page;
    }
    const reload = debounce(() => load(true).catch((e) => toast(e.message, false)), 300);
    ['q', 'cat', 'kind', 'free'].forEach((i) => $('#' + i).addEventListener('input', reload));
    more.onclick = () => { page++; load(false).catch((e) => toast(e.message, false)); };
    await load(true);
  };

  /* ---------- détail + réservation ---------- */
  pages.event = async () => {
    const id = document.body.dataset.id, box = $('#detail');
    let e;
    try { e = await api('/events/' + id); } catch (err) { box.innerHTML = `<div class="card"><h2>Événement introuvable</h2><p class="muted">${esc(err.message)}</p><a class="btn" href="/events">Retour au catalogue</a></div>`; return; }
    const u = auth.user;
    box.innerHTML = `<div class="detail"><div>
      <div class="hero-pic" style="background:${e.image ? `url('${esc(e.image)}') center/cover` : grad(e.category?.name)}">${esc(e.title)}</div>
      <span class="badge">${esc(e.category?.name)}</span> ${kindBadge(e.organizer_kind)}
      <p>${esc(e.description || 'Aucune description.').replace(/\n/g, '<br>')}</p></div>
      <aside class="card" style="align-self:start;display:grid;gap:12px">
        <div><b>${when(e.start_at)}</b><br><span class="muted small">jusqu’à ${when(e.end_at)}</span></div>
        <div>${esc(e.location)}</div>
        <div style="font:700 26px 'Plus Jakarta Sans'">${money(e.price)}</div>
        <div class="muted small">${e.available_places} / ${e.capacity} places disponibles</div>
        ${e.available_places > 0 ? `<label>Nombre de places<input id="qty" type="number" min="1" max="${Math.min(10, e.available_places)}" value="1"></label>
          <button id="book" class="btn gold block">${u ? 'Réserver' : 'Se connecter pour réserver'}</button>` : '<span class="badge CANCELLED">Complet</span>'}
      </aside></div>`;
    const btn = $('#book'); if (!btn) return;
    btn.onclick = async () => {
      if (!auth.user) { location.href = '/login?next=' + encodeURIComponent(location.pathname); return; }
      btn.disabled = true;
      try {
        const b = await api('/bookings', { method: 'POST', body: { event_id: e.id, quantity: Number($('#qty').value) } });
        if (b.status === 'CONFIRMED') { toast('Réservation confirmée ! Vos billets sont dans votre espace.'); setTimeout(() => (location.href = '/account'), 1200); }
        else payModal(b, () => (location.href = '/account'));
      } catch (err) { toast(err.message, false); btn.disabled = false; }
    };
  };

  /* ---------- connexion / inscription ---------- */
  function authForm(path) {
    $('#form').onsubmit = async (ev) => {
      ev.preventDefault();
      const body = Object.fromEntries(new FormData(ev.target));
      if (!body.phone) delete body.phone;
      try {
        const r = await api(path, { method: 'POST', body });
        auth.set(r.token, r.user);
        const next = new URLSearchParams(location.search).get('next');
        location.href = next && next.startsWith('/') ? next : HOME[r.user.role];
      } catch (e) { toast(e.message, false); }
    };
  }
  pages.login = async () => authForm('/auth/login');
  pages.register = async () => authForm('/auth/register');

  /* ---------- espace participant ---------- */
  async function dl(id, ref) {
    const r = await api(`/tickets/${id}/download`, { raw: true });
    if (!r.ok) return toast('Téléchargement impossible.', false);
    const a = document.createElement('a'); a.href = URL.createObjectURL(await r.blob()); a.download = ref + '.pdf'; a.click(); URL.revokeObjectURL(a.href);
  }
  pages.account = async () => {
    if (!need('PARTICIPANT')) return;
    const bk = $('#t-bookings'), tk = $('#t-tickets'), pf = $('#t-profile');
    async function loadBookings() {
      const list = await api('/bookings');
      bk.innerHTML = list.length ? `<div class="list">${list.map((b) => `<div class="item"><div><b>${esc(b.event_title)}</b> ${badge(b.status)}<br>
        <span class="muted small">${esc(b.reference)} · ${b.quantity} place(s) · ${money(b.total_amount)} · ${when(b.event_start_at)}</span></div>
        <div class="acts">${b.status === 'PENDING' ? `<button class="btn sm ok" data-pay="${esc(b.id)}">Payer</button>` : ''}
        ${['PENDING', 'CONFIRMED'].includes(b.status) ? `<button class="btn sm ghost" data-cancel="${esc(b.id)}">Annuler</button>` : ''}</div></div>`).join('')}</div>` : '<p class="muted">Aucune réservation. <a href="/events">Découvrir les événements</a></p>';
      bk._list = list;
    }
    async function loadTickets() {
      const list = await api('/tickets');
      tk.innerHTML = list.length ? `<div class="list">${list.map((t) => `<div class="item"><div><b>${esc(t.booking.event_title)}</b> ${badge(t.status)}<br>
        <span class="muted small">${esc(t.ticket_reference)} · ${when(t.booking.event_start_at)}</span><div class="qr">QR : ${esc(t.qr_token)}</div></div>
        <div class="acts">${t.status === 'VALID' ? `<button class="btn sm" data-dl="${esc(t.id)}" data-ref="${esc(t.ticket_reference)}">Télécharger le PDF</button>` : ''}</div></div>`).join('')}</div>` : '<p class="muted">Aucun billet pour le moment.</p>';
    }
    bk.onclick = async (e) => {
      const p = e.target.closest('[data-pay]'), c = e.target.closest('[data-cancel]');
      if (p) payModal(bk._list.find((b) => b.id === p.dataset.pay), loadBookings);
      if (c && confirm('Annuler cette réservation ?')) { try { await api(`/bookings/${c.dataset.cancel}/cancel`, { method: 'POST' }); toast('Réservation annulée.'); loadBookings(); } catch (err) { toast(err.message, false); } }
    };
    tk.onclick = (e) => { const d = e.target.closest('[data-dl]'); if (d) dl(d.dataset.dl, d.dataset.ref); };
    const me = await api('/me');
    pf.innerHTML = `<form id="pform" class="card narrow" style="margin:0"><div class="row2"><label>Prénom<input name="first_name" value="${esc(me.first_name)}" required></label><label>Nom<input name="last_name" value="${esc(me.last_name)}" required></label></div>
      <label>E-mail<input value="${esc(me.email)}" disabled></label><label>Téléphone<input name="phone" value="${esc(me.phone || '')}"></label>
      <label>Nouveau mot de passe (facultatif)<input name="password" type="password" minlength="8"></label><button class="btn gold">Enregistrer</button></form>`;
    $('#pform').onsubmit = async (ev) => {
      ev.preventDefault();
      try { const u = await api('/me', { method: 'PUT', body: Object.fromEntries(new FormData(ev.target)) }); auth.set(auth.token, { ...auth.user, ...u }); toast('Profil mis à jour.'); nav(); } catch (err) { toast(err.message, false); }
    };
    tabs((t) => { if (t === 'bookings') loadBookings(); if (t === 'tickets') loadTickets(); });
    await loadBookings();
  };

  /* ---------- espace organisateur ---------- */
  const toLocal = (iso) => { const d = new Date(iso); d.setMinutes(d.getMinutes() - d.getTimezoneOffset()); return d.toISOString().slice(0, 16); };
  const bookingsTable = (rows) => `<div class="scroll"><table><tr><th>Réf.</th><th>Événement</th><th>Participant</th><th>Qté</th><th>Montant</th><th>Statut</th><th>Date</th></tr>${rows.map((b) => `<tr><td>${esc(b.reference)}</td><td>${esc(b.event_title)}</td><td>${esc(b.participant_name)}</td><td>${b.quantity}</td><td>${money(b.total_amount)}</td><td>${badge(b.status)}</td><td>${when(b.created_at)}</td></tr>`).join('') || '<tr><td colspan="7" class="muted">Aucune réservation.</td></tr>'}</table></div>`;
  pages.organizer = async () => {
    if (!need('ORGANIZER', 'ADMIN')) return;
    const ev = $('#t-events'), bk = $('#t-bookings'); let cats = [];
    async function stats() {
      const [a, b] = await Promise.all([api('/event-stats'), api('/booking-stats')]);
      $('#stats').innerHTML = statBox([[a.events_total, 'Événements'], [a.events_by_status.APPROVED || 0, 'Validés'], [a.events_by_status.PENDING || 0, 'En attente'], [b.tickets_sold, 'Billets vendus'], [money(b.revenue), 'Revenus']]);
    }
    async function load() {
      const list = await api('/my-events');
      ev.innerHTML = list.length ? `<div class="list">${list.map((e) => `<div class="item"><div><b>${esc(e.title)}</b> ${badge(e.status)}<br><span class="muted small">${esc(e.category?.name)} · ${when(e.start_at)} · ${money(e.price)} · ${e.capacity - e.available_places}/${e.capacity} vendues</span>
        ${e.rejection_reason && e.status === 'REJECTED' ? `<div class="small" style="color:#a02b2b">Motif du rejet : ${esc(e.rejection_reason)}</div>` : ''}</div>
        <div class="acts">${['DRAFT', 'REJECTED'].includes(e.status) ? `<button class="btn sm ghost" data-edit="${esc(e.id)}">Modifier</button><button class="btn sm ok" data-submit="${esc(e.id)}">Soumettre</button><button class="btn sm danger" data-del="${esc(e.id)}">Supprimer</button>` : ''}
        ${['PENDING', 'APPROVED'].includes(e.status) ? `<button class="btn sm ghost" data-cancel="${esc(e.id)}">Annuler</button>` : ''}</div></div>`).join('')}</div>` : '<p class="muted">Aucun événement. Créez le premier !</p>';
      ev._list = list;
    }
    function form(e) {
      const m = modal(`<h2 style="margin:0">${e ? 'Modifier' : 'Nouvel'} événement</h2><form id="eform" style="display:grid;gap:12px">
        <label>Titre<input name="title" required value="${esc(e?.title)}"></label>
        <label>Catégorie<select name="category_id" required>${cats.map((c) => `<option value="${esc(c.id)}" ${e?.category_id === c.id ? 'selected' : ''}>${esc(c.name)}</option>`).join('')}</select></label>
        <label>Description<textarea name="description">${esc(e?.description)}</textarea></label>
        <label>Lieu<input name="location" required value="${esc(e?.location)}"></label>
        <label>Image (URL, facultatif)<input name="image" type="url" value="${esc(e?.image)}"></label>
        <div class="row2"><label>Début<input name="start_at" type="datetime-local" required value="${e ? toLocal(e.start_at) : ''}"></label><label>Fin<input name="end_at" type="datetime-local" required value="${e ? toLocal(e.end_at) : ''}"></label></div>
        <label>Type d'organisateur<select name="organizer_kind"><option value="PRIVATE" ${(!e || e.organizer_kind !== 'INSTITUTION') ? 'selected' : ''}>Privé (entreprise, association, agence, indépendant)</option><option value="INSTITUTION" ${e?.organizer_kind === 'INSTITUTION' ? 'selected' : ''}>Institution publique (ministère, administration, collectivité)</option></select></label>
        <div class="row2"><label>Capacité<input name="capacity" type="number" min="1" required value="${esc(e?.capacity)}"></label><label>Prix (FCFA, 0 = gratuit)<input name="price" type="number" min="0" step="1" required value="${esc(e ? Math.round(e.price) : 0)}"></label></div>
        <button class="btn gold">Enregistrer en brouillon</button></form>`);
      $('#eform', m).onsubmit = async (ev) => {
        ev.preventDefault();
        const b = Object.fromEntries(new FormData(ev.target));
        b.start_at = new Date(b.start_at).toISOString(); b.end_at = new Date(b.end_at).toISOString();
        if (!b.image) delete b.image;
        try { await api(e ? '/events/' + e.id : '/events', { method: e ? 'PUT' : 'POST', body: b }); m.close(); toast('Événement enregistré.'); load(); stats(); } catch (err) { toast(err.message, false); }
      };
    }
    ev.onclick = async (x) => {
      const b = x.target.closest('button'); if (!b) return;
      try {
        if (b.dataset.edit) return form(ev._list.find((e) => e.id === b.dataset.edit));
        if (b.dataset.submit) { await api(`/events/${b.dataset.submit}/submit`, { method: 'POST' }); toast('Soumis à validation.'); }
        if (b.dataset.cancel && confirm('Annuler cet événement ?')) { await api(`/events/${b.dataset.cancel}/cancel`, { method: 'POST' }); toast('Événement annulé.'); }
        if (b.dataset.del && confirm('Supprimer cet événement ?')) { await api('/events/' + b.dataset.del, { method: 'DELETE' }); toast('Supprimé.'); }
        load(); stats();
      } catch (err) { toast(err.message, false); }
    };
    cats = await api('/categories');
    $('#new').onclick = () => form(null);
    tabs(async (t) => { if (t === 'bookings') bk.innerHTML = bookingsTable((await api('/organizer/bookings')).data); });
    await Promise.all([load(), stats()]);
  };

  /* ---------- administration ---------- */
  pages.admin = async () => {
    if (!need('ADMIN')) return;
    const mod = $('#t-moderation'), evs = $('#t-events'), usr = $('#t-users'), bk = $('#t-bookings');
    async function stats() {
      const [a, b, u] = await Promise.all([api('/event-stats'), api('/booking-stats'), api('/users')]);
      $('#stats').innerHTML = statBox([[u.total, 'Utilisateurs'], [a.events_total, 'Événements'], [a.events_by_status.PENDING || 0, 'À modérer'], [b.bookings_total, 'Réservations'], [b.tickets_sold, 'Billets vendus'], [money(b.revenue), 'Revenus']]);
    }
    async function moderation() {
      const d = (await api('/admin/events?status=PENDING')).data;
      mod.innerHTML = d.length ? `<div class="list">${d.map((e) => `<div class="item"><div><b>${esc(e.title)}</b> ${badge(e.status)}<br><span class="muted small">${esc(e.category?.name)} · ${when(e.start_at)} · ${esc(e.location)} · ${money(e.price)} · ${e.capacity} places</span><div class="small">${esc(e.description)}</div></div>
        <div class="acts"><button class="btn sm ok" data-ap="${esc(e.id)}">Valider</button><button class="btn sm danger" data-rj="${esc(e.id)}">Rejeter</button></div></div>`).join('')}</div>` : '<p class="muted">Aucun événement en attente de validation.</p>';
    }
    mod.onclick = async (x) => {
      const b = x.target.closest('button'); if (!b) return;
      try {
        if (b.dataset.ap) { await api(`/events/${b.dataset.ap}/approve`, { method: 'POST' }); toast('Événement validé et publié.'); }
        if (b.dataset.rj) { const reason = prompt('Motif du rejet (obligatoire) :'); if (!reason) return; await api(`/events/${b.dataset.rj}/reject`, { method: 'POST', body: { reason } }); toast('Événement rejeté.'); }
        moderation(); stats();
      } catch (err) { toast(err.message, false); }
    };
    async function events() {
      const d = (await api('/admin/events')).data;
      evs.innerHTML = `<div class="scroll"><table><tr><th>Titre</th><th>Catégorie</th><th>Date</th><th>Prix</th><th>Places</th><th>Statut</th></tr>${d.map((e) => `<tr><td>${esc(e.title)}</td><td>${esc(e.category?.name)}</td><td>${when(e.start_at)}</td><td>${money(e.price)}</td><td>${e.available_places}/${e.capacity}</td><td>${badge(e.status)}</td></tr>`).join('')}</table></div>`;
    }
    async function users() {
      const d = (await api('/users')).data, me = auth.user.id;
      usr.innerHTML = `<div class="scroll"><table><tr><th>Nom</th><th>E-mail</th><th>Rôle</th><th>Compte</th></tr>${d.map((u) => `<tr><td>${esc(u.first_name)} ${esc(u.last_name)}</td><td>${esc(u.email)}</td>
        <td><select data-role="${esc(u.id)}" ${u.id === me ? 'disabled' : ''}>${['ADMIN', 'NATIONAL_AUTHORITY', 'ORGANIZER', 'PARTICIPANT'].map((r) => `<option ${u.role === r ? 'selected' : ''}>${r}</option>`).join('')}</select></td>
        <td><button class="btn sm ${u.is_active ? 'ghost' : 'ok'}" data-st="${esc(u.id)}" data-v="${u.is_active ? 0 : 1}" ${u.id === me ? 'disabled' : ''}>${u.is_active ? 'Désactiver' : 'Activer'}</button></td></tr>`).join('')}</table></div>`;
    }
    usr.onchange = async (x) => { const s = x.target.closest('[data-role]'); if (!s) return; try { await api(`/users/${s.dataset.role}/role`, { method: 'PATCH', body: { role: s.value } }); toast('Rôle modifié.'); } catch (err) { toast(err.message, false); users(); } };
    usr.onclick = async (x) => { const b = x.target.closest('[data-st]'); if (!b) return; try { await api(`/users/${b.dataset.st}/status`, { method: 'PATCH', body: { is_active: b.dataset.v === '1' } }); toast('Statut modifié.'); users(); } catch (err) { toast(err.message, false); } };
    tabs(async (t) => {
      if (t === 'moderation') moderation(); if (t === 'events') events(); if (t === 'users') users();
      if (t === 'bookings') bk.innerHTML = bookingsTable((await api('/organizer/bookings')).data);
    });
    await Promise.all([moderation(), stats()]);
  };

  /* ---------- supervision nationale (lecture seule) ---------- */
  pages.supervision = async () => {
    if (!need('NATIONAL_AUTHORITY')) return;
    const ev = $('#t-events'), us = $('#t-users'), bk = $('#t-bookings');
    async function stats() {
      const [a, b, u] = await Promise.all([api('/event-stats'), api('/booking-stats'), api('/users')]);
      $('#stats').innerHTML = statBox([[u.total, 'Utilisateurs'], [a.events_total, 'Événements'], [a.events_by_status.PENDING || 0, 'En attente de validation'], [b.bookings_total, 'Réservations'], [b.tickets_sold, 'Billets émis'], [money(b.revenue), 'Revenus totaux']]);
    }
    async function events() {
      const d = (await api('/admin/events')).data;
      ev.innerHTML = `<div class="scroll"><table><tr><th>Titre</th><th>Catégorie</th><th>Type</th><th>Date</th><th>Prix</th><th>Places</th><th>Statut</th></tr>${d.map((e) => `<tr><td>${esc(e.title)}</td><td>${esc(e.category?.name)}</td><td>${esc(KINDS[e.organizer_kind] || '—')}</td><td>${when(e.start_at)}</td><td>${money(e.price)}</td><td>${e.available_places}/${e.capacity}</td><td>${badge(e.status)}</td></tr>`).join('')}</table></div>`;
    }
    async function users() {
      const d = (await api('/users')).data;
      us.innerHTML = `<div class="scroll"><table><tr><th>Nom</th><th>E-mail</th><th>Rôle</th><th>Compte</th></tr>${d.map((u) => `<tr><td>${esc(u.first_name)} ${esc(u.last_name)}</td><td>${esc(u.email)}</td><td>${esc(LABELS[u.role] || u.role)}</td><td>${u.is_active ? 'Actif' : 'Désactivé'}</td></tr>`).join('')}</table></div>`;
    }
    tabs(async (t) => { if (t === 'events') events(); if (t === 'users') users(); if (t === 'bookings') bk.innerHTML = bookingsTable((await api('/organizer/bookings')).data); });
    await Promise.all([events(), stats()]);
  };

  document.addEventListener('DOMContentLoaded', () => {
    nav();
    const p = pages[document.body.dataset.page];
    if (p) p().catch((e) => toast(e.message, false));
  });
})();
