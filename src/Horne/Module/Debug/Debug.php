<?php

namespace Horne\Module\Debug;

use Horne\MetaBag;
use Horne\Module\AbstractModule;

class Debug extends AbstractModule
{
    public function hookProcessingBefore()
    {
        $app = $this->application;

        $app->metas->add(new MetaBag(__DIR__ . '/scripts/debug.phtml', $app->config['outputDir'] . '/debug/index.html', array(
            'id'     => 'horne-debug-debug',
            'title'  => 'Debug',
            'type'   => 'page',
            'layout' => 'horne-layout-debug',
            'path'   => '/debug/index.html'
        )));

        $app->metas->add(new MetaBag(__DIR__ . '/scripts/layout.phtml', $app->config['outputDir'] . '/nothing', array(
            'id'   => 'horne-layout-debug',
            'type' => 'layout'
        )));
    }
}
