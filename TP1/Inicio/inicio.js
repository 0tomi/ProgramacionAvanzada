// Inicio/inicio.js

// Ruta base hacia la API del módulo POSTS (desde /Inicio)
const API_BASE = '../POSTS/api.php';

document.addEventListener('DOMContentLoaded', () => {
  marcarLikesAlCargar();
  wireEventosClick();
});

/** Marca en rojo los posts likeados en esta sesión */
async function marcarLikesAlCargar() {
  try {
    const res = await fetch(`${API_BASE}?action=liked_ids`, { credentials: 'same-origin' });
    const data = await res.json();
    if (!data.ok || !Array.isArray(data.ids)) return;

    const liked = new Set(data.ids.map(String));
    document.querySelectorAll('.chip.like[data-id]').forEach(btn => {
      if (liked.has(btn.getAttribute('data-id'))) btn.classList.add('liked');
    });
  } catch (_) {
    // silencioso
  }
}

/** Delegación global de clicks para like + overlay */
function wireEventosClick() {
  document.addEventListener('click', async (e) => {
    // 1) Like en el feed (♥)
    const likeBtn = e.target.closest('.chip.like');
    if (likeBtn) {
      e.preventDefault();
      e.stopPropagation();
      await manejarLike(likeBtn);
      return;
    }

    // 2) Navegación por overlay (toda la tarjeta)
    const card = e.target.closest('.post');
    if (!card) return;
    const overlay = card.querySelector('.post-overlay');
    if (!overlay || !overlay.getAttribute('href')) return;

    if (!e.target.closest('.post-overlay')) {
      window.location.href = overlay.href;
    }
  }, { passive: true });
}

/** Envía like/unlike a la API con UI optimista */
async function manejarLike(likeBtn) {
  const postId = likeBtn.getAttribute('data-id');
  if (!postId) return;

  const countEl = likeBtn.querySelector('.count');
  const prev = parseInt(countEl.textContent, 10) || 0;

  // Optimistic UI
  likeBtn.classList.toggle('liked');
  const optimistic = likeBtn.classList.contains('liked') ? prev + 1 : prev - 1;
  countEl.textContent = optimistic;

  try {
    const res = await fetch(`${API_BASE}?action=like`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({ post_id: postId })
    });
    const data = await res.json();
    if (!data.ok) throw new Error(data.error || 'Error al likear');

    // sincronizar con el valor real que devolvió la API
    likeBtn.classList.toggle('liked', !!data.liked);
    countEl.textContent = data.like_count;
  } catch (err) {
    // rollback si hubo error
    likeBtn.classList.toggle('liked');
    countEl.textContent = prev;
    alert(String(err.message || err));
  }
}

document.getElementById('createPostForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const form = e.currentTarget;
  const fd = new FormData(form);

  const text = (fd.get('text') || '').toString().trim();
  if (text.length === 0 || text.length > 280) {
    alert('El texto es requerido (1..280 caracteres).');
    return;
  }

  try {
    const res = await fetch('../POSTS/api.php?action=create', {
      method: 'POST',
      body: fd,
      credentials: 'same-origin'
    });
    const data = await res.json();
    if (!data.ok || !data.item) throw new Error(data.error || 'No se pudo crear el post');

    // 👉 Renderizamos el post en el inicio (sin redirigir)
    const p = data.item;
    const id = p.id;
    const name = p.author?.name || 'Anónimo';
    const handle = p.author?.handle || 'anon';
    const avatarL = (handle[0] || 'U').toUpperCase();
    const tsHuman = new Date(p.created_at).toLocaleString();
    const media = p.media_url ? `<figure class="media"><img src="${p.media_url}" alt="Imagen del post"></figure>` : "";

    const html = `
      <article class="post" data-id="${id}">
        <a class="post-overlay" href="../POSTS/?id=${encodeURIComponent(id)}" aria-label="Ver post"></a>
        <header class="post-header">
          <div class="avatar">${avatarL}</div>
          <div class="meta">
            <div class="name">${name}</div>
            <div class="subline">
              <span class="handle">@${handle}</span>
              <span class="dot">·</span>
              <time datetime="${p.created_at}">${tsHuman}</time>
            </div>
          </div>
        </header>
        <p class="text">${p.text}</p>
        ${media}
        <div class="actions">
          <button type="button" class="chip like" data-id="${id}">
            ♥ <span class="count">${p.counts.likes}</span>
          </button>
        </div>
      </article>
    `;

    // Insertar al principio del feed
    const feed = document.getElementById('feed');
    feed.insertAdjacentHTML('afterbegin', html);

    // Resetear form
    form.reset();

  } catch (err) {
    alert(String(err.message || err));
  }
});
