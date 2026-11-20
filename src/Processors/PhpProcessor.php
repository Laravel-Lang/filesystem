<?php

declare(strict_types=1);

namespace LaravelLang\Filesystem\Processors;

use Illuminate\Support\Arr;
use Symfony\Component\VarExporter\VarExporter;

use function array_merge;
use function ksort;
use function sprintf;

class PhpProcessor extends Processor
{
    protected string $template = <<<'PHP'
        <?php

        declare(strict_types=1);

        return %s;

        PHP;

    protected function getContent(string $path): array
    {
        return require $path;
    }

    protected function putContent(string $path, array $content, bool $merge = false): string
    {
        $data = $this->merge($path, $content, $merge);

        ksort($data);

        return $this->storeContent($data);
    }

    protected function storeContent(array $data): string
    {
        return sprintf($this->template, $this->export($data));
    }

    protected function merge(string $path, array $content, bool $merge): array
    {
        if ($merge) {
            $previous = Arr::dot($this->read($path));
            $current  = Arr::dot($content);

            return array_merge($previous, $current);
        }

        return Arr::dot($content);
    }

    protected function export(array $data): string
    {
        return VarExporter::export(
            Arr::undot($data)
        );
    }
}
