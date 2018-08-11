<?php

namespace Horne\OutputFilter;

use Horne\Application;

abstract class AbstractOutputFilter implements OutputFilterInterface
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }
}
