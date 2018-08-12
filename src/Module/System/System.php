<?php

namespace Horne\Module\System;

use Horne\Module\AbstractModule;

class System extends AbstractModule
{
    /**
     * @param array $settings
     *
     * @return array
     */
    public function hookLoadConfig(array $settings)
    {
        $settings['siteName']   = array_key_exists('siteName', $settings) ? $settings['siteName'] : null;
        $settings['siteSlogan'] = array_key_exists('siteSlogan', $settings) ? $settings['siteSlogan'] : null;

        return $settings;
    }

    /**
     * @return void
     * @throws \Horne\HorneException
     * @throws \InvalidArgumentException
     */
    public function hookProcessingBefore()
    {
        $this->sourceDir(__DIR__ . '/data');
    }
}
