<?php

use Horne\Application;
use Horne\GeSHiSyntaxHighlighter;
use Horne\OutputFilter\InigoOutputFilter;
use Horne\OutputFilter\KramdownOutputFilter;
use Horne\OutputFilter\TableOfContentsOutputFilter;
use Horne\OutputFilter\UppercaseOutputFilter;
use Horne\OutputFilter\XmlLegacyOutputFilter;
use Horne\OutputFilter\XmlOutputFilter;

require_once __DIR__ . '/../vendor/autoload.php';

$horne = new Application();

$horne->setSyntaxHighlighter(new GeSHiSyntaxHighlighter());

$horne->setFilters('toc', [
    new TableOfContentsOutputFilter($horne)
]);

$horne->setFilters('uppercase', [
    new UppercaseOutputFilter($horne)
]);

$horne->setFilters('kramdown', [
    new KramdownOutputFilter($horne)
]);

$horne->setFilters('inigo', [
    new InigoOutputFilter($horne)
]);

$horne->setFilters('xml', [
    new XmlOutputFilter($horne)
]);

$horne->setFilters('xmllegacy', [
    new XmlLegacyOutputFilter($horne)
]);

return $horne;
