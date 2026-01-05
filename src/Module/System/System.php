<?php

namespace Horne\Module\System;

use Horne\Module\AbstractModule;

class System extends AbstractModule
{
    public function hookLoadConfig(array $settings): array
    {
        $settings['siteName']   = array_key_exists('siteName', $settings) ? $settings['siteName'] : null;
        $settings['siteSlogan'] = array_key_exists('siteSlogan', $settings) ? $settings['siteSlogan'] : null;

        return $settings;
    }

    /**
     * @throws \Horne\HorneException
     * @throws \InvalidArgumentException
     */
    public function hookProcessingBefore(): void
    {
        $this->sourceDir(__DIR__ . '/data');
    }
}
