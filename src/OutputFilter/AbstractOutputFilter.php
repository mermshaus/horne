<?php

declare(strict_types=1);

namespace Horne\OutputFilter;

use Horne\Application;

abstract class AbstractOutputFilter implements OutputFilterInterface
{
    public function __construct(protected Application $application)
    {
    }
}
