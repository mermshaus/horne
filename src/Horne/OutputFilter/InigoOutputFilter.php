<?php

namespace Horne\OutputFilter;

use Horne\MetaBag;
use Kaloa\Renderer\Config;
use Kaloa\Renderer\Factory;

class InigoOutputFilter extends AbstractOutputFilter
{
    /**
     * @param string  $content
     * @param MetaBag $metaBag
     *
     * @return string
     * @throws \Exception
     */
    public function run($content, MetaBag $metaBag)
    {
        $config = new Config();
        $config->setSyntaxHighlighter($this->application->getSyntaxHighlighter());

        $inigoRenderer = Factory::createRenderer($config, 'inigo');

        return $inigoRenderer->render($content);
    }
}
