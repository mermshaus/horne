<?php

namespace Horne\OutputFilter;

class KramdownOutputFilter implements OutputFilterInterface
{
    public function run($content)
    {
        $file = __DIR__ . '/bla';

        file_put_contents($file, $content);

        $data = shell_exec('kramdown < ' . $file);

        unlink($file);

        return $data;
    }
}
