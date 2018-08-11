<?php

namespace Horne\OutputFilter;

use Horne\MetaBag;

class UppercaseOutputFilter extends AbstractOutputFilter
{
    /**
     * @param string  $content
     * @param MetaBag $metaBag
     *
     * @return string
     */
    public function run($content, MetaBag $metaBag)
    {
        return mb_strtoupper($content, 'UTF-8');
    }
}
