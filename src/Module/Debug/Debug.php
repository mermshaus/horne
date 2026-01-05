<?php

namespace Horne\Module\Debug;

use Horne\Module\AbstractModule;

class Debug extends AbstractModule
{
    /**
     * @throws \Horne\HorneException
     * @throws \InvalidArgumentException
     */
    public function hookProcessingBefore(): void
    {
        $this->sourceDir(__DIR__ . '/scripts');
    }
}
