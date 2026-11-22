<?php

declare(strict_types=1);

namespace LaravelLang\Filesystem\Processors;

use JsonException;
use LaravelLang\Exceptions\InvalidJsonEncodeException;

use function array_merge;
use function json_encode;
use function json_last_error;
use function json_last_error_msg;
use function ksort;

class JsonProcessor extends Processor
{
    protected int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR;

    protected function getContent(string $path): array
    {
        return $this->filesystem->json($path);
    }

    protected function putContent(string $path, array $content, bool $merge = false): string
    {
        if ($merge) {
            $previous = $this->read($path);
        }

        $data = array_merge($previous ?? [], $content);

        ksort($data);

        return $this->encode($data);
    }

    /** @param  array<int|string, mixed>  $data */
    protected function encode(array $data): string
    {
        try {
            $encoded = json_encode($data, $this->flags);
        } catch (JsonException $exception) {
            throw InvalidJsonEncodeException::from($exception);
        }

        if ($encoded === false) {
            throw new InvalidJsonEncodeException(json_last_error_msg(), json_last_error());
        }

        return $encoded;
    }
}
