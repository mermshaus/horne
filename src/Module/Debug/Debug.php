<?php

namespace Horne\Module\Debug;

use Horne\Module\AbstractModule;

class Debug extends AbstractModule
{
    /**
     * @return void
     * @throws \Horne\HorneException
     * @throws \InvalidArgumentException
     */
    public function hookProcessingBefore()
    {
        $this->sourceDir(__DIR__ . '/scripts');
    }
}
