<?php

declare(strict_types=1);

namespace PHireScript\Cache;

use PHireScript\Compiler\Program;
use PHireScript\Helper\Messenger;

/**
 * Persistent disk cache for the PHireScript compiler pipeline.
 *
 * Caches type-method registries, tokenized files, parsed ASTs,
 * dependency graphs, and compiler configuration so that repeated
 * builds only reprocess what actually changed.
 *
 * Directory layout (relative to the project root):
 *
 *   .cache/
 *   ├── manifest.json        # file → content-hash index
 *   ├── types/               # serialized SymbolTableManager registries
 *   ├── tokens/              # serialized Token[] per source file
 *   ├── ast/                 # serialized parsed Program per source file
 *   ├── graph/               # serialized DependencyGraphBuilder state
 *   └── config/              # serialized compiler config array
 */
class CacheManager
{
    private const MANIFEST_FILE = 'manifest.json';
    private const DIR_TYPES    = 'types';
    private const DIR_TOKENS   = 'tokens';
    private const DIR_AST      = 'ast';
    private const DIR_GRAPH    = 'graph';
    private const DIR_CONFIG   = 'config';

    private readonly string $cacheDir;

    /** @var array<string, string> filePath → md5 hash */
    private array $manifest = [];

    private bool $dirty = false;

    public function __construct(string $projectRoot)
    {
        $this->cacheDir = rtrim($projectRoot, '/') . '/.cache';
        $this->ensureDirectories();
        $this->loadManifest();
    }

    // =========================================================================
    //  File-level validity
    // =========================================================================

    /**
     * Returns true when the file's content has NOT changed since the last
     * cache write (i.e. the cached artefacts are still valid).
     */
    public function isFileValid(string $filePath): bool
    {
        $realPath = $this->normalizePath($filePath);

        if (!isset($this->manifest[$realPath])) {
            return false;
        }

        return $this->manifest[$realPath] === $this->hashFile($realPath);
    }

    /**
     * Record the current content hash for a source file.
     */
    public function touchFile(string $filePath): void
    {
        $realPath = $this->normalizePath($filePath);
        $this->manifest[$realPath] = $this->hashFile($realPath);
        $this->dirty = true;
    }

    /**
     * Returns true when ALL paths in the list have valid (unchanged) cache entries.
     *
     * @param array<int, string> $filePaths
     */
    public function allFilesValid(array $filePaths): bool
    {
        if ($filePaths === []) {
            return false;
        }

        foreach ($filePaths as $path) {
            if (!$this->isFileValid($path)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Remove all cached artefacts for a single file.
     */
    public function invalidateFile(string $filePath): void
    {
        $realPath = $this->normalizePath($filePath);
        $key = $this->fileKey($realPath);

        // Remove cached tokens
        $this->deleteIfExists($this->path(self::DIR_TOKENS, $key . '.cache'));

        // Remove manifest entry
        unset($this->manifest[$realPath]);
        $this->dirty = true;
    }

    /**
     * Invalidate a file AND every file that (transitively) depends on it.
     *
     * @param array<string, array<string>> $dependencyEdges  package → list of dependent packages
     * @param array<string, string>        $packageToFile    package → file path
     */
    public function invalidateCascade(
        string $filePath,
        array $dependencyEdges,
        array $packageToFile,
    ): void {
        $this->invalidateFile($filePath);

        // Build reverse lookup: file → package
        $fileToPackage = array_flip($packageToFile);
        $normalizedPath = $this->normalizePath($filePath);

        if (!isset($fileToPackage[$normalizedPath])) {
            return;
        }

        $changedPackage = $fileToPackage[$normalizedPath];

        // BFS to find all transitively dependent packages
        $queue = [$changedPackage];
        $visited = [$changedPackage => true];

        while ($queue) {
            $pkg = array_shift($queue);
            $dependents = $dependencyEdges[$pkg] ?? [];

            foreach ($dependents as $dependent) {
                if (isset($visited[$dependent])) {
                    continue;
                }

                $visited[$dependent] = true;
                $queue[] = $dependent;

                if (isset($packageToFile[$dependent])) {
                    $this->invalidateFile($packageToFile[$dependent]);
                }
            }
        }
    }

    // =========================================================================
    //  Type Methods cache  (SymbolTableManager)
    // =========================================================================

    /**
     * @return array<string, array<string, mixed>>|null  The full type-method
     *         registry, or null when the cache is stale / missing.
     */
    public function getTypeMethods(): ?array
    {
        /** @var array<string, array<string, mixed>>|null $result */
        $result = $this->readCache(self::DIR_TYPES, 'registry');
        return $result;
    }

    /**
     * @param array<string, array<string, mixed>> $registry
     */
    public function setTypeMethods(array $registry): void
    {
        $this->writeCache(self::DIR_TYPES, 'registry', $registry);
    }

    /**
     * Check whether any PHP file inside the given directory changed
     * since the last cache write.
     */
    public function areTypeSourcesValid(string $typesDirectory): bool
    {
        $realDir = realpath($typesDirectory);

        if ($realDir === false) {
            return false;
        }

        $files = glob($realDir . '/*.php');

        if ($files === false || $files === []) {
            return false;
        }

        foreach ($files as $file) {
            if (!$this->isFileValid($file)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Touch every PHP file inside the types directory so that subsequent
     * calls to `areTypeSourcesValid()` return true.
     */
    public function touchTypesSources(string $typesDirectory): void
    {
        $realDir = realpath($typesDirectory);

        if ($realDir === false) {
            return;
        }

        $files = glob($realDir . '/*.php') ?: [];

        foreach ($files as $file) {
            $this->touchFile($file);
        }
    }

    // =========================================================================
    //  Token cache
    // =========================================================================

    /**
     * @return array<int, mixed>|null  Serialized token array or null when
     *         the cache is stale / missing.
     */
    public function getTokens(string $filePath): ?array
    {
        if (!$this->isFileValid($filePath)) {
            return null;
        }

        $key = $this->fileKey($this->normalizePath($filePath));

        /** @var array<int, mixed>|null $result */
        $result = $this->readCache(self::DIR_TOKENS, $key);
        return $result;
    }

    /**
     * @param array<int, mixed> $tokens
     */
    public function setTokens(string $filePath, array $tokens): void
    {
        $key = $this->fileKey($this->normalizePath($filePath));
        $this->writeCache(self::DIR_TOKENS, $key, $tokens);
        // Evict any AST cached under the old file content so getProgram() can't
        // return a stale program from a previous build of the same path.
        $this->deleteIfExists($this->path(self::DIR_AST, $key . '.cache'));
        $this->touchFile($filePath);
    }

    // =========================================================================
    //  AST (Program) cache
    // =========================================================================

    /**
     * Returns the cached parsed Program for a source file, or null when the
     * cache is stale or missing.
     */
    public function getProgram(string $filePath): ?Program
    {
        if (!$this->isFileValid($filePath)) {
            return null;
        }

        $key = $this->fileKey($this->normalizePath($filePath));

        /** @var Program|null $result */
        $result = $this->readCache(self::DIR_AST, $key);
        return $result;
    }

    public function setProgram(string $filePath, Program $ast): void
    {
        $key = $this->fileKey($this->normalizePath($filePath));
        $this->writeCache(self::DIR_AST, $key, $ast);
        $this->touchFile($filePath);
    }

    // =========================================================================
    //  Dependency Graph cache
    // =========================================================================

    /**
     * @return array{nodes: array<string, mixed>, edges: array<string, array<string>>, config: array<mixed>}|null
     */
    public function getDependencyGraph(): ?array
    {
        /** @var array{nodes: array<string, mixed>, edges: array<string, array<string>>, config: array<mixed>}|null $result */
        $result = $this->readCache(self::DIR_GRAPH, 'dependency_graph');
        return $result;
    }

    /**
     * @param array{nodes: array<string, mixed>, edges: array<string, array<string>>, config: array<mixed>} $graphData
     */
    public function setDependencyGraph(array $graphData): void
    {
        $this->writeCache(self::DIR_GRAPH, 'dependency_graph', $graphData);
    }

    public function invalidateDependencyGraph(): void
    {
        $this->deleteIfExists(
            $this->path(self::DIR_GRAPH, 'dependency_graph.cache')
        );
    }

    // =========================================================================
    //  Config cache  (metatypes / supertypes discovery)
    // =========================================================================

    /**
     * @return array<string, mixed>|null
     */
    public function getConfig(): ?array
    {
        /** @var array<string, mixed>|null $result */
        $result = $this->readCache(self::DIR_CONFIG, 'compiler_config');
        return $result;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function setConfig(array $config): void
    {
        $this->writeCache(self::DIR_CONFIG, 'compiler_config', $config);
    }

    public function invalidateConfig(): void
    {
        $this->deleteIfExists(
            $this->path(self::DIR_CONFIG, 'compiler_config.cache')
        );
    }

    // =========================================================================
    //  File hash persistence  (used by FileWatcher)
    // =========================================================================

    /**
     * @return array<string, string|int>  filePath → hash/mtime
     */
    public function getFileHashes(): array
    {
        /** @var array<string, string|int>|null $result */
        $result = $this->readCache(self::DIR_CONFIG, 'file_hashes');
        return $result ?? [];
    }

    /**
     * @param array<string, string|int> $hashes
     */
    public function setFileHashes(array $hashes): void
    {
        $this->writeCache(self::DIR_CONFIG, 'file_hashes', $hashes);
    }

    // =========================================================================
    //  Lifecycle
    // =========================================================================

    /**
     * Delete all cache files and reset the manifest.
     */
    public function flush(): void
    {
        $this->deleteRecursive($this->cacheDir);
        $this->manifest = [];
        $this->dirty = false;
        $this->ensureDirectories();
    }

    /**
     * Write the manifest to disk.  Should be called at the end of a build.
     */
    public function persist(): void
    {
        if (!$this->dirty) {
            return;
        }

        $manifestPath = $this->cacheDir . '/' . self::MANIFEST_FILE;
        file_put_contents(
            $manifestPath,
            json_encode($this->manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        );
        $this->dirty = false;
    }

    /**
     * Convenience: persist + log.
     */
    public function close(): void
    {
        $this->persist();
    }

    // =========================================================================
    //  Internal helpers
    // =========================================================================

    private function ensureDirectories(): void
    {
        $dirs = [
            $this->cacheDir,
            $this->cacheDir . '/' . self::DIR_TYPES,
            $this->cacheDir . '/' . self::DIR_TOKENS,
            $this->cacheDir . '/' . self::DIR_AST,
            $this->cacheDir . '/' . self::DIR_GRAPH,
            $this->cacheDir . '/' . self::DIR_CONFIG,
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    private function loadManifest(): void
    {
        $manifestPath = $this->cacheDir . '/' . self::MANIFEST_FILE;

        if (!file_exists($manifestPath)) {
            $this->manifest = [];
            return;
        }

        $content = file_get_contents($manifestPath);

        if ($content === false) {
            $this->manifest = [];
            return;
        }

        $decoded = json_decode($content, true);
        /** @var array<string, string> $safe */
        $safe = is_array($decoded) ? $decoded : [];
        $this->manifest = $safe;
    }

    private function normalizePath(string $path): string
    {
        $real = realpath($path);

        return $real !== false ? $real : $path;
    }

    private function hashFile(string $filePath): string
    {
        if (!file_exists($filePath)) {
            return '';
        }

        return md5_file($filePath) ?: '';
    }

    /**
     * Derive a filesystem-safe key from a file path.
     */
    private function fileKey(string $filePath): string
    {
        return md5($filePath);
    }

    private function path(string $subDir, string $filename): string
    {
        $ext = str_ends_with($filename, '.cache') ? '' : '.cache';

        return $this->cacheDir . '/' . $subDir . '/' . $filename . $ext;
    }

    /**
     * @return mixed  The unserialized value, or null when missing / corrupt.
     */
    private function readCache(string $subDir, string $key): mixed
    {
        $file = $this->path($subDir, $key);

        if (!file_exists($file)) {
            return null;
        }

        $raw = file_get_contents($file);

        if ($raw === false) {
            return null;
        }

        try {
            return unserialize($raw, ['allowed_classes' => true]);
        } catch (\Throwable) {
            // Corrupted cache — silently discard.
            @unlink($file);
            return null;
        }
    }

    private function writeCache(string $subDir, string $key, mixed $data): void
    {
        $file = $this->path($subDir, $key);
        file_put_contents($file, serialize($data));
    }

    private function deleteIfExists(string $path): void
    {
        if (file_exists($path)) {
            @unlink($path);
        }
    }

    private function deleteRecursive(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            /** @var \SplFileInfo $item */
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }

        @rmdir($dir);
    }
}
