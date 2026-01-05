<?php

declare(strict_types=1);

namespace Horne\Module\Theme;

use Horne\HorneException;
use Horne\Module\AbstractModule;

class Theme extends AbstractModule
{
    public function hookLoadConfig(array $settings): array
    {
        if (!array_key_exists('name', $settings)) {
            throw new HorneException('Setting theme.name must be set');
        }

        return $settings;
    }

    /**
     * @throws HorneException
     * @throws \InvalidArgumentException
     */
    public function hookProcessingBefore(): void
    {
        $theme = $this->application->getSetting('theme.name');

        $this->sourceDir(__DIR__ . '/themes/' . $theme);
    }
}
