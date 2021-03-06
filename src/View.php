<?php

namespace Horne;

class View
{
    /**
     * @param string $tplFile
     * @param Api    $api
     * @param array  $vars
     */
    public function execute($tplFile, Api $api, array $vars = [])
    {
        $closure = function () use ($tplFile, $api, $vars) {
            require $tplFile;
        };

        // Will make $this unavailable in included code
        $contextlessClosure = $closure->bindTo(null);

        $contextlessClosure();
    }
}
