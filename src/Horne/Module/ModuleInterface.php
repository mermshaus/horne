<?php

namespace Horne\Module;

interface ModuleInterface
{
    /**
     * @param array $settings
     *
     * @return array
     */
    public function hookLoadConfig(array $settings);

    /**
     * @return void
     */
    public function hookProcessingBefore();

    /**
     * @return void
     */
    public function hookProcessingBefore2();
}
