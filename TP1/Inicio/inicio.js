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
