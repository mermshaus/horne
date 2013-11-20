<?php

namespace Horne\Module\Theme;

use Horne\Module\AbstractModule;

class Theme extends AbstractModule
{
    public function hookProcessingBefore()
    {
        $theme = $this->application->getSetting('theme.name');

        $mb = new \Horne\MetaBag(
            __DIR__ . '/themes/' . $theme . '/screen.css',
            $this->application->config['outputDir'] . '/assets/screen.css',
            array(
                'id'   => '/assets/screen.css',
                'type' => 'asset',
                'path' => '/assets/screen.css'
            )
        );

        $this->application->metas->add($mb);
    }
}
