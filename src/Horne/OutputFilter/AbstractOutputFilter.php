<?php

namespace Horne\OutputFilter;

use Horne\Application;
use Horne\OutputFilter\OutputFilterInterface;

abstract class AbstractOutputFilter implements OutputFilterInterface
{
    protected $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }
}
