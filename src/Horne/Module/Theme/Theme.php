<?php

namespace Horne\Module\Theme;

use Horne\Module\AbstractModule;

class Theme extends AbstractModule
{
    /**
     *
     * @param array $settings
     */
    public function hookLoadConfig(array $settings)
    {
        if (!array_key_exists('name', $settings)) {
            throw new HorneException('Setting theme.name must be set');
        }

        return $settings;
    }

    public function hookProcessingBefore()
    {
        $theme = $this->application->getSetting('theme.name');

        $this->sourceDir(__DIR__ . '/themes/' . $theme);
    }
}
