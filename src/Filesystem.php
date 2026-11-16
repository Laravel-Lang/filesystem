<?php

declare(strict_types=1);

namespace LaravelLang\Filesystem;

use Illuminate\Filesystem\Filesystem as BaseFilesystem;
use Illuminate\Support\Arr;
use Symfony\Component\VarExporter\VarExporter;

use function array_merge;
use function base_path;
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

    public function store(string $path, array $content, bool $merge = false): void
    {
        $this->isJson($path)
            ? $this->storeJson($path, $content, $merge)
            : $this->storePhp($path, $content, $merge);
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

        $this->filesystem->put($path, $exported);
    }
}
