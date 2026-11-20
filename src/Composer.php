<?php

declare(strict_types=1);

namespace LaravelLang\Filesystem;

use Composer\InstalledVersions;
use Composer\Semver\Semver;
use Composer\Semver\VersionParser;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Arr;

use function in_array;

class Composer
{
    public function __construct(
        #[Config('localization_main.force')]
        protected array $force,
    ) {}

    public function hasProject(?string $vendor): bool
    {
        if (! $vendor) {
            return true;
        }

        if ($this->isForced($vendor)) {
            return true;
        }

        return $vendor === $this->projectName();
    }

    public function hasPackage(?string $vendor): bool
    {
        if (! $vendor) {
            return true;
        }

        if ($this->isForced($vendor)) {
            return true;
        }

        return InstalledVersions::isInstalled($vendor);
    }

    public function hasVersion(?string $vendor, ?string $version): bool
    {
        if (! $vendor || ! $version) {
            return true;
        }

        if ($this->isForced($vendor)) {
            return true;
        }

        if ($vendor === 'laravel/framework' && InstalledVersions::getVersion($vendor) === 'dev-master') {
            return Semver::satisfies('dev-master', $version);
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

    protected function isForced(string $name): bool
    {
        return in_array($name, $this->force, true);
    }
}
