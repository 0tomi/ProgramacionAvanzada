/**
 * /Views/POSTS/app.js
 *
 * Cliente de la vista de publicaciones. Solicita el contenido al endpoint PHP,
 * compone el HTML del post y gestiona acciones de interacción (likes y
 * comentarios). Mantiene el código centrado en tres responsabilidades:
 *  - llamadas a la API (`fetchPost`, `apiToggleLike`, `apiPostComment`)
 *  - renderizado del árbol de comentarios
 *  - manejadores de eventos del usuario
 */

const API_URL = '../../Controlers/PostsApi.php';
const feed = document.getElementById('feed');
const DEFAULT_AVATAR = '../../Resources/profilePictures/defaultProfilePicture.png';

init();

/**
 * Carga inicial del post (utiliza el parámetro ?id=... de la URL).
 */
async function init() {
  const id = new URLSearchParams(window.location.search).get('id');
  if (!id) {
    feed.innerHTML = '<div class="error">Falta el parámetro ?id=...</div>';
    return;
  }

  try {
    const post = await fetchPost(id);
    feed.innerHTML = renderPost(post);
  } catch (err) {
    const message = err instanceof Error ? err.message : String(err);
    feed.innerHTML = `<div class="error">${escapeHtml(message)}</div>`;
  }
}

/**
 * Recupera un post puntual desde la API.
 * Lanza un error si la respuesta no es satisfactoria.
 */
async function fetchPost(id) {
  const res = await fetch(`${API_URL}?action=get&id=${encodeURIComponent(id)}`, {
    credentials: 'same-origin',
  });
  const data = await res.json();
  if (!data.ok || !data.item) {
    throw new Error(data.error || 'Post no encontrado');
  }
  return data.item;
}

/**
 * Devuelve el marcado HTML para la vista completa del post.
 */
function renderPost(post) {
  const likeClasses = `chip ${post.viewer?.liked ? 'liked' : ''}`;
  const avatar = post.author?.avatar_url || DEFAULT_AVATAR;
  const authorName = post.author?.name ? escapeHtml(post.author.name) : 'Anónimo';
  const createdAt = formatDate(post.created_at);
  const media = post.media_url
    ? `<figure class="media"><img class="post-image" src="${escapeHtml(post.media_url)}" alt="Imagen del post"></figure>`
    : '';
  const comments = renderCommentsTree(post.replies || []);
  const commentForm = commentFormTemplate(post.id);

  return `
    <article class="post" data-id="${post.id}">
      <header class="post-header">
        <img class="avatar" src="${escapeHtml(avatar)}" alt="${authorName}">
        <div class="meta">
          <div class="name">${authorName}</div>
          <div class="subline"><time>${createdAt}</time></div>
        </div>
      </header>
      <p class="text">${escapeHtml(post.text)}</p>
      ${media}
      <div class="actions">
        <button class="${likeClasses}" onclick="toggleLike('${post.id}', this)">
          ♥ <span class="like-count">${post.counts?.likes ?? 0}</span>
        </button>
      </div>
      <details open>
        <summary>Comentarios (${post.counts?.replies ?? 0})</summary>
        <div class="comentarios">
          ${comments}
        </div>
        ${commentForm}
      </details>
    </article>
  `;
}

/**
 * Plantilla para el formulario de comentario raíz.
 */
function commentFormTemplate(postId) {
  return `
    <form class="comment-root" onsubmit="event.preventDefault(); return comentar('${postId}', null, this, event)">
      <input name="text" required maxlength="280" placeholder="Escribe un comentario">
      <button class="btn primary">Comentar</button>
    </form>
  `;
}

/**
 * Construye el árbol UL/LI de comentarios. Si el usuario no puede responder,
 * se devuelven los nodos sin botones de respuesta.
 */
function renderCommentsTree(list) {
  if (!Array.isArray(list) || list.length === 0) {
    return '<div class="muted">Sé el primero en comentar</div>';
  }
  const roots = buildTree(list);
  return `<ul class="c-tree">${roots.map((node) => renderCommentNode(node)).join('')}</ul>`;
}

/**
 * Toma una lista plana de comentarios (cada uno con parent_id) y arma el árbol.
 */
function buildTree(list) {
  const byId = new Map();
  list.forEach((c) => byId.set(c.id || cryptoRand(), { ...c, children: [] }));
  const roots = [];
  byId.forEach((node) => {
    if (node.parent_id && byId.has(node.parent_id)) {
      byId.get(node.parent_id).children.push(node);
    } else {
      roots.push(node);
    }
  });
  return roots;
}

/**
 * Renderiza un nodo individual del árbol de comentarios.
 */
function renderCommentNode(node) {
  const children = node.children?.length
    ? `<ul class="c-tree">${node.children.map((child) => renderCommentNode(child)).join('')}</ul>`
    : '';

  return `
    <li class="c-node" data-cid="${node.id}">
      <div class="c-bubble">
        <div class="c-meta"><b>${escapeHtml(node.author || 'Anónimo')}</b> · <span>${formatDate(node.created_at)}</span></div>
        <div class="c-text">${escapeHtml(node.text || '')}</div>
        <div class="c-actions">
          <button type="button" class="btn ghost small" onclick="toggleReplyForm('${node.id}', this)">Responder</button>
        </div>
        <form class="c-reply-form hidden" onsubmit="event.preventDefault(); return comentar(getPostId(this), '${node.id}', this, event)">
          <input name="text" required maxlength="280" placeholder="Responder…">
          <button class="btn">Enviar</button>
        </form>
      </div>
      ${children}
    </li>
  `;
}

/**
 * Alterna el estado de like de la publicación.
 */
async function toggleLike(id, btn) {
  if (btn.hasAttribute('disabled')) return;

  const countEl = btn.querySelector('.like-count');
  const wasLiked = btn.classList.contains('liked');
  const previousCount = parseInt(countEl.textContent, 10);

  // Optimistic UI
  btn.classList.toggle('liked');
  countEl.textContent = wasLiked ? previousCount - 1 : previousCount + 1;

  try {
    const result = await apiToggleLike(id);
    btn.classList.toggle('liked', Boolean(result.liked));
    countEl.textContent = result.like_count;
  } catch (err) {
    btn.classList.toggle('liked', wasLiked);
    countEl.textContent = previousCount;
    alert(err instanceof Error ? err.message : String(err));
  }
}

/**
 * Envía un comentario (raíz o respuesta) y lo inyecta en el DOM.
 */
async function comentar(postId, parentCommentId, form, ev) {
  if (ev) ev.preventDefault();

  const textField = form.querySelector('[name="text"]');
  const text = textField ? textField.value.trim() : '';
  if (!text) return;

  try {
    const comment = await apiPostComment(postId, parentCommentId, text);
    const commentsWrapper = form.closest('details').querySelector('.comentarios');

    if (parentCommentId) {
      const target = commentsWrapper.querySelector(`[data-cid="${parentCommentId}"]`);
      if (target) {
        let subTree = target.querySelector(':scope > ul.c-tree');
        if (!subTree) {
          subTree = document.createElement('ul');
          subTree.className = 'c-tree';
          target.appendChild(subTree);
        }
        subTree.insertAdjacentHTML('afterbegin', renderCommentNode({ ...comment, children: [] }));
      }
      form.classList.add('hidden');
    } else {
      let root = commentsWrapper.querySelector('ul.c-tree');
      if (!root) {
        commentsWrapper.innerHTML = '';
        root = document.createElement('ul');
        root.className = 'c-tree';
        commentsWrapper.appendChild(root);
      }
      root.insertAdjacentHTML('afterbegin', renderCommentNode({ ...comment, children: [] }));
    }

    const summary = form.closest('details').querySelector('summary');
    const match = summary.textContent.match(/\d+/);
    if (match) {
      summary.textContent = `Comentarios (${parseInt(match[0], 10) + 1})`;
    }

    form.reset();
  } catch (err) {
    alert(err instanceof Error ? err.message : String(err));
  }
}

/* ==== Llamadas a la API ==== */

async function apiToggleLike(postId) {
  const res = await fetch(`${API_URL}?action=like`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'same-origin',
    body: JSON.stringify({ post_id: Number(postId) }),
  });
  const data = await res.json();
  if (!res.ok || !data.ok) {
    throw new Error(data.error || 'No se pudo actualizar el like');
  }
  return data;
}

async function apiPostComment(postId, parentCommentId, text) {
  const payload = {
    post_id: Number(postId),
    parent_comment_id: parentCommentId ? Number(parentCommentId) : null,
    text,
  };

  const res = await fetch(`${API_URL}?action=comment`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'same-origin',
    body: JSON.stringify(payload),
  });
  const data = await res.json();
  if (!res.ok || !data.ok) {
    throw new Error(data.error || 'No se pudo enviar el comentario');
  }
  return data.comment;
}

/* ==== Utilidades de UI ==== */

function toggleReplyForm(_commentId, btn) {
  const bubble = btn.closest('.c-bubble');
  const form = bubble.querySelector('.c-reply-form');
  form.classList.toggle('hidden');
}

function getPostId(el) {
  return el.closest('article.post')?.dataset?.id;
}

function escapeHtml(str) {
  return (str || '').replace(/[&<>"']/g, (ch) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
  })[ch]);
}

function formatDate(iso) {
  try {
    return new Date(iso).toLocaleString();
  } catch {
    return '';
  }
}

function cryptoRand() {
  return String(Date.now()) + Math.floor(Math.random() * 10000);
}
