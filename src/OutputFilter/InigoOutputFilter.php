<?php

namespace Horne\OutputFilter;

use Horne\MetaBag;
use Kaloa\Renderer\Config;
use Kaloa\Renderer\Factory;

class InigoOutputFilter extends AbstractOutputFilter
{
    public function run(string $content, MetaBag $metaBag): string
    {
        $config = new Config('.', $this->application->getSyntaxHighlighter());

        $inigoRenderer = Factory::createRenderer('inigo', $config);

        return $inigoRenderer->render($content);
    }
}
