// Inicio/inicio.js

// Ruta base hacia la API del módulo POSTS (desde /Inicio)
const API_BASE = '../POSTS/api.php';

document.addEventListener('DOMContentLoaded', () => {
  marcarLikesAlCargar();
  wireEventosClick();

  // Hook del formulario de crear post (sin redirigir)
  const form = document.getElementById('createPostForm');
  if (form) form.addEventListener('submit', onCreatePostSubmit);
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
    // Evitar que el composer dispare overlay/likes
    if (e.target.closest('.composer')) return;

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

/** Submit del formulario de crear post (sin redirección) */
async function onCreatePostSubmit(e) {
  e.preventDefault();
  e.stopPropagation();

  const form = e.currentTarget;
  const fd = new FormData(form);

  const text = (fd.get('text') || '').toString().trim();
  if (text.length === 0 || text.length > 280) {
    alert('El texto es requerido (1..280 caracteres).');
    return;
  }

  try {
    const res = await fetch(`${API_BASE}?action=create`, {
      method: 'POST',
      body: fd,
      credentials: 'same-origin'
    });
    const data = await res.json();
    if (!data.ok || !data.item) throw new Error(data.error || 'No se pudo crear el post');

    // Insertar en el feed SIN redirigir
    insertarPostEnFeed(data.item);

    // limpiar form
    form.reset();
  } catch (err) {
    alert(String(err.message || err));
  }
}

/** Inserta el post recién creado al principio del feed (sin @handle) */
function insertarPostEnFeed(p) {
  const id = p.id;
  const name = p.author?.name || 'Anónimo';
  const avatarL = (name[0] || 'U').toUpperCase(); // inicial desde el nombre
  const tsHuman = new Date(p.created_at).toLocaleString();
  const media = p.media_url ? `<figure class="media"><img src="${escapeHtml(p.media_url)}" alt="Imagen del post"></figure>` : "";

  const html = `
    <article class="post" data-id="${escapeHtml(id)}">
      <a class="post-overlay" href="../POSTS/?id=${encodeURIComponent(id)}" aria-label="Ver post"></a>
      <header class="post-header">
        <div class="avatar">${escapeHtml(avatarL)}</div>
        <div class="meta">
          <div class="name">${escapeHtml(name)}</div>
          <div class="subline">
            <time datetime="${escapeHtml(p.created_at)}">${escapeHtml(tsHuman)}</time>
          </div>
        </div>
      </header>
      <p class="text">${escapeHtml(p.text)}</p>
      ${media}
      <div class="actions">
        <button type="button" class="chip like" data-id="${escapeHtml(id)}">
          ♥ <span class="count">${Number(p.counts?.likes ?? 0)}</span>
        </button>
      </div>
    </article>
  `;

  const feed = document.getElementById('feed');
  feed.insertAdjacentHTML('afterbegin', html);
}

/* Utilidad para evitar inyección de HTML */
function escapeHtml(s) {
  return (s ?? '').toString().replace(/[&<>"']/g, ch => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  }[ch]));
}
