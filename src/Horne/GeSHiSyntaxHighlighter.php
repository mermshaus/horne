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

        return $html;
    }
}
