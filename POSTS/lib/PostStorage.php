<?php
declare(strict_types=1);

namespace Posts\Lib;

/**
 * Gestiona el almacenamiento de los posteos en el archivo JSON.
 *
 * La clase encapsula toda la lógica relacionada con el acceso concurrente al
 * archivo y la creación de la estructura de directorios necesaria. De esta
 * manera el resto del código no necesita preocuparse por rutas, locks o
 * formatos.
 */
final class PostStorage
{
    private string $jsonPath;
    private string $uploadsDir;

    public function __construct(?string $jsonPath = null, ?string $uploadsDir = null)
    {
        $this->jsonPath  = $jsonPath  ?? dirname(__DIR__) . '/../JSON/POST.json';
        $this->uploadsDir = $uploadsDir ?? __DIR__ . '/../uploads';
    }

    /**
     * Devuelve todos los posts almacenados.
     *
     * @return array<int, array<string, mixed>>
     */
    public function readAll(): array
    {
        $this->ensureJsonFile();
        $raw = file_get_contents($this->jsonPath);
        if ($raw === false) {
            throw new \RuntimeException('No se pudo leer el archivo de posts.');
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            throw new \RuntimeException('El archivo de posts contiene datos inválidos.');
        }

        return $data;
    }

    /**
     * Escribe completamente el archivo de posts utilizando un lock exclusivo.
     *
     * @param array<int, array<string, mixed>> $posts
     */
    public function writeAll(array $posts): void
    {
        $this->ensureJsonFile();

        $fp = fopen($this->jsonPath, 'c+');
        if ($fp === false) {
            throw new \RuntimeException('No se pudo abrir el archivo de posts para escritura.');
        }

        if (!flock($fp, LOCK_EX)) {
            fclose($fp);
            throw new \RuntimeException('No se pudo bloquear el archivo de posts.');
        }

        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * Asegura la existencia del directorio de subidas.
     */
    public function ensureUploadsDir(): void
    {
        if (!is_dir($this->uploadsDir)) {
            if (!@mkdir($this->uploadsDir, 0775, true) && !is_dir($this->uploadsDir)) {
                throw new \RuntimeException('No se pudo crear el directorio de subidas.');
            }
        }
    }

    public function getUploadsDir(): string
    {
        return $this->uploadsDir;
    }

    public function getJsonPath(): string
    {
        return $this->jsonPath;
    }

    private function ensureJsonFile(): void
    {
        $dir = dirname($this->jsonPath);
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0775, true) && !is_dir($dir)) {
                throw new \RuntimeException('No se pudo crear el directorio de datos de posts.');
            }
        }

        if (!file_exists($this->jsonPath)) {
            if (file_put_contents($this->jsonPath, '[]') === false) {
                throw new \RuntimeException('No se pudo inicializar el archivo de posts.');
            }
        }
    }
}
