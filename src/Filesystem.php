<?php

declare(strict_types=1);

namespace LaravelLang\Filesystem;

use Illuminate\Filesystem\Filesystem as IlluminateFilesystem;

use function base_path;

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

    public function glob(string $pattern, int $flags = 0): array
    {
        return $this->filesystem->glob($pattern, $flags);
    }

    public function json(string $path, ?string $key = null, array $default = []): array
    {
        $content = $this->filesystem->json($path);

        if (! $key) {
            return $content;
        }

        return $content[$key] ?? $default;
    }

    public function php(string $path, ?string $key = null, array $default = []): array
    {
        $content = require $path;

        if (! $key) {
            return $content;
        }

        return $content[$key] ?? $default;
    }
}
