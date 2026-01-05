<?php

namespace Horne\OutputFilter;

use DateTime;
use Horne\MetaBag;
use Kaloa\Renderer\Config;
use Kaloa\Renderer\Factory;

class XmlLegacyOutputFilter extends AbstractOutputFilter
{
    public function run(string $content, MetaBag $metaBag): string
    {
        $config = null;

        if ($metaBag->getType() === 'article') {
            $tmp = $metaBag->getMetaPayload();
            $dt  = DateTime::createFromFormat('Y-m-d H:i:s', $tmp['date_created']);

            $dataRoot = $this->application->getPathToRoot() . '/data/blog';

            $config = new Config(
                $dataRoot . '/' . $dt->format('Y') . '/' . $dt->format('m'),
                $this->application->getSyntaxHighlighter()
            );
        }

        $xmlLegacyRenderer = Factory::createRenderer('xmllegacy', $config);

        return $xmlLegacyRenderer->render($content);
    }
}
