<?php

namespace Horne\OutputFilter;

use Horne\OutputFilter\OutputFilterInterface;
use Kaloa\Renderer\Factory;

class InigoOutputFilter implements OutputFilterInterface
{
    public function run($content)
    {
        $mp = Factory::createRenderer(null, 'inigo');

        $tmp = $mp->render($content);

        return $tmp;
    }
}
