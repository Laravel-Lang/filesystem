<?php

declare(strict_types=1);

namespace LaravelLang\Filesystem;

use Composer\InstalledVersions;
use Composer\Semver\Semver;
use Composer\Semver\VersionParser;
use Illuminate\Container\Attributes\Config;

use function in_array;

readonly class Composer
{
    /** @param array<string> $force */
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

        $installed = InstalledVersions::getVersion($vendor);

        if ($installed === null) {
            return InstalledVersions::satisfies(new VersionParser, $vendor, $version);
        }

        return Semver::satisfies($installed, $version);
    }

    protected function projectName(): ?string
    {
        return $this->root()['name'] ?? null;
    }

    /** @return array{name?: string|null, ...<string, mixed>} */
    protected function root(): array
    {
        return InstalledVersions::getRootPackage();
    }

    protected function isForced(string $name): bool
    {
        return in_array($name, $this->force, true);
    }
}
