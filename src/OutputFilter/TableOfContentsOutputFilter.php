<?php

namespace Horne\OutputFilter;

use DOMDocument;
use DOMNode;
use Horne\MetaBag;

class TableOfContentsOutputFilter extends AbstractOutputFilter
{
    public function run(string $content, MetaBag $metaBag): string
    {
        $content6 = '';

        if ($content !== '') {
            $content1 = $this->addIds($content, 'h1');
            $content2 = $this->addIds($content1, 'h2');
            $content3 = $this->addIds($content2, 'h3');
            $content4 = $this->addIds($content3, 'h4');
            $content5 = $this->addIds($content4, 'h5');
            $content6 = $this->addIds($content5, 'h6');
        }

        return $content6;
    }

    /**
     * @param DOMNode $node
     *
     * @return string
     */
    protected function innerHTML(DOMNode $node)
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->appendChild($doc->importNode($node, true));
        $html = trim($doc->saveHTML($doc->documentElement));
        $tag  = $node->nodeName;
        return preg_replace('@^<' . $tag . '[^>]*>|</' . $tag . '>$@', '', $html);
    }

    /**
     * @param string $string
     * @param string $tagname
     *
     * @return string
     */
    protected function addIds($string, $tagname)
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->loadHTML('<meta http-equiv="content-type" content="text/html; charset=utf-8">' . $string);

        foreach ($document->getElementsByTagName($tagname) as $item) {
            /* @var \DOMElement $item */
            $id = $this->string_to_id($item->textContent);
            $item->setAttribute('id', $id);
            $item->setAttribute('title', '#' . $id);
        }

        $bodies = $document->getElementsByTagName('body');

        return $this->innerHTML($bodies->item(0));
    }

    /**
     * @param string $s
     *
     * @return string
     */
    protected function string_to_id($s)
    {
        $s = preg_replace('/\s+/', ' ', $s);
        $s = preg_replace('/[^0-9A-Za-z- ]/', '', $s);
        $s = trim($s);
        $s = str_replace(' ', '-', $s);
        $s = strtolower($s);

        return $s;
    }
}
