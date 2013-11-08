<?php

namespace Horne\Module\Debug;

use Horne\MetaBag;
use Horne\Module\AbstractModule;

class Debug extends AbstractModule
{
    public function hookProcessingBefore()
    {
        $app = $this->application;

        $app->metas->add(new MetaBag(__DIR__ . '/scripts/debug.phtml', $app->config['outputDir'] . '/debug/index.html', [
            'id' => 'horne-debug-debug',
            'title' => 'Debug',
            'type' => 'page',
            'path' => '/debug/index.html'
        ]));
    }
}
