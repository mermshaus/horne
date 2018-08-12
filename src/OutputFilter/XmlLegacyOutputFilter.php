<?php

namespace Horne\OutputFilter;

use DateTime;
use Horne\MetaBag;
use Kaloa\Renderer\Config;
use Kaloa\Renderer\Factory;

class XmlLegacyOutputFilter extends AbstractOutputFilter
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
        $config = null;

        if ($metaBag->getType() === 'article') {
            $tmp = $metaBag->getMetaPayload();
            $dt  = DateTime::createFromFormat('Y-m-d H:i:s', $tmp['date_created']);

            $dataRoot = $this->application->getPathToRoot() . '/data/blog';

            $config = new Config();
            $config->setResourceBasePath($dataRoot . '/' . $dt->format('Y') . '/' . $dt->format('m'));
            $config->setSyntaxHighlighter($this->application->getSyntaxHighlighter());
        }

        $xmlLegacyRenderer = Factory::createRenderer($config, 'xmllegacy');

        return $xmlLegacyRenderer->render($content);
    }
}
