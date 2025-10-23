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
const ASSET_BASE = '../../';
const feed = document.getElementById('feed');
const DEFAULT_AVATAR = resolveAssetUrl('Resources/profilePictures/defaultProfilePicture.png');

init();
document.addEventListener('click', onGlobalClick);
document.addEventListener('keydown', onGlobalKeydown);
document.addEventListener('change', onGlobalChange);

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
  const postId = String(post.id ?? '');
  const likeClasses = `chip ${post.viewer?.liked ? 'liked' : ''}`;
  const avatarUrl = escapeHtml(resolveAssetUrl(post.author?.avatar_url, DEFAULT_AVATAR));
  const authorName = post.author?.name ? escapeHtml(post.author.name) : 'Anónimo';
  const authorId = post.author?.id ? String(post.author.id) : null;
  let profileHref = null;
  if (authorId) {
    profileHref = `../perfil.php?id=${encodeURIComponent(authorId)}`;
  }
  const safeProfileHref = profileHref ? escapeHtml(profileHref) : null;
  const createdAt = formatDate(post.created_at);
  const mediaUrl = resolveAssetUrl(post.media_url);
  const safeMedia = escapeHtml(mediaUrl);
  const media = mediaUrl
    ? `<figure class="media" data-action="open-media" data-media="${safeMedia}" tabindex="0" role="button">
        <img class="post-image" src="${safeMedia}" alt="Imagen del post">
      </figure>`
    : '';
  const comments = renderCommentsTree(post.replies || []);
  const commentForm = commentFormTemplate(post.id);
  const canDelete = Boolean(post.viewer?.can_delete);
  const menu = canDelete
    ? `<div class="post-menu">
        <button type="button" class="post-menu__toggle" aria-haspopup="true" aria-expanded="false">⋮</button>
        <div class="post-menu__dropdown" role="menu">
          <button type="button" class="post-menu__item post-menu__item--danger" role="menuitem" data-action="delete-post" data-id="${escapeHtml(postId)}">
            Eliminar post
          </button>
        </div>
      </div>`
    : '';

  return `
    <article class="post" data-id="${escapeHtml(postId)}">
      ${menu}
      <header class="post-header">
        ${safeProfileHref
          ? `<a class="avatar-link" href="${safeProfileHref}"><img class="avatar" src="${avatarUrl}" alt="${authorName}"></a>`
          : `<img class="avatar" src="${avatarUrl}" alt="${authorName}">`}
        <div class="meta">
          <div class="name">
            ${safeProfileHref
              ? `<a class="name-link" href="${safeProfileHref}">${authorName}</a>`
              : authorName}
          </div>
          <div class="subline"><time>${createdAt}</time></div>
        </div>
      </header>
      <p class="text">${escapeHtml(post.text)}</p>
      ${media}
      <div class="actions">
        <button class="${likeClasses}" onclick="toggleLike('${postId}', this)">
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
  const safeId = escapeHtml(String(postId));
  return `
    <form class="comment-form comment-root" onsubmit="event.preventDefault(); return comentar('${safeId}', null, this, event)" enctype="multipart/form-data" novalidate>
      <input name="text" maxlength="280" placeholder="Escribe un comentario" autocomplete="off">
      <div class="comment-form__actions">
        <div class="comment-form__media">
          <button type="button" class="btn ghost small" data-action="comment-add-image">Agregar imagen</button>
          <button type="button" class="comment-form__remove-image" data-action="comment-remove-image" hidden>Quitar</button>
          <input type="file" name="image" accept="image/*" data-comment-image hidden>
        </div>
        <button type="submit" class="btn primary">Comentar</button>
      </div>
      <div class="comment-form__preview" hidden></div>
    </form>
  `;
}

function replyFormTemplate(commentId) {
  const safeId = escapeHtml(String(commentId));
  return `
    <form class="comment-form c-reply-form hidden" onsubmit="event.preventDefault(); return comentar(getPostId(this), '${safeId}', this, event)" enctype="multipart/form-data" novalidate>
      <input name="text" maxlength="280" placeholder="Responder…" autocomplete="off">
      <div class="comment-form__actions">
        <div class="comment-form__media">
          <button type="button" class="btn ghost small" data-action="comment-add-image">Agregar imagen</button>
          <button type="button" class="comment-form__remove-image" data-action="comment-remove-image" hidden>Quitar</button>
          <input type="file" name="image" accept="image/*" data-comment-image hidden>
        </div>
        <button type="submit" class="btn small">Enviar</button>
      </div>
      <div class="comment-form__preview" hidden></div>
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
  const id = String(node.id ?? '');
  const safeId = escapeHtml(id);
  const author = escapeHtml(node.author || 'Anónimo');
  const created = escapeHtml(formatDate(node.created_at));
  const text = escapeHtml(node.text || '');
  const textHtml = text !== '' ? `<div class="c-text">${text}</div>` : '';

  const mediaUrlRaw = node.media_url ? String(node.media_url) : '';
  const mediaUrl = resolveAssetUrl(mediaUrlRaw);
  const safeMedia = escapeHtml(mediaUrl);
  const mediaHtml = mediaUrl
    ? `<figure class="c-media" data-action="open-media" data-media="${safeMedia}" tabindex="0" role="button">
        <img src="${safeMedia}" alt="Imagen del comentario">
      </figure>`
    : '';

  const likeCount = Number(node.counts?.likes ?? 0);
  const liked = Boolean(node.viewer?.liked);
  const likeBtn = `
    <button type="button" class="chip like comment-like${liked ? ' liked' : ''}" onclick="toggleLike('${safeId}', this)">
      ♥ <span class="like-count">${likeCount}</span>
    </button>
  `;

  const canDelete = Boolean(node.viewer?.can_delete);
  const menu = canDelete
    ? `<div class="comment-menu">
        <button type="button" class="comment-menu__toggle" aria-haspopup="true" aria-expanded="false">⋮</button>
        <div class="comment-menu__dropdown" role="menu">
          <button type="button" class="comment-menu__item comment-menu__item--danger" role="menuitem" data-action="delete-comment" data-id="${safeId}">
            Eliminar comentario
          </button>
        </div>
      </div>`
    : '';

  const children = node.children?.length
    ? `<ul class="c-tree">${node.children.map((child) => renderCommentNode(child)).join('')}</ul>`
    : '';

  return `
    <li class="c-node" data-cid="${safeId}">
      <div class="c-bubble">
        ${menu}
        <div class="c-meta"><b>${author}</b> · <span>${created}</span></div>
        ${textHtml}
        ${mediaHtml}
        <div class="c-actions">
          ${likeBtn}
          <button type="button" class="btn ghost small" onclick="toggleReplyForm('${safeId}', this)">Responder</button>
        </div>
        ${replyFormTemplate(id)}
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
  const hasImage = commentFormHasImage(form);

  if (!text && !hasImage) {
    alert('Escribí un comentario o adjuntá una imagen.');
    if (textField) textField.focus();
    return;
  }

  if (text.length > 280) {
    alert('El comentario puede tener hasta 280 caracteres.');
    if (textField) textField.focus();
    return;
  }

  const fd = new FormData();
  fd.append('post_id', Number(postId));
  fd.append('text', text);
  if (parentCommentId) {
    fd.append('parent_comment_id', Number(parentCommentId));
  }

  const imageInput = form.querySelector('input[type="file"][data-comment-image]');
  if (imageInput && imageInput.files && imageInput.files[0]) {
    fd.append('image', imageInput.files[0]);
  }

  const submitBtn = form.querySelector('button[type="submit"]');
  if (submitBtn) submitBtn.disabled = true;

  try {
    const comment = await apiPostComment(fd);
    const commentsWrapper = form.closest('details').querySelector('.comentarios');
    const commentHtml = renderCommentNode({ ...comment, children: [] });

    if (parentCommentId) {
      const target = commentsWrapper.querySelector(`[data-cid="${cssEscape(parentCommentId)}"]`);
      if (target) {
        let subTree = target.querySelector(':scope > ul.c-tree');
        if (!subTree) {
          subTree = document.createElement('ul');
          subTree.className = 'c-tree';
          target.appendChild(subTree);
        }
        subTree.insertAdjacentHTML('afterbegin', commentHtml);
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
      root.insertAdjacentHTML('afterbegin', commentHtml);
    }

    updateCommentCounter(form.closest('details'), 1);
    clearCommentForm(form);
  } catch (err) {
    alert(err instanceof Error ? err.message : String(err));
  } finally {
    if (submitBtn) submitBtn.disabled = false;
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

async function apiPostComment(formData) {
  const res = await fetch(`${API_URL}?action=comment`, {
    method: 'POST',
    credentials: 'same-origin',
    body: formData,
  });
  const data = await res.json();
  if (!res.ok || !data.ok) {
    throw new Error(data.error || 'No se pudo enviar el comentario');
  }
  return data.comment;
}

async function apiDeleteComment(commentId) {
  const res = await fetch(`${API_URL}?action=delete_comment`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'same-origin',
    body: JSON.stringify({ comment_id: Number(commentId) }),
  });
  const data = await res.json();
  if (!res.ok || !data.ok) {
    throw new Error(data.error || 'No se pudo eliminar el comentario');
  }
  return data;
}

async function apiDeletePost(postId) {
  const res = await fetch(`${API_URL}?action=delete`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'same-origin',
    body: JSON.stringify({ post_id: Number(postId) }),
  });
  const data = await res.json();
  if (!res.ok || !data.ok) {
    throw new Error(data.error || 'No se pudo eliminar el post');
  }
  return data;
}

/* ==== Utilidades de UI ==== */

function toggleReplyForm(_commentId, btn) {
  const bubble = btn.closest('.c-bubble');
  const form = bubble.querySelector('.c-reply-form');
  if (!form) return;
  form.classList.toggle('hidden');
  if (!form.classList.contains('hidden')) {
    const input = form.querySelector('[name="text"]');
    if (input) input.focus();
  } else {
    clearCommentForm(form);
  }
}

function getPostId(el) {
  return el.closest('article.post')?.dataset?.id;
}

function resolveAssetUrl(input, fallback = '') {
  const raw = typeof input === 'string' ? input.trim() : '';
  if (raw === '') {
    return fallback;
  }
  if (/^(?:https?:)?\/\//i.test(raw) || raw.startsWith('/') || raw.startsWith('data:')) {
    return raw;
  }

  const sanitized = raw.replace(/^(\.\/)+/, '').replace(/^(\.\.\/)+/, '');
  return `${ASSET_BASE}${sanitized}`;
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

function onGlobalClick(event) {
  const target = event.target;

  const addImageBtn = target.closest('[data-action="comment-add-image"]');
  if (addImageBtn) {
    event.preventDefault();
    event.stopPropagation();
    handleCommentAddImage(addImageBtn);
    return;
  }

  const removeImageBtn = target.closest('[data-action="comment-remove-image"]');
  if (removeImageBtn) {
    event.preventDefault();
    event.stopPropagation();
    handleCommentRemoveImage(removeImageBtn);
    return;
  }

  const postMenuToggle = target.closest('.post-menu__toggle');
  if (postMenuToggle) {
    event.preventDefault();
    event.stopPropagation();
    togglePostMenu(postMenuToggle);
    return;
  }

  const commentMenuToggle = target.closest('.comment-menu__toggle');
  if (commentMenuToggle) {
    event.preventDefault();
    event.stopPropagation();
    toggleCommentMenu(commentMenuToggle);
    return;
  }

  const postMenuItem = target.closest('.post-menu__item');
  if (postMenuItem) {
    event.preventDefault();
    event.stopPropagation();
    closeAllMenus();
    handlePostDelete(postMenuItem.getAttribute('data-id'));
    return;
  }

  const commentMenuItem = target.closest('.comment-menu__item');
  if (commentMenuItem) {
    event.preventDefault();
    event.stopPropagation();
    closeAllMenus();
    handleCommentDelete(commentMenuItem.getAttribute('data-id'));
    return;
  }

  const mediaTrigger = target.closest('[data-action="open-media"]');
  if (mediaTrigger) {
    event.preventDefault();
    event.stopPropagation();
    openImageLightbox(mediaTrigger.getAttribute('data-media'));
    return;
  }

  if (!target.closest('.post-menu, .comment-menu')) {
    closeAllMenus();
  }
}

function onGlobalKeydown(event) {
  if (event.key === 'Escape') {
    closeAllMenus();
    closeImageLightbox();
    return;
  }

  if ((event.key === 'Enter' || event.key === ' ') && event.target && event.target.matches('[data-action="open-media"]')) {
    event.preventDefault();
    openImageLightbox(event.target.getAttribute('data-media'));
  }
}

function onGlobalChange(event) {
  const target = event.target;
  if (target && target.matches('input[type="file"][data-comment-image]')) {
    handleCommentFileChange(target);
  }
}

function togglePostMenu(toggle) {
  const menu = toggle.closest('.post-menu');
  if (!menu) return;
  const isOpen = menu.classList.contains('is-open');
  closeAllMenus();
  if (!isOpen) {
    menu.classList.add('is-open');
    toggle.setAttribute('aria-expanded', 'true');
  }
}

function toggleCommentMenu(toggle) {
  const menu = toggle.closest('.comment-menu');
  if (!menu) return;
  const isOpen = menu.classList.contains('is-open');
  closeAllMenus();
  if (!isOpen) {
    menu.classList.add('is-open');
    toggle.setAttribute('aria-expanded', 'true');
  }
}

function closeAllMenus() {
  document.querySelectorAll('.post-menu.is-open').forEach((menu) => {
    menu.classList.remove('is-open');
    const toggle = menu.querySelector('.post-menu__toggle');
    if (toggle) toggle.setAttribute('aria-expanded', 'false');
  });
  document.querySelectorAll('.comment-menu.is-open').forEach((menu) => {
    menu.classList.remove('is-open');
    const toggle = menu.querySelector('.comment-menu__toggle');
    if (toggle) toggle.setAttribute('aria-expanded', 'false');
  });
}

async function handlePostDelete(postId) {
  if (!postId) return;
  try {
    await apiDeletePost(postId);
    if (feed) {
      feed.innerHTML = '<div class="error">El post fue eliminado. Redirigiendo…</div>';
    }
    setTimeout(() => {
      window.location.href = '../../Inicio/inicio.php';
    }, 1200);
  } catch (err) {
    alert(err instanceof Error ? err.message : String(err));
  }
}

async function handleCommentDelete(commentId) {
  if (!commentId) return;
  try {
    await apiDeleteComment(commentId);
    const node = document.querySelector(`.c-node[data-cid="${cssEscape(commentId)}"]`);
    if (!node) return;

    const details = node.closest('details');
    const removedCount = 1 + node.querySelectorAll('.c-node').length;
    node.remove();

    cleanupEmptyCommentLists(details);
    updateCommentCounter(details, -removedCount);
  } catch (err) {
    alert(err instanceof Error ? err.message : String(err));
  }
}

function handleCommentAddImage(button) {
  const form = button.closest('form');
  if (!form || button.disabled || button.classList.contains('is-disabled')) return;
  const input = form.querySelector('input[type="file"][data-comment-image]');
  if (input) {
    input.click();
  }
}

function handleCommentRemoveImage(button) {
  const form = button.closest('form');
  if (!form) return;
  clearCommentImage(form);
}

function handleCommentFileChange(input) {
  const form = input.closest('form');
  if (!form) return;

  const files = Array.from(input.files || []).filter(
    (file) => file && file.type && file.type.startsWith('image/')
  );

  if (files.length === 0) {
    if ((input.files || []).length > 0) {
      alert('El archivo seleccionado no es una imagen compatible.');
    }
    clearCommentImage(form);
    return;
  }

  const [file] = files;
  setCommentImagePreview(form, file);
}

function setCommentImagePreview(form, file) {
  clearCommentImage(form);

  const preview = form.querySelector('.comment-form__preview');
  if (!preview) return;

  const url = URL.createObjectURL(file);
  const img = document.createElement('img');
  img.src = url;
  img.alt = file.name ? `Imagen seleccionada: ${file.name}` : 'Imagen seleccionada';

  preview.innerHTML = '';
  preview.appendChild(img);
  preview.hidden = false;
  preview.dataset.previewUrl = url;

  const addBtn = form.querySelector('[data-action="comment-add-image"]');
  if (addBtn) {
    addBtn.disabled = true;
    addBtn.classList.add('is-disabled');
  }

  const removeBtn = form.querySelector('[data-action="comment-remove-image"]');
  if (removeBtn) {
    removeBtn.hidden = false;
  }
}

function clearCommentImage(form) {
  const preview = form.querySelector('.comment-form__preview');
  if (preview) {
    const url = preview.dataset.previewUrl;
    if (url) {
      URL.revokeObjectURL(url);
    }
    preview.innerHTML = '';
    preview.hidden = true;
    delete preview.dataset.previewUrl;
  }

  const addBtn = form.querySelector('[data-action="comment-add-image"]');
  if (addBtn) {
    addBtn.disabled = false;
    addBtn.classList.remove('is-disabled');
  }

  const removeBtn = form.querySelector('[data-action="comment-remove-image"]');
  if (removeBtn) {
    removeBtn.hidden = true;
  }

  const input = form.querySelector('input[type="file"][data-comment-image]');
  if (input) {
    input.value = '';
  }
}

function clearCommentForm(form) {
  if (!form) return;
  form.reset();
  clearCommentImage(form);
}

function commentFormHasImage(form) {
  if (!form) return false;
  const input = form.querySelector('input[type="file"][data-comment-image]');
  if (!input) return false;
  const files = input.files || [];
  return files.length > 0;
}

function updateCommentCounter(details, delta) {
  if (!details || !delta) return;
  const summary = details.querySelector('summary');
  if (!summary) return;

  const match = summary.textContent.match(/(\d+)/);
  const current = match ? parseInt(match[1], 10) : 0;
  const next = Math.max(0, current + delta);
  summary.textContent = `Comentarios (${next})`;
}

function cleanupEmptyCommentLists(details) {
  if (!details) return;
  const wrapper = details.querySelector('.comentarios');
  if (!wrapper) return;

  wrapper.querySelectorAll('ul.c-tree').forEach((list) => {
    if (list.children.length === 0) {
      list.remove();
    }
  });

  if (!wrapper.querySelector('.c-tree')) {
    wrapper.innerHTML = '<div class="muted">Sé el primero en comentar</div>';
  }
}

function ensureImageLightbox() {
  let lightbox = document.getElementById('imageLightbox');
  if (lightbox) return lightbox;

  lightbox = document.createElement('div');
  lightbox.id = 'imageLightbox';
  lightbox.className = 'lightbox';
  lightbox.setAttribute('aria-hidden', 'true');
  lightbox.innerHTML = `
    <div class="lightbox__content">
      <button type="button" class="lightbox__close" aria-label="Cerrar imagen">×</button>
      <img src="" alt="Imagen ampliada">
    </div>
  `;
  lightbox.addEventListener('click', (e) => {
    if (e.target === lightbox || e.target.closest('.lightbox__close')) {
      closeImageLightbox();
    }
  });
  document.body.appendChild(lightbox);
  return lightbox;
}

function openImageLightbox(url) {
  if (!url) return;
  const lightbox = ensureImageLightbox();
  const img = lightbox.querySelector('img');
  if (img) img.src = url;
  lightbox.classList.add('is-open');
  lightbox.setAttribute('aria-hidden', 'false');
  document.body.style.overflow = 'hidden';
}

function closeImageLightbox() {
  const lightbox = document.getElementById('imageLightbox');
  if (!lightbox) return;
  lightbox.classList.remove('is-open');
  lightbox.setAttribute('aria-hidden', 'true');
  const img = lightbox.querySelector('img');
  if (img) img.src = '';
  document.body.style.overflow = '';
}

function formatDate(iso) {
  try {
    return new Date(iso).toLocaleString();
  } catch {
    return '';
  }
}

function cssEscape(value) {
  if (typeof CSS !== 'undefined' && typeof CSS.escape === 'function') {
    return CSS.escape(String(value));
  }
  return String(value).replace(/[^a-zA-Z0-9_\-]/g, (ch) => `\\${ch}`);
}

function cryptoRand() {
  return String(Date.now()) + Math.floor(Math.random() * 10000);
}
