<?php

namespace Horne\OutputFilter;

class UppercaseOutputFilter implements OutputFilterInterface
{
    public function run($content)
    {
        return mb_strtoupper($content, 'UTF-8');
    }
}
