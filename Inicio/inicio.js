// Inicio/inicio.js

// Ruta base hacia la API del módulo POSTS (desde /Inicio)
const API_BASE = '../Controlers/PostsApi.php';

/**
 * Normaliza rutas relativas de archivos multimedia para que sean accesibles
 * desde la vista de inicio.
 */
function resolveMediaPath(path) {
  const trimmed = (path ?? '').toString().trim();
  if (!trimmed) return '';
  if (/^(?:https?:)?\/\//i.test(trimmed) || trimmed.startsWith('../')) {
    return trimmed;
  }
  return `../${trimmed.replace(/^\/+/, '')}`;
}

document.addEventListener('DOMContentLoaded', () => {
  loadFeed()
    .catch(err => {
      console.error('[feed] Error cargando inicio:', err);
    })
    .then(() => marcarLikesAlCargar());

  wireEventosClick();

  if (window.CreatePostComposer && typeof window.CreatePostComposer.init === 'function') {
    window.CreatePostComposer.init({
      onSubmit: onCreatePostSubmit,
      onError: showErrorAlert
    });
  } else {
    console.warn('CreatePostComposer no está disponible; la vista previa de imágenes no se inicializó.');
    const fallbackForm = document.querySelector('.js-create-post-form');
    if (fallbackForm) {
      fallbackForm.addEventListener('submit', onCreatePostSubmit);
    }
  }
});

/** Obtiene los posts principales desde la API y los renderiza */
async function loadFeed() {
  const feed = document.getElementById('feed');
  if (!feed) return;

  const res = await fetch(`${API_BASE}?action=list`, { credentials: 'same-origin' });
  const data = await res.json();
  if (!data.ok || !Array.isArray(data.items)) {
    throw new Error(data.error || 'No se pudo cargar el feed.');
  }

  renderFeed(data.items);
}

/** Dibuja el feed en pantalla */
function renderFeed(items) {
  const feed = document.getElementById('feed');
  if (!feed) return;

  if (!Array.isArray(items) || items.length === 0) {
    feed.innerHTML = '<p class="muted">No hay posts todavía.</p>';
    return;
  }

  const html = items.map(buildPostHtml).join('');
  feed.innerHTML = html;
}

/** Mapea la respuesta de la API al HTML del post */
function buildPostHtml(p) {
  if (!p || typeof p !== 'object') return '';

  const id = String(p.id ?? '');
  if (!id) return '';

  const author = p.author ?? {};
  const name = author.name ? String(author.name) : 'Anónimo';
  const handle = author.handle ? String(author.handle) : '';
  const avatarUrl = author.avatar_url ? resolveMediaPath(author.avatar_url) : '';
  const initialSource = (handle || name || 'U').trim();
  const avatarInitial = initialSource !== '' ? initialSource.charAt(0).toUpperCase() : 'U';

  const createdIso = p.created_at ? String(p.created_at) : '';
  const createdHuman = formatDateForUi(createdIso);
  const handleLabel = handle !== '' ? formatHandleForUi(handle) : '';

  const counts = p.counts ?? {};
  const likeCount = Number(counts.likes ?? 0);

  const viewer = p.viewer ?? {};
  const liked = !!viewer.liked;
  const canDelete = !!viewer.can_delete;

  const images = normalizeImages(p.images);
  const mediaHtml = images.length > 0
    ? `<div class="media-gallery">
        ${images.map(buildMediaFigureHtml).join('')}
      </div>`
    : '';

  const rawText = String(p.text ?? '');
  const displayText = truncatePostText(rawText, 150);
  const safeDisplayText = escapeHtml(displayText);
  const textHtml = safeDisplayText !== '' ? `<p class="text">${safeDisplayText}</p>` : '';

  const likeClasses = `chip like${liked ? ' liked' : ''}`;

  const safeId = escapeHtml(id);
  const safeName = escapeHtml(name);
  const safeCreatedIso = escapeHtml(createdIso);
  const safeCreatedHuman = escapeHtml(createdHuman);
  const safeHandleLabel = escapeHtml(handleLabel);

  const avatarHtml = avatarUrl !== ''
    ? `<img class="avatar" src="${escapeHtml(avatarUrl)}" alt="Avatar de ${safeName}">`
    : `<div class="avatar">${escapeHtml(avatarInitial)}</div>`;

  const menuHtml = canDelete
    ? `<div class="post-menu">
        <button type="button" class="post-menu__toggle" aria-haspopup="true" aria-expanded="false">
          ⋮
        </button>
        <div class="post-menu__dropdown" role="menu">
          <button type="button" class="post-menu__item post-menu__item--danger" role="menuitem" data-action="delete-post" data-id="${safeId}">
            Eliminar post
          </button>
        </div>
      </div>`
    : '';

  return `
    <article class="post" data-id="${safeId}">
      <a class="post-overlay" href="../Views/POSTS/index.php?id=${encodeURIComponent(id)}" aria-label="Ver post"></a>
      ${menuHtml}
      <header class="post-header">
      ${avatarHtml}
      <div class="meta">
        <div class="name">${safeName}</div>
        <div class="subline">
          ${safeHandleLabel !== '' ? `<span class="handle">${safeHandleLabel}</span> · ` : ''}
          <time datetime="${safeCreatedIso}">${safeCreatedHuman}</time>
        </div>
      </div>
    </header>
      ${textHtml}
      ${mediaHtml}
      <div class="actions">
        <button type="button" class="${likeClasses}" data-id="${safeId}">
          ♥ <span class="count">${likeCount}</span>
        </button>
      </div>
    </article>
  `;
}

function formatHandleForUi(rawHandle) {
  const trimmed = (rawHandle ?? '').toString().trim();
  if (trimmed === '') return '';
  return trimmed.startsWith('@') ? trimmed : `@${trimmed}`;
}

function normalizeImages(rawImages) {
  if (!rawImages) return [];
  if (Array.isArray(rawImages)) {
    return rawImages
      .map(resolveMediaPath)
      .filter(Boolean);
  }
  if (typeof rawImages === 'object') {
    return Object.values(rawImages)
      .map(resolveMediaPath)
      .filter(Boolean);
  }
  if (typeof rawImages === 'string') {
    const normalized = resolveMediaPath(rawImages);
    return normalized !== '' ? [normalized] : [];
  }
  return [];
}

function buildMediaFigureHtml(url) {
  const safeUrl = escapeHtml(url);
  return `<figure class="media" data-action="open-media" data-media="${safeUrl}" tabindex="0" role="button">
    <img src="${safeUrl}" alt="Imagen del post">
  </figure>`;
}

/** Formatea fecha ISO a un string legible para el usuario */
function formatDateForUi(isoString) {
  if (!isoString) return '';
  const date = new Date(isoString);
  if (Number.isNaN(date.getTime())) return '';
  return date.toLocaleString();
}

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

function resolveMediaPath(path) {
  const trimmed = (path ?? '').toString().trim();
  if (!trimmed) return '';
  if (/^(?:https?:)?\/\//i.test(trimmed) || trimmed.startsWith('../')) return trimmed;
  return `../${trimmed.replace(/^\/+/, '')}`;
}

/** Delegación global de clicks para like, menú contextual y overlay */
function wireEventosClick() {
  document.addEventListener('click', async (e) => {
    const target = e.target;
    if (target.closest('.composer')) return;

    const menuToggle = target.closest('.post-menu__toggle');
    if (menuToggle) {
      e.preventDefault();
      e.stopPropagation();
      togglePostMenu(menuToggle);
      return;
    }

    const menuItem = target.closest('.post-menu__item');
    if (menuItem) {
      e.preventDefault();
      e.stopPropagation();
      closeAllPostMenus();
      await eliminarPost(menuItem);
      return;
    }

    const mediaTrigger = target.closest('[data-action="open-media"]');
    if (mediaTrigger) {
      e.preventDefault();
      e.stopPropagation();
      openImageLightbox(mediaTrigger.getAttribute('data-media'));
      return;
    }

    const likeBtn = target.closest('.chip.like');
    if (likeBtn) {
      if (likeBtn.hasAttribute('disabled')) return;
      e.preventDefault();
      e.stopPropagation();
      await manejarLike(likeBtn);
      return;
    }

    closeAllPostMenus();

    const card = target.closest('.post');
    if (!card) return;
    const overlay = card.querySelector('.post-overlay');
    if (!overlay || !overlay.getAttribute('href')) return;

    if (!target.closest('.post-overlay')) {
      window.location.href = overlay.href;
    }
  });
}

function togglePostMenu(toggleBtn) {
  const menu = toggleBtn.closest('.post-menu');
  if (!menu) return;
  const isOpen = menu.classList.contains('is-open');
  closeAllPostMenus();
  if (!isOpen) {
    menu.classList.add('is-open');
    toggleBtn.setAttribute('aria-expanded', 'true');
  }
}

function closeAllPostMenus() {
  document.querySelectorAll('.post-menu.is-open').forEach((menu) => {
    menu.classList.remove('is-open');
    const toggle = menu.querySelector('.post-menu__toggle');
    if (toggle) {
      toggle.setAttribute('aria-expanded', 'false');
    }
  });
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
      <button type="button" class="lightbox__close" aria-label="Cerrar imagen ampliada">×</button>
      <img src="" alt="Imagen del post ampliada">
    </div>
  `;
  lightbox.addEventListener('click', (event) => {
    if (event.target === lightbox || event.target.closest('.lightbox__close')) {
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
  if (img) {
    img.src = url;
  }
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
  if (img) {
    img.src = '';
  }
  document.body.style.overflow = '';
}

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape') {
    closeAllPostMenus();
    closeImageLightbox();
    return;
  }
  if ((event.key === 'Enter' || event.key === ' ') && event.target && event.target.matches('[data-action="open-media"]')) {
    event.preventDefault();
    openImageLightbox(event.target.getAttribute('data-media'));
  }
});

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
    showErrorAlert(String(err.message || err));
  }
}

/** Submit del formulario de crear post (sin redirección) */
async function onCreatePostSubmit(e) {
  e.preventDefault();
  e.stopPropagation();

  const form = e.currentTarget;
  const submitBtn = form.querySelector('[type="submit"]');
  const textarea = form.querySelector('textarea[name]');
  const textFieldName = textarea ? textarea.getAttribute('name') : 'text';
  const fd = new FormData(form);

  const text = (fd.get(textFieldName) || '').toString().trim();
  const hasImage = hasSelectedImages(form);
  if (text.length === 0 && !hasImage) {
    showErrorAlert('Escribí algo o adjuntá al menos una imagen.');
    if (textarea) textarea.focus();
    return;
  }
  if (text.length > 280) {
    showErrorAlert('El texto puede tener hasta 280 caracteres.');
    if (textarea) textarea.focus();
    return;
  }

  if (submitBtn) submitBtn.disabled = true;

  try {
    const res = await fetch(`${API_BASE}?action=create`, {
      method: 'POST',
      body: fd,
      credentials: 'same-origin'
    });
    let data;
    try {
      data = await res.json();
    } catch (_) {
      throw new Error('Respuesta inválida del servidor.');
    }

    if (!res.ok || !data.ok || !data.item) {
      throw new Error((data && data.error) || 'No se pudo crear el post.');
    }

    try {
      insertarPostEnFeed(data.item);
    } catch (err) {
      console.error('Fallo insertarPostEnFeed:', err);
      showErrorAlert('Hubo un error renderizando el nuevo post (ver consola).');
    }

    form.reset();
    showSuccessAlert('Tu post se publicó.');
  } catch (err) {
    const message = err instanceof Error ? err.message : String(err);
    showErrorAlert(message);
  } finally {
    if (submitBtn) submitBtn.disabled = false;
  }
}


/** Inserta el post recién creado al principio del feed (sin @handle) */
function insertarPostEnFeed(p) {
  // defensivo por si algo viene raro
  if (!p || typeof p !== 'object') throw new Error('insertarPostEnFeed: objeto post inválido');

  const html = buildPostHtml(p);
  if (!html) throw new Error('insertarPostEnFeed: no se pudo renderizar el post');

  const feed = document.getElementById('feed');
  if (!feed) throw new Error('No existe #feed en el DOM');

  removeEmptyState(feed);
  feed.insertAdjacentHTML('afterbegin', html);
}

/** Elimina un post del feed pidiendo confirmación al usuario */
async function eliminarPost(button) {
  const postId = button.getAttribute('data-id');
  if (!postId) return;

  button.disabled = true;
  try {
    const res = await fetch(`${API_BASE}?action=delete`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({ post_id: postId })
    });
    const data = await res.json();
    if (!data.ok) throw new Error(data.error || 'No se pudo eliminar el post');

    const article = button.closest('.post');
    if (article) article.remove();
    ensureEmptyState();
    showSuccessAlert('Post eliminado.');
  } catch (err) {
    showErrorAlert(String(err.message || err));
    button.disabled = false;
  }
}

/** Quita el estado vacío del feed cuando se agrega contenido */
function removeEmptyState(feed) {
  feed.querySelectorAll(':scope > .muted').forEach(msg => msg.remove());
}

/** Muestra el estado vacío si no quedan publicaciones */
function ensureEmptyState() {
  const feed = document.getElementById('feed');
  if (!feed) return;
  if (feed.querySelector('article.post')) return;
  feed.innerHTML = '<p class="muted">No hay posts todavía.</p>';
}


/* Utilidad para evitar inyección de HTML */
function escapeHtml(s) {
  return (s ?? '').toString().replace(/[&<>"']/g, ch => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  }[ch]));
}

function truncatePostText(text, maxLength = 150) {
  const raw = (text ?? '').toString();
  if (raw.length <= maxLength) return raw;
  return `${raw.slice(0, maxLength).trimEnd()}.. Expandir el post para ver mas`;
}

function hasSelectedImages(form) {
  if (!form) return false;
  const input = form.querySelector('input[type="file"]');
  if (!input) return false;
  const files = input.files || [];
  return files.length > 0;
}

//mensaje login correcto
const FancyAlerts = (() => {
  const api = {};

  api.show = function (options = {}) {
    // cerrar cualquier alerta previa
    const prev = document.querySelector(".fancy-alert");
    if (prev) prev.remove();

    const defaults = {
      type: "success",
      msg: "Success",
      timeout: 2500,
      icon: "✓", 
      closable: false,
      onClose: () => {}
    };
    const o = { ...defaults, ...options };

    // crear elementos
    const alert = document.createElement("div");
    alert.className = `fancy-alert ${o.type}`;

    alert.innerHTML = `
      <div>
        <div class="fancy-alert--icon">${o.icon}</div>
        <div class="fancy-alert--content">
          <div class="fancy-alert--words">${o.msg}</div>
          ${o.closable ? `<a class="fancy-alert--close" href="#">×</a>` : ""}
        </div>
      </div>
    `;

    document.body.prepend(alert);

    // animaciones
    setTimeout(() => alert.classList.add("fancy-alert__active"), 10);
    setTimeout(() => alert.classList.add("fancy-alert__extended"), 500);

    // autocierre
    if (o.timeout) api.hide(o.timeout, o.onClose);
  };

  api.hide = function (delay = 0, cb = () => {}) {
    const alert = document.querySelector(".fancy-alert");
    if (!alert) return;
    setTimeout(() => {
      alert.classList.remove("fancy-alert__extended");
      setTimeout(() => alert.classList.remove("fancy-alert__active"), 500);
      setTimeout(() => {
        alert.remove();
        cb();
      }, 1000);
    }, delay);
  };

  return api;
})();

function showErrorAlert(message) {
  FancyAlerts.show({
    type: "error",
    icon: "!",
    msg: String(message || "Ocurrió un error inesperado."),
    timeout: 4000,
    closable: true
  });
}

function showSuccessAlert(message) {
  FancyAlerts.show({
    type: "success",
    icon: "✓",
    msg: String(message || "Acción completada."),
    timeout: 2500,
    closable: false
  });
}

document.addEventListener("DOMContentLoaded", () => {
  const flash = document.getElementById("flash");
  if (flash) {
    FancyAlerts.show({
      type: flash.dataset.type,
      msg: flash.dataset.msg,
      timeout: 2500,
      closable: false
    });
    flash.remove();
  }
});
