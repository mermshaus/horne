<?php

namespace Horne\Module;

interface ModuleInterface
{
    /**
     * @param array $settings
     *
     * @return array
     */
    public function hookLoadConfig(array $settings): array;

    public function hookProcessingBefore(): void;

    public function hookProcessingBefore2(): void;
}
