#!/usr/bin/env php
<?php

use Xpathgenerator\Service\XPathExtractionService;

require_once 'vendor/autoload.php';

if (!$xmlData = file_get_contents('php://stdin')) {
    echo "Error: Cannot read file from stdin.\n";
    exit(1);
}

$service = new XPathExtractionService();

try {
    echo $service->getXPaths($xmlData);
} catch (Exception $e) {
    echo "Extraction failed with following Reason:" . PHP_EOL .
        $e->getMessage();
}

