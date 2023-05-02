#!/usr/bin/env php
<?php

use Xpathgenerator\Service\XPathExtractionService;

require_once 'vendor/autoload.php';

if (!$xmlData = file_get_contents('php://stdin')) {
    echo "Error: Cannot read file from stdin.\n";
    exit(1);
}

$serviceXpath = new XPathExtractionService();
// Beispielaufruf


try {

    $results = $serviceXpath->getXPaths($xmlData);
    // Convert the results array to JSON without escape characters
    $json = json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_INVALID_UTF8_IGNORE);

    echo $json;
} catch (Exception $e) {
    echo "Extraction failed with following Reason:" . PHP_EOL .
        $e->getMessage();
}

