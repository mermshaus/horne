<?php

namespace Horne\Module;

use Horne\Application;
use Horne\Module\ModuleInterface;

/**
 *
 */
abstract class AbstractModule implements ModuleInterface
{
    /**
     *
     * @var Application
     */
    protected $application;

    /**
     *
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function hookProcessingBefore()
    {

    }

    public function hookProcessingBefore2()
    {

    }
}
