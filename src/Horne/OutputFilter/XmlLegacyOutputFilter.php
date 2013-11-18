<?php

namespace Horne\OutputFilter;

use DateTime;
use Horne\MetaBag;
use Kaloa\Renderer\Config;
use Kaloa\Renderer\Factory;

class XmlLegacyOutputFilter extends AbstractOutputFilter
{
    public function run($content, MetaBag $mb)
    {
        $config = null;

        if ($mb->getType() === 'article') {
            $dt = DateTime::createFromFormat('Y-m-d H:i:s', $mb->getMetaPayload()['date_created']);

            $dataRoot = $this->application->getPathToRoot() . '/data/blog';

            $config = new Config();
            $config->setResourceBasePath($dataRoot . '/'
                    . $dt->format(('Y')) . '/'
                    . $dt->format(('m')));
            $config->setSyntaxHighlighter($this->application->getSyntaxHighlighter());
        }

        $mp = Factory::createRenderer($config, 'xmllegacy');

        $tmp = $mp->render($content);

        return $tmp;
    }
}
