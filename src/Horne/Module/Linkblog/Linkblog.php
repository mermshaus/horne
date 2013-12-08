<?php

namespace Horne\Module\Linkblog;

use Horne\Module\AbstractModule;

class Linkblog extends AbstractModule
{
    /**
     *
     * @param array $settings
     */
    public function hookLoadConfig(array $settings)
    {
        $app = $this->application;

        $settings['dataFile'] = (isset($settings['dataFile']))
                ? $settings['dataFile']
                : 'linkblog.rss';

        $settings['dataFile'] = $app->dingsify($app->getSetting('sourceDir'), $settings['dataFile']);

        return $settings;
    }

    /**
     *
     */
    public function hookProcessingBefore()
    {
        $this->sourceDir(__DIR__ . '/scripts');
    }
}
