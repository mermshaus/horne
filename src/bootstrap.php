<?php

use Horne\Application;
use Horne\GeSHiRepository;
use Horne\GeSHiSyntaxHighlighter;
use Horne\OutputFilter\InigoOutputFilter;
use Horne\OutputFilter\KramdownOutputFilter;
use Horne\OutputFilter\TableOfContentsOutputFilter;
use Horne\OutputFilter\UppercaseOutputFilter;
use Horne\OutputFilter\XmlLegacyOutputFilter;
use Horne\OutputFilter\XmlOutputFilter;

require_once __DIR__ . '/../vendor/autoload.php';

$horne = new Application();

$horne->setSyntaxHighlighter(new GeSHiSyntaxHighlighter(new GeSHiRepository()));

$horne->setFilters('toc', array(
    new TableOfContentsOutputFilter($horne)
));

$horne->setFilters('uppercase', array(
    new UppercaseOutputFilter($horne)
));

$horne->setFilters('kramdown', array(
    new KramdownOutputFilter($horne)
));

$horne->setFilters('inigo', array(
    new InigoOutputFilter($horne)
));

$horne->setFilters('xml', array(
    new XmlOutputFilter($horne)
));

$horne->setFilters('xmllegacy', array(
    new XmlLegacyOutputFilter($horne)
));

return $horne;
