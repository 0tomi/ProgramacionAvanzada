<?php
declare(strict_types=1);

require_once __DIR__ . '/../Model/PostRepository.php';

/**
 * Controlador del módulo de Inicio.
 *
 * Centraliza el acceso a la API de posts y ofrece una vía de respaldo
 * directa al repositorio para mantener la página funcional aun ante
 * fallos de red o configuración.
 */
final class InicioController
{
    private ?string $apiEndpoint;

    public function __construct()
    {
        $this->apiEndpoint = $this->resolveApiEndpoint();
    }

    /**
     * Obtiene el feed principal desde la API. Si la llamada falla devuelve
     * los datos directamente desde el repositorio.
     *
     * @param int|null $viewerId
     * @return array<int, array<string, mixed>>
     */
    public function getFeed(?int $viewerId = null): array
    {
        $response = $this->callApi('list');
        if (is_array($response) && ($response['ok'] ?? false) && isset($response['items']) && is_array($response['items'])) {
            return $response['items'];
        }

        // Fallback: acceder directamente al repositorio para evitar dejar sin feed al usuario.
        try {
            $repository = new PostRepository();
            return $repository->getFeed($viewerId);
        } catch (\Throwable $e) {
            error_log('[InicioController] Error obteniendo feed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Resuelve la URL completa hacia la API de posts.
     */
    private function resolveApiEndpoint(): ?string
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if ($host === '') {
            return null;
        }

        $scheme = 'http';
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $scheme = 'https';
        } elseif (isset($_SERVER['REQUEST_SCHEME'])) {
            $scheme = $_SERVER['REQUEST_SCHEME'];
        } elseif (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) {
            $scheme = 'https';
        }

        $scriptDir = isset($_SERVER['SCRIPT_NAME']) ? rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') : '';
        $basePath = $scriptDir !== '' ? preg_replace('#/Inicio$#', '', $scriptDir) : '';

        return sprintf(
            '%s://%s%s/POSTS/api.php',
            $scheme,
            $host,
            $basePath === '' ? '' : $basePath
        );
    }

    /**
     * Realiza la llamada HTTP hacia la API.
     *
     * @param string $action
     * @param array<string,mixed> $options
     * @return array<string,mixed>|null
     */
    private function callApi(string $action, array $options = []): ?array
    {
        if ($this->apiEndpoint === null) {
            return null;
        }

        $method = strtoupper((string)($options['method'] ?? 'GET'));
        $url = $this->apiEndpoint . '?action=' . urlencode($action);

        $headers = [];
        $headers[] = 'Accept: application/json';

        if (session_status() === PHP_SESSION_ACTIVE) {
            $headers[] = sprintf('%s: %s=%s', 'Cookie', session_name(), session_id());
        }

        if (isset($options['headers']) && is_array($options['headers'])) {
            foreach ($options['headers'] as $h) {
                if (is_string($h) && trim($h) !== '') {
                    $headers[] = $h;
                }
            }
        }

        $contextConfig = [
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers) . "\r\n",
                'ignore_errors' => true,
            ],
        ];

        if (isset($options['body'])) {
            $contextConfig['http']['content'] = (string)$options['body'];
        }

        $context = stream_context_create($contextConfig);
        $raw = @file_get_contents($url, false, $context);
        if ($raw === false) {
            return null;
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }
}
