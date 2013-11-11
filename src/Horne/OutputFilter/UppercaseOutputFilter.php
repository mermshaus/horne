<?php

namespace Horne\OutputFilter;

use Horne\MetaBag;

class UppercaseOutputFilter extends AbstractOutputFilter
{
    public function run($content, MetaBag $mb)
    {
        return mb_strtoupper($content, 'UTF-8');
    }
}
