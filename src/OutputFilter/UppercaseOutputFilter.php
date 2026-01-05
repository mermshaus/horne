<?php

namespace Horne\OutputFilter;

use Horne\MetaBag;

class UppercaseOutputFilter extends AbstractOutputFilter
{
    public function run(string $content, MetaBag $metaBag): string
    {
        return mb_strtoupper($content, 'UTF-8');
    }
}
