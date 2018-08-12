<?php

namespace Horne\OutputFilter;

use Horne\MetaBag;

interface OutputFilterInterface
{
    /**
     * @param string  $content
     * @param MetaBag $metaBag
     *
     * @return string
     */
    public function run($content, MetaBag $metaBag);
}
