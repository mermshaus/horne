<?php

namespace Horne\OutputFilter;

use Horne\OutputFilter\OutputFilterInterface;

class InigoOutputFilter implements OutputFilterInterface
{
    public function run($content)
    {
        return '[not implemented]' . "\n" . $content;
    }
}
