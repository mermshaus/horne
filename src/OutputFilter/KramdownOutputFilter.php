<?php

namespace Horne\OutputFilter;

use Horne\MetaBag;

class KramdownOutputFilter extends AbstractOutputFilter
{
    /**
     * @param string  $content
     * @param MetaBag $metaBag
     *
     * @return string
     */
    public function run($content, MetaBag $metaBag)
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
