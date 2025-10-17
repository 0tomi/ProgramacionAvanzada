<?php
declare(strict_types=1);

namespace Posts\Lib;

/**
 * Utilidades para transformar posts en HTML reutilizable.
 */
final class PostRenderer
{
    /**
     * Genera el bloque HTML completo de un post con sus comentarios.
     *
     * @param array<string, mixed> $post Post ya enriquecido por PostService.
     */
    public function renderFullPost(array $post): string
    {
        $avatar = htmlspecialchars((string) ($post['author']['avatar_url'] ?? '/imagenes/profilePictures/defaultProfilePicture.png'), ENT_QUOTES, 'UTF-8');
        $name = htmlspecialchars($post['author']['name'] ?? 'Anónimo', ENT_QUOTES, 'UTF-8');
        $createdAt = $post['created_at'] ?? '';
        $createdAttr = htmlspecialchars((string) $createdAt, ENT_QUOTES, 'UTF-8');
        $timeLabel = $createdAt !== '' ? date('d/m/Y H:i', strtotime((string) $createdAt)) : '';
        $text = htmlspecialchars($post['text'] ?? '', ENT_QUOTES, 'UTF-8');

        $mediaHtml = '';
        if (!empty($post['media_url'])) {
            $mediaUrl = htmlspecialchars((string) $post['media_url'], ENT_QUOTES, 'UTF-8');
            $mediaHtml = <<<HTML
                <figure class="media">
                    <img class="post-image" src="{$mediaUrl}" alt="Imagen del post">
                </figure>
            HTML;
        }

        $likeCount = (int) ($post['counts']['likes'] ?? 0);
        $replyCount = (int) ($post['counts']['replies'] ?? 0);
        $isLiked = !empty($post['viewer']['liked']);
        $isAuth = !empty($post['viewer']['authenticated']);
        $likeDisabled = $isAuth ? '' : 'disabled title="Inicia sesión para likear"';
        $likeClass = 'chip' . ($isLiked ? ' liked' : '');

        $postId = htmlspecialchars((string) ($post['id'] ?? ''), ENT_QUOTES, 'UTF-8');

        $commentsHtml = $this->renderCommentsSection($post);

        return <<<HTML
            <article class="post" data-id="{$postId}">
                <header class="post-header">
                    <img class="avatar" src="{$avatar}" alt="{$name}">
                    <div class="meta">
                        <div class="name">{$name}</div>
                        <div class="subline"><time datetime="{$createdAttr}">{$timeLabel}</time></div>
                    </div>
                </header>

                <p class="text">{$text}</p>
                {$mediaHtml}

                <form method="post" class="actions">
                    <input type="hidden" name="action" value="toggle_like">
                    <input type="hidden" name="post_id" value="{$postId}">
                    <button class="{$likeClass}" {$likeDisabled}>
                        ♥ <span class="like-count">{$likeCount}</span>
                    </button>
                </form>

                {$commentsHtml}
            </article>
        HTML;
    }

    /**
     * Renderiza el bloque de comentarios y el formulario asociado.
     *
     * @param array<string, mixed> $post
     */
    public function renderCommentsSection(array $post): string
    {
        $postId = htmlspecialchars((string) ($post['id'] ?? ''), ENT_QUOTES, 'UTF-8');
        $replyCount = (int) ($post['counts']['replies'] ?? 0);
        $treeHtml = $this->renderCommentsTree($post['replies'] ?? [], $postId);

        return <<<HTML
            <details open>
                <summary>Comentarios ({$replyCount})</summary>
                <div class="comentarios">{$treeHtml}</div>
                <form class="comment-root" method="post">
                    <input type="hidden" name="action" value="comment">
                    <input type="hidden" name="post_id" value="{$postId}">
                    <input name="author" placeholder="Tu nombre">
                    <input name="text" required maxlength="280" placeholder="Escribe un comentario">
                    <button class="btn primary">Comentar</button>
                </form>
            </details>
        HTML;
    }

    /**
     * @param array<int, array<string, mixed>> $comments
     */
    private function renderCommentsTree(array $comments, string $postId): string
    {
        $nodes = $this->buildTree($comments);
        if ($nodes === []) {
            return '<div class="muted">Sé el primero en comentar</div>';
        }

        $items = array_map(fn ($node) => $this->renderCommentNode($node, $postId), $nodes);
        return '<ul class="c-tree">' . implode('', $items) . '</ul>';
    }

    /**
     * @param array<string, mixed> $node
     */
    private function renderCommentNode(array $node, string $postId): string
    {
        $id = htmlspecialchars((string) ($node['id'] ?? ''), ENT_QUOTES, 'UTF-8');
        $author = htmlspecialchars((string) ($node['author'] ?? 'Anónimo'), ENT_QUOTES, 'UTF-8');
        $text = htmlspecialchars((string) ($node['text'] ?? ''), ENT_QUOTES, 'UTF-8');
        $createdAt = (string) ($node['created_at'] ?? '');
        $timeLabel = $createdAt !== '' ? date('d/m/Y H:i', strtotime($createdAt)) : '';

        $children = $node['children'] ?? [];
        $childrenHtml = '';
        if (is_array($children) && $children !== []) {
            $childrenHtml = '<ul class="c-tree">' . implode('', array_map(fn ($child) => $this->renderCommentNode($child, $postId), $children)) . '</ul>';
        }

        return <<<HTML
            <li class="c-node" data-cid="{$id}">
                <div class="c-bubble">
                    <div class="c-meta"><b>{$author}</b> · <span>{$timeLabel}</span></div>
                    <div class="c-text">{$text}</div>
                    <div class="c-actions">
                        <button type="button" class="btn ghost small" data-reply-toggle="{$id}">Responder</button>
                    </div>
                    <form class="c-reply-form hidden" method="post">
                        <input type="hidden" name="action" value="comment">
                        <input type="hidden" name="post_id" value="{$postId}">
                        <input type="hidden" name="parent_comment_id" value="{$id}">
                        <input name="author" placeholder="Tu nombre">
                        <input name="text" required maxlength="280" placeholder="Responder…">
                        <button class="btn">Enviar</button>
                    </form>
                </div>
                {$childrenHtml}
            </li>
        HTML;
    }

    /**
     * Convierte la lista plana de comentarios en árbol.
     *
     * @param array<int, array<string, mixed>> $comments
     * @return array<int, array<string, mixed>>
     */
    private function buildTree(array $comments): array
    {
        $map = [];
        foreach ($comments as $comment) {
            $id = (string) ($comment['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $map[$id] = $comment + ['children' => []];
        }

        $roots = [];
        foreach ($map as $id => &$node) {
            $parentId = $node['parent_id'] ?? null;
            $parentId = $parentId !== null ? (string) $parentId : null;
            if ($parentId !== null && isset($map[$parentId])) {
                $map[$parentId]['children'][] = &$node;
            } else {
                $roots[] = &$node;
            }
        }

        // Limpiar referencias
        return array_map(fn ($node) => $this->cloneNode($node), $roots);
    }

    /**
     * @param array<string, mixed> $node
     * @return array<string, mixed>
     */
    private function cloneNode(array $node): array
    {
        $copy = $node;
        if (isset($copy['children']) && is_array($copy['children'])) {
            $copy['children'] = array_map(fn ($child) => $this->cloneNode($child), $copy['children']);
        }
        return $copy;
    }
}
