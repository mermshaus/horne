<?php

namespace Horne;

use Kaloa\Renderer\SyntaxHighlighter;

class GeSHiSyntaxHighlighter extends SyntaxHighlighter
{
    /**
     * @var GeSHiRepository
     */
    protected $geshiRepository;

    /**
     * @param GeSHiRepository $geshiRepository
     */
    public function __construct(GeSHiRepository $geshiRepository)
    {
        $this->geshiRepository = $geshiRepository;
    }

    /**
     * @param string $html
     * @param string $language
     * @param array  $lineHighlights
     *
     * @return string
     */
    private function enhanceHtmlOutput($html, $language = '', array $lineHighlights = [])
    {
        $htmlTmp = preg_replace_callback(
            '/<span class="([^"]+)">(.*?)<\/span>/s',
            function ($m) use ($language) {

                $x = $m[1];

                switch (true) {
                    case (preg_match('/^br(?:\d+)$/', $m[1]) === 1):
                        /* no idea */
                        break;
                    case (preg_match('/^co(?:\d+|MULTI)$/', $m[1]) === 1):
                        $x = 'def_comment';
                        break;
                    case (preg_match('/^kw\d+$/', $m[1]) === 1):
                        $x = 'def_keyword';
                        break;
                    case (preg_match('/^nu\d+$/', $m[1]) === 1):
                        $x = 'def_number';
                        break;
                    case (preg_match('/^re\d+$/', $m[1]) === 1):
                        $x = 'def_identifier';
                        break;
                    case (preg_match('/^st(?:\d+|_h)$/', $m[1]) === 1):
                        $x = 'def_string';
                        break;
                    case (preg_match('/^sy(?:\d+)$/', $m[1]) === 1):
                        $x = 'def_operator';
                        break;
                    default:
                        /* not mapped, yet */
                        break;
                }

                if ($language === 'php') {
                    if ($m[1] === 'kw3') {
                        $x = 'def_function';
                    }
                    if ($m[1] === 'kw4') {
                        $x = 'def_constant';
                    }
                }

                #return '<span class="' . $x . '">' . $m[2] . '<sub>' . $x . '</sub></span>';
                return '<span class="' . $x . '">' . $m[2] . '</span>';
            },
            $html
        );

        $htmlTmp = preg_replace('/<pre class="([^"]+)">/', '<pre class="geshi $1">', $htmlTmp);


        $matches = [];
        preg_match('/(<pre[^>]*>)(.*?)(<\/pre>)/s', $htmlTmp, $matches);

        list(/*skip*/, $start, $content, $end) = $matches;

        $htmlTmp = $start;

        $stack = [];

        $i = 0;

        foreach (explode("\n", $content) as $line) {
            // Add spans from stack to line
            $line  = implode('', $stack) . $line;
            $stack = [];


            $spans = [];
            preg_match_all('/<span[^>]*>|<\/span>/', $line, $spans, PREG_OFFSET_CAPTURE);

            foreach ($spans[0] as $entry) {
                if (strpos($entry[0], '</') !== 0) {
                    // Opening tag
                    $stack[] = $entry[0];
                } else {
                    array_pop($stack);
                }
            }

            $line .= str_repeat('</span>', count($stack));

            $i++;

            $highlight = false;

            foreach ($lineHighlights as $range) {
                if ($i >= $range[0] && $i < $range[0] + $range[1]) {
                    $highlight = true;
                    break;
                }
            }

            if ($highlight) {
                // No support for IDs as of now
                #$html .= '<span class="line selection" id="l'.$i.'">' . strip_tags($line) . "</span>\n";
                $htmlTmp .= '<span class="line selection">' . strip_tags($line) . "</span>\n";
            } else {
                #$html .= '<span class="line" id="l'.$i.'">' . $line . "</span>\n";
                $htmlTmp .= '<span class="line">' . $line . "</span>\n";
            }
        }

        $htmlTmp = rtrim($htmlTmp) . $end;

        $tmp = '<div class="source">' . "\n";
        $tmp .= '  <table>' . "\n";
        $tmp .= '    <tr>' . "\n";
        $tmp .= '      <td class="sidebar">' . "\n";
        $tmp .= '        <div class="geshi">' . "\n";
        $tmp .= '          <pre class="line-numbers">' . implode("\n", range(1, $i)) . '</pre>' . "\n";
        $tmp .= '        </div>' . "\n";
        $tmp .= '      </td>' . "\n";
        $tmp .= '      <td>' . "\n";
        $tmp .= '        ' . $htmlTmp . "\n";
        $tmp .= '      </td>' . "\n";
        $tmp .= '    </tr>' . "\n";
        $tmp .= '  </table>' . "\n";
        $tmp .= '</div>' . "\n";

        return $tmp;
    }

    /**
     * @param string $source
     * @param string $language
     *
     * @return string
     * @throws \Exception
     */
    public function highlight($source, $language)
    {
        if ($language === '' || $language === 'plain') {
            $language = 'text';
        }

        $geshi = $this->geshiRepository->obtain($language);

        $geshi->set_source(trim($source, "\r\n"));

        $html = $geshi->parse_code();

        return $this->enhanceHtmlOutput($html, $language);
    }
}
