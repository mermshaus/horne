<?php

namespace Horne\Module;

interface ModuleInterface
{
    public function hookLoadConfig(array $settings);
    public function hookProcessingBefore();
    public function hookProcessingBefore2();
}
