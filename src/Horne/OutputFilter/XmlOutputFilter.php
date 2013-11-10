<?php

namespace Horne\OutputFilter;

use Horne\OutputFilter\OutputFilterInterface;
use Kaloa\Renderer\Factory;

class XmlOutputFilter implements OutputFilterInterface
{
    public function run($content)
    {
        $mp = Factory::createRenderer(null, 'xml');

        $tmp = $mp->render($content);

        return $tmp;
    }
}
