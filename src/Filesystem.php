<?php

declare(strict_types=1);

namespace LaravelLang\Filesystem;

use Illuminate\Filesystem\Filesystem as IlluminateFilesystem;

use Symfony\Component\VarExporter\VarExporter;

use function base_path;
use function json_encode;
use function mb_strtolower;
use function pathinfo;

class Filesystem
{
    public function __construct(
        protected IlluminateFilesystem $filesystem,
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

    public function store(string $path, array $content): void
    {
        $this->isJson($path)
            ? $this->storeJson($path, $content)
            : $this->storePhp($path, $content);
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

    protected function storeJson(string $path, array $content): void
    {
        $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR;

        $this->filesystem->put($path, json_encode($content, $flags));
    }

    protected function storePhp(string $path, array $content): void
    {
        $exported = VarExporter::export($content);

        $this->filesystem->put($path, $exported);
    }
}
