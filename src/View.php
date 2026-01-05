<?php

namespace Horne;

class View
{
    public function execute(string $tplFile, Api $api, array $vars = [])
    {
        $closure = function () use ($tplFile, $api, $vars) {
            require $tplFile;
        };

        // Will make $this unavailable in included code
        $contextlessClosure = $closure->bindTo(null);

        $contextlessClosure();
    }
}
