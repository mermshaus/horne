<?php

namespace Horne\OutputFilter;

use Horne\MetaBag;
use Kaloa\Renderer\Factory;

class XmlOutputFilter extends AbstractOutputFilter
{
    public function run($content, MetaBag $mb)
    {
        $mp = Factory::createRenderer(null, 'xml');

        $tmp = $mp->render($content);

        return $tmp;
    }
}
