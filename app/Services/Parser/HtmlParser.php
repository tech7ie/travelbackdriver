<?php

namespace App\Services\Parser;

use DOMDocument;
use DOMXPath;

class HtmlParser
{
    function getItemsInHtml(string $html, array $items): ?string
    {
        $finder = $this->domInitFinder($html);
        //$item = "//". $attribute->getTag() . "[@". $attribute->getType() ."='" . $attribute->getValue() . "']";

        return '';
    }

    private function domInitFinder(string $html): DOMXPath
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        return new DomXPath($dom);
    }
}
