<?php

namespace Horne;

use Horne\GeSHiRepository;
use Kaloa\Renderer\SyntaxHighlighter;

/**
 *
 */
class GeSHiSyntaxHighlighter extends SyntaxHighlighter
{
    /**
     *
     * @var GeSHiRepository
     */
    protected $geshiRepository;

    /**
     *
     * @param GeSHiRepository $geshiRepository
     */
    public function __construct(GeSHiRepository $geshiRepository)
    {
        $this->geshiRepository = $geshiRepository;
    }

    private function stuff($html, $language = '', array $lineHighlights = array())
    {
        $html = preg_replace_callback(
            '/<span class="([^"]+)">(.*?)<\/span>/s',
            function ($m) use ($language) {

                $x = $m[1];

                switch (true) {
                    case (1 === preg_match('/^co(?:[0-9]+|MULTI)$/', $m[1])):
                        $x = 'def_comment';
                        break;
                    case (1 === preg_match('/^re[0-9]+$/', $m[1])):
                        $x = 'def_identifier';
                        break;
                    case (1 === preg_match('/^nu[0-9]+$/', $m[1])):
                        $x = 'def_decimal';
                        break;
                    case (1 === preg_match('/^st(?:[0-9]+|_h)$/', $m[1])):
                        $x = 'def_string';
                        break;
                    case (1 === preg_match('/^kw[0-9]+$/', $m[1])):
                        $x = 'def_keyword';
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

        $html = preg_replace('/<pre class="([^"]+)">/', '<pre class="geshi $1">', $html);


        $matches = array();
        preg_match('/(<pre[^>]*>)(.*?)(<\/pre>)/s', $html, $matches);

        list(/*skip*/, $start, $content, $end) = $matches;

        $html = $start;

        $stack = array();

        $i = 0;

        foreach (explode("\n", $content) as $line) {
            // Add spans from stack to line
            $line = implode('', $stack) . $line;
            $stack = array();


            $spans = array();
            preg_match_all('/<span[^>]*>|<\/span>/', $line, $spans, PREG_OFFSET_CAPTURE);

            foreach ($spans[0] as $entry) {
                if (0 !== strpos($entry[0], '</')) {
                    // Opening tag
                    array_push($stack, $entry[0]);
                } else {
                    array_pop($stack);
                }
            }

            $line .= str_repeat('</span>', count($stack));

            $i++;

            $highlight = false;

            foreach ($lineHighlights as $range) {
                if ($i >= $range[0] && $i <= $range[0] + $range[1]) {
                    $highlight = true;
                    break;
                }
            }

            if ($highlight) {
                $html .= '<span class="line selection" id="l'.$i.'">' . strip_tags($line) . "</span>\n";
            } else {
                $html .= '<span class="line" id="l'.$i.'">' . $line . "</span>\n";
            }
        }

        $html = rtrim($html) . $end;


        $tmp = '<div class="source"><table>';

        $tmp .= '<tr>';
        $tmp .= '<td style="vertical-align: top; text-align: right; min-width: 4em;">';
        $tmp .= '<div class="geshi"><div class="line-numbers">';
        $tmp .= '<pre style="padding-right: 0.5em;">' . implode("\n", range(1, $i)) . '</pre>';
        $tmp .= '</div></div>';
        $tmp .= '</td>';
        $tmp .= '<td style="vertical-align: top;">' . $html . '</td>';

        $tmp .= '</tr>';

        $tmp .= '</table></div>';

        return $tmp;
    }

    /**
     *
     * @param string $source
     * @param string $language
     * @return string
     */
    public function highlight($source, $language)
    {
        // Language name might be used in HTML attributes, so filter it
        $language = preg_replace('/[^a-zA-Z0-9_-]/', '', $language);

        if ($language === '' || $language === 'plain') {
            $language = 'text';
        }

        $geshi = $this->geshiRepository->obtain($language);

        $geshi->set_source(trim($source, "\r\n"));

        $html = $geshi->parse_code();

        $html = $this->stuff($html, $language);

        return $html;
    }
}
