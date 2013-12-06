<?php

namespace Horne\Module\Linkblog;

use Horne\MetaBag;
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
        $app = $this->application;

        // horne-linkblog-index

        $app->metas->add(new MetaBag(
            __DIR__ . '/scripts/index.phtml',
            $app->getSetting('outputDir') . '/linkblog/index.html',
            array(
                'id'     => 'horne-linkblog-index',
                'title'  => 'Linkblog',
                'type'   => 'page',
                'layout' => 'horne-layout-page',
                'path'   => '/linkblog/index.html'
            )
        ));
    }
}
