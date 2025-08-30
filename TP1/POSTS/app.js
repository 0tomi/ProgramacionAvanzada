// POSTS/app.js
const API = "api.php";
const feed = document.getElementById("feed");

init();

function init(){
  const params = new URLSearchParams(location.search);
  const id = params.get("id");
  if(!id){
    feed.innerHTML = `<div class="error">Falta el parámetro ?id=...</div>`;
    return;
  }
  cargarPost(id);
}

async function cargarPost(id){
  try{
    const res = await fetch(`${API}?action=get&id=${encodeURIComponent(id)}`, { credentials: "same-origin" });
    const data = await res.json();
    if(!data.ok) throw new Error(data.error || "No se pudo cargar el post");
    feed.innerHTML = renderPost(data.item);   // <- un solo post
    bindPostEvents(feed);
  }catch(err){
    feed.innerHTML = `<div class="error">${escapeHtml(String(err.message || err))}</div>`;
  }
}

/* ==== Render de Post ==== */
function renderPost(post) {
  const img = post.media_url
    ? `<img class="post-image" src="${escapeHtml(post.media_url)}" alt="imagen del post">`
    : "";
  const author = post.author?.handle ? `@${escapeHtml(post.author.handle)}` : '@anon';
  const name = post.author?.name ? escapeHtml(post.author.name) : 'Anónimo';
  const ts = formatDate(post.created_at);

  // Si no está autenticado, deshabilitamos like y el form de comentarios
  const canInteract = !!post.viewer?.authenticated;
  const likeBtnAttrs = canInteract ? '' : 'disabled title="Inicia sesión para likear"';
  const likeClasses = `like ${post.viewer?.liked ? "liked" : ""}`;
  const commentFormDisabled = canInteract ? '' : 'disabled';
  const commentPlaceholder = canInteract ? 'Escribe un comentario' : 'Inicia sesión para comentar';

  return `
    <article class="post" data-id="${post.id}">
      <header class="post-header">
        <div class="avatar">${(post.author?.handle||'U')[0].toUpperCase()}</div>
        <div class="meta">
          <strong>${name}</strong> <span class="handle">${author}</span>
          <span class="time"> · ${ts}</span>
        </div>
      </header>

      <p>${escapeHtml(post.text)}</p>
      ${img}

      <div>
        <button class="${likeClasses}" ${likeBtnAttrs} onclick="toggleLike('${post.id}', this)">
          ♥ <span class="like-count">${post.counts.likes}</span>
        </button>
      </div>

      <details open>
        <summary>Comentarios (${post.counts.replies})</summary>
        <div class="comentarios">
          ${renderCommentsTree(post.replies || [])}
        </div>
        <form onsubmit="return comentar('${post.id}', null, this)">
          <input name="author" placeholder="Tu nombre" ${commentFormDisabled}>
          <input name="text" required maxlength="280" placeholder="${commentPlaceholder}" ${commentFormDisabled}>
          <button ${commentFormDisabled}>Comentar</button>
        </form>
      </details>
    </article>
  `;
}


/* ==== Árbol de comentarios ==== */
function buildTree(list) {
  const byId = new Map();
  list.forEach(c => byId.set(c.id || cryptoRand(), { ...c, children: [] }));
  const roots = [];
  byId.forEach(node => {
    if (node.parent_id && byId.has(node.parent_id)) {
      byId.get(node.parent_id).children.push(node);
    } else {
      roots.push(node);
    }
  });
  return roots;
}

function renderCommentsTree(list) {
  if (!Array.isArray(list) || list.length === 0) {
    return '<div class="muted">Sé el primero en comentar</div>';
  }
  const roots = buildTree(list);
  return `<ul class="c-tree">${roots.map(renderCommentNode).join("")}</ul>`;
}

function renderCommentNode(node) {
  return `
    <li class="c-node" data-cid="${node.id}">
      <div class="c-bubble">
        <div class="c-meta"><b>${escapeHtml(node.author || "Anónimo")}</b> · <span>${formatDate(node.created_at)}</span></div>
        <div class="c-text">${escapeHtml(node.text || "")}</div>
        <div class="c-actions">
          <button class="c-reply-btn" onclick="toggleReplyForm('${node.id}', this)">Responder</button>
        </div>
        <form class="c-reply-form hidden" onsubmit="return comentar(getPostId(this), '${node.id}', this)">
          <input name="author" placeholder="Tu nombre">
          <input name="text" required maxlength="280" placeholder="Responder…">
          <button>Enviar</button>
        </form>
      </div>
      ${node.children?.length ? `<ul class="c-tree">${node.children.map(renderCommentNode).join("")}</ul>` : ""}
    </li>
  `;
}

/* ==== Likes ==== */
async function toggleLike(id, btn) {
  const countEl = btn.querySelector(".like-count");
  const prevLiked = btn.classList.contains("liked");
  const prev = parseInt(countEl.textContent, 10);

  // Optimistic
  btn.classList.toggle("liked");
  countEl.textContent = prevLiked ? prev - 1 : prev + 1;

  try {
    const res = await fetch(`${API}?action=like`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "same-origin",
      body: JSON.stringify({ post_id: id })
    });
    const data = await res.json();
    if (!data.ok) throw new Error(data.error || "Error al likear");
    btn.classList.toggle("liked", data.liked);
    countEl.textContent = data.like_count;
  } catch (err) {
    // rollback
    btn.classList.toggle("liked", prevLiked);
    countEl.textContent = prev;
    alert(String(err.message || err));
  }
}

/* ==== Comentar (raíz o respuesta) ==== */
async function comentar(postId, parentCommentId, form) {
  const payload = {
    post_id: postId,
    parent_comment_id: parentCommentId,
    author: form.author.value,
    text: form.text.value
  };

  try {
    const res = await fetch(`${API}?action=comment`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "same-origin",
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (!data.ok) throw new Error(data.error || "Error al comentar");

    // Insertar DOM sin recargar
    const comentarios = form.closest("details").querySelector(".comentarios");

    if (parentCommentId) {
      // respuesta dentro del nodo objetivo
      const targetNode = comentarios.querySelector(`[data-cid="${parentCommentId}"]`);
      if (targetNode) {
        let sub = targetNode.querySelector(":scope > ul.c-tree");
        if (!sub) {
          sub = document.createElement("ul");
          sub.className = "c-tree";
          targetNode.appendChild(sub);
        }
        sub.insertAdjacentHTML("afterbegin", renderCommentNode({ ...data.comment, children: [] }));
      }
      form.classList.add("hidden");
    } else {
      // comentario raíz
      let root = comentarios.querySelector("ul.c-tree");
      if (!root) {
        comentarios.innerHTML = "";
        root = document.createElement("ul");
        root.className = "c-tree";
        comentarios.appendChild(root);
      }
      root.insertAdjacentHTML("afterbegin", renderCommentNode({ ...data.comment, children: [] }));
    }

    // actualizar contador de summary
    const sum = form.closest("details").querySelector("summary");
    const m = sum.textContent.match(/\d+/);
    if (m) sum.textContent = `Comentarios (${parseInt(m[0], 10) + 1})`;

    form.reset();
  } catch (err) {
    alert(String(err.message || err));
  }
  return false;
}

/* ==== Utilidades & Bind ==== */
function bindPostEvents(_root) { /* reservado por si queremos delegación futura */ }

function toggleReplyForm(_commentId, btn) {
  const bubble = btn.closest(".c-bubble");
  const form = bubble.querySelector(".c-reply-form");
  form.classList.toggle("hidden");
}

function getPostId(el) {
  return el.closest("article.post").dataset.id;
}

function escapeHtml(s) {
  return (s || "").replace(/[&<>"']/g, ch => ({
    "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;"
  }[ch]));
}

function formatDate(iso) {
  try { return new Date(iso).toLocaleString(); } catch { return ""; }
}

function cryptoRand() {
  return String(Date.now()) + Math.floor(Math.random() * 10000);
}
