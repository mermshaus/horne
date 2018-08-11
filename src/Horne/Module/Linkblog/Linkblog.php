<?php

namespace Horne\Module\Linkblog;

use Horne\Module\AbstractModule;

class Linkblog extends AbstractModule
{
    /**
     * @param array $settings
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function hookLoadConfig(array $settings)
    {
        $app = $this->application;

        $settings['dataFile']       = isset($settings['dataFile']) ? $settings['dataFile'] : 'linkblog.rss';
        $settings['dataFile']       = $app->dingsify($app->getSetting('sourceDir'), $settings['dataFile']);
        $settings['entriesPerPage'] = isset($settings['entriesPerPage']) ? $settings['entriesPerPage'] : 40;

        return $settings;
    }

    /**
     * @return void
     * @throws \Horne\HorneException
     * @throws \InvalidArgumentException
     */
    public function hookProcessingBefore()
    {
        $this->sourceDir(__DIR__ . '/scripts');
    }
}
