<?php

namespace Horne\OutputFilter;

use Horne\MetaBag;
use Kaloa\Renderer\Factory;

class XmlOutputFilter extends AbstractOutputFilter
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
        $xmlRenderer = Factory::createRenderer(null, 'xml');

        return $xmlRenderer->render($content);
    }
}
