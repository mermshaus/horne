<?php

namespace Horne\Module\System;

use Horne\MetaBag;
use Horne\Module\AbstractModule;

class System extends AbstractModule
{
    /**
     *
     * @param array $settings
     */
    public function hookLoadConfig(array $settings)
    {
        $settings['siteName'] = (array_key_exists('siteName', $settings))
            ? $settings['siteName']
            : null;

        $settings['siteSlogan'] = (array_key_exists('siteSlogan', $settings))
            ? $settings['siteSlogan']
            : null;

        return $settings;
    }

    /**
     *
     */
    public function hookProcessingBefore()
    {
        $app = $this->application;

        $app->metas->add(new MetaBag(
            __DIR__ . '/layouts/page.phtml',
            $app->config['outputDir'] . '/nothing',
            array(
                'id'     => 'horne-layout-page',
                'type'   => 'layout',
                'layout' => 'horne-layout-default'
            )
        ));

        $app->metas->add(new MetaBag(
            __DIR__ . '/layouts/default.phtml',
            $app->config['outputDir'] . '/nothing',
            array(
                'id'   => 'horne-layout-default',
                'type' => 'layout'
            )
        ));

        $app->metas->add(new MetaBag(
            __DIR__ . '/layouts/html/head.phtml',
            $app->config['outputDir'] . '/nothing',
            array(
                'id'     => 'horne-system-html-head',
                'type'   => '_script'
            )
        ));

        $app->metas->add(new MetaBag(
            __DIR__ . '/layouts/html/foot.phtml',
            $app->config['outputDir'] . '/nothing',
            array(
                'id'     => 'horne-system-html-foot',
                'type'   => '_script'
            )
        ));

        $app->metas->add(new MetaBag(
            __DIR__ . '/assets/jquery-1.10.2.min.js',
            $app->config['outputDir'] . '/assets/jquery-1.10.2.min.js',
            array(
                'id'     => 'jquery',
                'type'   => 'asset'
            )
        ));
    }
}
