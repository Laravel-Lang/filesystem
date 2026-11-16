<?php

declare(strict_types=1);

namespace LaravelLang\Filesystem;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Illuminate\Support\Arr;

class Composer
{
    public function hasProject(?string $vendor): bool
    {
        if (! $vendor) {
            return true;
        }

        return $vendor === $this->projectName();
    }

    public function hasPackage(?string $vendor): bool
    {
        if (! $vendor) {
            return true;
        }

        return InstalledVersions::isInstalled($vendor);
    }

    public function hasVersion(?string $vendor, ?string $version): bool
    {
        if (! $vendor || ! $version) {
            return true;
        }

        return InstalledVersions::satisfies(new VersionParser(), $vendor, $version);
    }

    protected function projectName(): ?string
    {
        return Arr::get($this->root(), 'name');
    }

    protected function root(): array
    {
        return InstalledVersions::getRootPackage();
    }
}
