<?php

namespace Horne\Module\Debug;

use Horne\Module\AbstractModule;

/**
 *
 */
class Debug extends AbstractModule
{
    public function hookProcessingBefore()
    {
        $this->sourceDir(__DIR__ . '/scripts');
    }
}
