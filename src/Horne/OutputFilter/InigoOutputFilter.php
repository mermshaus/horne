<?php

namespace Horne\OutputFilter;

use Horne\MetaBag;
use Horne\OutputFilter\AbstractOutputFilter;
use Kaloa\Renderer\Factory;

class InigoOutputFilter extends AbstractOutputFilter
{
    public function run($content, MetaBag $mb)
    {
        $mp = Factory::createRenderer(null, 'inigo');

        $tmp = $mp->render($content);

        return $tmp;
    }
}
