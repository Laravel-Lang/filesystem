<?php

declare(strict_types=1);

namespace LaravelLang\Filesystem;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;

class Composer
{
    public function hasProject(?string $vendor): bool
    {
        if (! $vendor) {
            return true;
        }

        $root = InstalledVersions::getRootPackage()['name'] ?? null;

        return $vendor === $root;
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
}
