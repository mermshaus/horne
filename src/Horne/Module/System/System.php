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

        $app->metas->add(new MetaBag(__DIR__ . '/scripts/page.phtml', $app->config['outputDir'] . '/nothing', [
            'id' => 'horne-layout-page',
            'type' => 'layout',
            'layout' => 'horne-layout-default'
        ]));

        $app->metas->add(new MetaBag(__DIR__ . '/scripts/_default.phtml', $app->config['outputDir'] . '/nothing', [
            'id' => 'horne-layout-default',
            'type' => 'layout'
        ]));
    }
}
