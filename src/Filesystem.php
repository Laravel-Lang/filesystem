<?php

declare(strict_types=1);

namespace LaravelLang\Filesystem;

use Illuminate\Filesystem\Filesystem as BaseFilesystem;
use LaravelLang\Filesystem\Processors\JsonProcessor;
use LaravelLang\Filesystem\Processors\PhpProcessor;

use function base_path;
use function dirname;
use function mb_strtolower;
use function pathinfo;

class Filesystem
{
    public function __construct(
        protected BaseFilesystem $filesystem,
        protected JsonProcessor $jsonProcessor,
        protected PhpProcessor $phpProcessor,
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
        return $this->jsonProcessor->read($path, $key, $default);
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
        return $this->phpProcessor->read($path, $key, $default);
    }

    public function ensureDelete(string $path): void
    {
        $this->filesystem->deleteDirectory($path);
    }

    protected function storeJson(string $path, array $content, bool $merge = false): void
    {
        $this->jsonProcessor->store($path, $content, $merge);
    }

    protected function storePhp(string $path, array $content, bool $merge = false): void
    {
        $this->phpProcessor->store($path, $content, $merge);
    }
}
