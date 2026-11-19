<?php

declare(strict_types=1);

namespace LaravelLang\Filesystem;

use Illuminate\Filesystem\Filesystem as BaseFilesystem;
use Illuminate\Support\Arr;
use Symfony\Component\VarExporter\VarExporter;

use function array_merge;
use function base_path;
use function dirname;
use function json_encode;
use function ksort;
use function mb_strtolower;
use function pathinfo;

class Filesystem
{
    protected int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR;

    public function __construct(
        protected BaseFilesystem $filesystem,
    ) {}

    public function basePath(string $path = ''): string
    {
        return base_path($path);
    }

    public function vendorPath(string $path = ''): string
    {
        return $this->basePath('vendor/' . $path);
    }

    public function libraryPath(): string
    {
        return $this->vendorPath('laravel-lang/library');
    }

    public function glob(string $pattern, int $flags = 0): array
    {
        return $this->filesystem->glob($pattern, $flags);
    }

    public function fromJson(string $path, ?string $key = null, array $default = []): array
    {
        if (! $this->filesystem->exists($path)) {
            return [];
        }

        $content = $this->filesystem->json($path);

        if (! $key) {
            return $content;
        }

        return $content[$key] ?? $default;
    }

    public function storeArray(string $path, array $items, bool $merge = false): void
    {
        $this->filesystem->ensureDirectoryExists(
            dirname($path)
        );

        $this->isJson($path)
            ? $this->storeJson($path, $items, $merge)
            : $this->storePhp($path, $items, $merge);
    }

    public function store(string $path, string $content): void
    {
        $this->filesystem->ensureDirectoryExists(
            dirname($path)
        );

        $this->filesystem->put($path, $content);
    }

    public function isJson(string $filename): bool
    {
        return mb_strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'json';
    }

    public function fromPhp(string $path, ?string $key = null, array $default = []): array
    {
        if (! $this->filesystem->exists($path)) {
            return [];
        }

        $content = require $path;

        if (! $key) {
            return $content;
        }

        return $content[$key] ?? $default;
    }

    public function ensureDelete(string $path): void
    {
        $this->filesystem->isFile($path)
            ? $this->filesystem->delete($path)
            : $this->filesystem->deleteDirectory($path);
    }

    protected function storeJson(string $path, array $content, bool $merge = false): void
    {
        if ($merge) {
            $previous = $this->fromJson($path);
        }

        $data = array_merge($previous ?? [], $content);

        ksort($data);

        $this->filesystem->put($path, json_encode($data, $this->flags));
    }

    protected function storePhp(string $path, array $content, bool $merge = false): void
    {
        if ($merge) {
            $previous = Arr::dot($this->fromPhp($path));
            $current  = Arr::dot($content);

            $data = array_merge($previous, $current);
        } else {
            $data = Arr::dot($content);
        }

        ksort($data);

        $exported = VarExporter::export(
            Arr::undot($content)
        );

        $this->filesystem->put(
            $path,
            <<<PHP
<?php

declare(strict_types=1);

return $exported;

PHP
        );
    }
}
