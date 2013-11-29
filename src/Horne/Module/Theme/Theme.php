<?php

namespace Horne\Module\Theme;

use Horne\Module\AbstractModule;

class Theme extends AbstractModule
{
    /**
     *
     * @param array $settings
     */
    public function hookLoadConfig(array $settings)
    {
        if (!array_key_exists('name', $settings)) {
            throw new HorneException('Setting theme.name must be set');
        }

        return $settings;
    }

    public function hookProcessingBefore()
    {
        $theme = $this->application->getSetting('theme.name');

        $mb = new \Horne\MetaBag(
            __DIR__ . '/themes/' . $theme . '/screen.css',
            $this->application->config->get('outputDir') . '/assets/screen.css',
            array(
                'id'   => '/assets/screen.css',
                'type' => 'asset',
                'path' => '/assets/screen.css'
            )
        );

        $this->application->metas->add($mb);
    }
}
