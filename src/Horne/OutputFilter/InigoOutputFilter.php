<?php

namespace Horne\OutputFilter;

use Horne\MetaBag;
use Horne\OutputFilter\AbstractOutputFilter;
use Kaloa\Renderer\Config;
use Kaloa\Renderer\Factory;

class InigoOutputFilter extends AbstractOutputFilter
{
    public function run($content, MetaBag $mb)
    {
        $config = new Config();
        $config->setSyntaxHighlighter($this->application->getSyntaxHighlighter());

        $mp = Factory::createRenderer($config, 'inigo');

        $tmp = $mp->render($content);

        return $tmp;
    }
}
