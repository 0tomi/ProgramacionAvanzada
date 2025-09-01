// /POSTS/app.js
const API = "api.php";
const feed = document.getElementById("feed");

init();

function init(){
  const id = new URLSearchParams(location.search).get("id");
  if(!id){ feed.innerHTML = `<div class="error">Falta el parámetro ?id=...</div>`; return; }
  cargarPost(id);
}

async function cargarPost(id){
  try{
    const res = await fetch(`${API}?action=get&id=${encodeURIComponent(id)}`, { credentials:"same-origin" });
    const data = await res.json();
    if(!data.ok || !data.item) throw new Error(data.error || "Post no encontrado");
    feed.innerHTML = renderPost(data.item);
  }catch(err){
    feed.innerHTML = `<div class="error">${escapeHtml(String(err.message || err))}</div>`;
  }
}

/* ===== Render post + acciones ===== */
function renderPost(post){
  const img = post.media_url
    ? `<figure class="media"><img class="post-image" src="${escapeHtml(post.media_url)}" alt="Imagen del post"></figure>`
    : "";
  const name = post.author?.name ? escapeHtml(post.author.name) : 'Anónimo';
  const initial = (post.author?.name || 'U').charAt(0).toUpperCase(); // ← desde el nombre
  const ts = formatDate(post.created_at);

  const likeClasses = `chip ${post.viewer?.liked ? "liked" : ""}`;

  return `
    <article class="post" data-id="${post.id}">
    <header class="post-header">
      <div class="avatar">${initial}</div>
      <div class="meta">
        <div class="name">${name}</div>
        <div class="subline">
          <time>${ts}</time>
        </div>
      </div>
    </header>

    <p class="text">${escapeHtml(post.text)}</p>
    ${img}

    <div class="actions">
      <button class="${likeClasses}" onclick="toggleLike('${post.id}', this)">
        ♥ <span class="like-count">${post.counts.likes}</span>
      </button>
    </div>

    <details open>
      <summary>Comentarios (${post.counts.replies})</summary>
      <div class="comentarios">
        ${renderCommentsTree(post.replies || [])}
      </div>
      <form class="comment-root" onsubmit="event.preventDefault(); return comentar('${post.id}', null, this, event)">
        <input name="author" placeholder="Tu nombre">
        <input name="text" required maxlength="280" placeholder="Escribe un comentario">
        <button class="btn primary">Comentar</button>
      </form>
    </details>
  </article>
  `;
}

/* ===== Árbol de comentarios ===== */
function buildTree(list){
  const byId = new Map();
  list.forEach(c => byId.set(c.id || cryptoRand(), { ...c, children: [] }));
  const roots = [];
  byId.forEach(node => {
    if (node.parent_id && byId.has(node.parent_id)) byId.get(node.parent_id).children.push(node);
    else roots.push(node);
  });
  return roots;
}
function renderCommentsTree(list){
  if (!Array.isArray(list) || list.length === 0) {
    return '<div class="muted">Sé el primero en comentar</div>';
  }
  const roots = buildTree(list);
  return `<ul class="c-tree">${roots.map(renderCommentNode).join("")}</ul>`;
}
function renderCommentNode(node){
  return `
    <li class="c-node" data-cid="${node.id}">
      <div class="c-bubble">
        <div class="c-meta"><b>${escapeHtml(node.author || "Anónimo")}</b> · <span>${formatDate(node.created_at)}</span></div>
        <div class="c-text">${escapeHtml(node.text || "")}</div>
        <div class="c-actions">
          <button type="button" class="btn ghost small" onclick="toggleReplyForm('${node.id}', this)">Responder</button>
        </div>
        <form class="c-reply-form hidden" onsubmit="event.preventDefault(); return comentar(getPostId(this), '${node.id}', this, event)">
          <input name="author" placeholder="Tu nombre">
          <input name="text" required maxlength="280" placeholder="Responder…">
          <button class="btn">Enviar</button>
        </form>
      </div>
      ${node.children?.length ? `<ul class="c-tree">${node.children.map(renderCommentNode).join("")}</ul>` : ""}
    </li>
  `;
}

/* ===== Likes ===== */
async function toggleLike(id, btn){
  const countEl = btn.querySelector(".like-count");
  const wasLiked = btn.classList.contains("liked");
  const prev = parseInt(countEl.textContent, 10);

  // Optimistic UI
  btn.classList.toggle("liked");
  countEl.textContent = wasLiked ? prev - 1 : prev + 1;

  try{
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
  }catch(err){
    // rollback
    btn.classList.toggle("liked", wasLiked);
    countEl.textContent = prev;
    alert(String(err.message || err));
  }
}

/* ===== Comentar ===== */
async function comentar(postId, parentCommentId, form, ev){
  if (ev) ev.preventDefault();
  const payload = {
    post_id: postId,
    parent_comment_id: parentCommentId,
    author: form.author.value,
    text: form.text.value
  };
  try{
    const res = await fetch(`${API}?action=comment`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "same-origin",
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (!data.ok) throw new Error(data.error || "Error al comentar");

    const comentarios = form.closest("details").querySelector(".comentarios");

    if (parentCommentId) {
      // respuesta en el nodo
      const target = comentarios.querySelector(`[data-cid="${parentCommentId}"]`);
      if (target) {
        let sub = target.querySelector(":scope > ul.c-tree");
        if (!sub) { sub = document.createElement("ul"); sub.className = "c-tree"; target.appendChild(sub); }
        sub.insertAdjacentHTML("afterbegin", renderCommentNode({ ...data.comment, children: [] }));
      }
      form.classList.add("hidden");
    } else {
      // comentario raíz
      let root = comentarios.querySelector("ul.c-tree");
      if (!root) { comentarios.innerHTML = ""; root = document.createElement("ul"); root.className = "c-tree"; comentarios.appendChild(root); }
      root.insertAdjacentHTML("afterbegin", renderCommentNode({ ...data.comment, children: [] }));
    }

    // actualizar contador del summary
    const sum = form.closest("details").querySelector("summary");
    const m = sum.textContent.match(/\d+/);
    if (m) sum.textContent = `Comentarios (${parseInt(m[0], 10) + 1})`;

    form.reset();
  }catch(err){
    alert(String(err.message || err));
  }
  return false;
}

/* ===== Utils ===== */
function toggleReplyForm(_commentId, btn){
  const bubble = btn.closest(".c-bubble");
  const f = bubble.querySelector(".c-reply-form");
  f.classList.toggle("hidden");
}
function getPostId(el){ return el.closest("article.post").dataset.id; }
function escapeHtml(s){ return (s||"").replace(/[&<>"']/g, ch=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[ch])); }
function formatDate(iso){ try{ return new Date(iso).toLocaleString(); }catch{ return ""; } }
function cryptoRand(){ return String(Date.now()) + Math.floor(Math.random()*10000); }
