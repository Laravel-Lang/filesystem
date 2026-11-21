<?php

declare(strict_types=1);

namespace LaravelLang\Filesystem\Processors;

use Illuminate\Filesystem\Filesystem;

abstract class Processor
{
    /** @return array<array-key, mixed> */
    abstract protected function getContent(string $path): array;

    /** @param array<array-key, mixed> $content */
    abstract protected function putContent(string $path, array $content, bool $merge = false): string;

    public function __construct(
        protected Filesystem $filesystem,
    ) {}

    /**
     * @param  array<array-key, mixed>  $default
     * @return array<array-key, mixed>
     */
    public function read(string $path, ?string $key = null, array $default = []): array
    {
        if (! $this->filesystem->exists($path)) {
            return [];
        }

        $content = $this->getContent($path);

        if (! $key) {
            return $content;
        }

        return $content[$key] ?? $default;
    }

    /** @param array<array-key, mixed> $content */
    public function store(string $path, array $content, bool $merge = false): void
    {
        $prepared = $this->putContent($path, $content, $merge);

        $this->filesystem->put($path, $prepared);
    }
}
