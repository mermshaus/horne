<?php

namespace Horne\OutputFilter;

use Horne\MetaBag;
use Horne\OutputFilter\AbstractOutputFilter;

class KramdownOutputFilter extends AbstractOutputFilter
{
    public function run($content, MetaBag $mb)
    {
        $args = $this->application->getSetting('filters.kramdown.cmdargs');

        if ($args === null) {
            $args = '--coderay-css :class --coderay-line-numbers nil';
        }

        $file = __DIR__ . '/bla';

        file_put_contents($file, $content);

        $data = shell_exec('kramdown ' . $args . ' < ' . $file);

        unlink($file);

        return $data;
    }
}
