<?php

namespace Horne\OutputFilter;

use Horne\MetaBag;
use Kaloa\Renderer\Factory;

class XmlOutputFilter extends AbstractOutputFilter
{
    public function run(string $content, MetaBag $metaBag): string
    {
        $xmlRenderer = Factory::createRenderer('xml', null);

        return $xmlRenderer->render($content);
    }
}
