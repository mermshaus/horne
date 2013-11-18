<?php

namespace Horne\Module\System;

use Horne\MetaBag;
use Horne\Module\AbstractModule;

class System extends AbstractModule
{
    /**
     *
     */
    public function hookProcessingBefore()
    {
        $app = $this->application;

        $app->metas->add(new MetaBag(
            __DIR__ . '/layouts/page.phtml',
            $app->config['outputDir'] . '/nothing',
            [
                'id'     => 'horne-layout-page',
                'type'   => 'layout',
                'layout' => 'horne-layout-default'
            ]
        ));

        $app->metas->add(new MetaBag(
            __DIR__ . '/layouts/default.phtml',
            $app->config['outputDir'] . '/nothing',
            [
                'id'   => 'horne-layout-default',
                'type' => 'layout'
            ]
        ));

        $app->metas->add(new MetaBag(
            __DIR__ . '/layouts/html/head.phtml',
            $app->config['outputDir'] . '/nothing',
            [
                'id'     => 'horne-system-html-head',
                'type'   => '_script'
            ]
        ));

        $app->metas->add(new MetaBag(
            __DIR__ . '/layouts/html/foot.phtml',
            $app->config['outputDir'] . '/nothing',
            [
                'id'     => 'horne-system-html-foot',
                'type'   => '_script'
            ]
        ));

        $app->metas->add(new MetaBag(
            __DIR__ . '/assets/jquery-1.10.2.min.js',
            $app->config['outputDir'] . '/assets/jquery-1.10.2.min.js',
            [
                'id'     => 'jquery',
                'type'   => 'asset'
            ]
        ));
    }
}
