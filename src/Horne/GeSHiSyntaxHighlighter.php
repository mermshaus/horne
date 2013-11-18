<?php

namespace Horne;

use GeSHi;
use Kaloa\Renderer\SyntaxHighlighter;

class GeSHiSyntaxHighlighter extends SyntaxHighlighter
{
    public function highlight($source, $language)
    {
        if ($language === '') {
            $language = 'plain';
        }

        $geshi = new GeSHi(ltrim(rtrim($source), "\r\n"), $language);
        $geshi->enable_classes();
        $geshi->enable_keyword_links(false);

        $html = $geshi->parse_code();

        return $html;
    }
}
