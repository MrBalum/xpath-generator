<?php
declare(strict_types=1);

namespace Xpathgenerator\Service;

use DOMDocument;
use DOMXPath;
use Exception;

/**
 * This cla
 */
class XPathExtractionService
{
    public function getXPaths(string $xmlData): array
    {
        $dom = new DOMDocument();
        if (!$dom->loadXML($xmlData)) {
            throw new Exception('Cannot load xml, maybe it is broken');
        }

        // Extract namespaces
        $xpath = new DOMXPath($dom);
        $namespaces = $this->extractNamespaces($xpath);

        // Find all elements without child nodes
        $elements = $xpath->query('//*[not(*)]');

        $results = [];
        /** @var \DOMNode $element */
        foreach ($elements as $element) {
            $attributes = [];
            $xpath = $this->getXpath($element);
            foreach ($element->attributes as $attr) {
                $attributes[] = [
                    "name"=> $attr->name,
                    "value"=> $attr->nodeValue,
                    "xpath" => $xpath."/@".$attr->name
                ];
            }
            $results[] = [
                'xpath' => $xpath,
                'text' => $element->textContent,
                'attributes' => $attributes
            ];
        }

        // Group related elements in a tree-like structure
        $groupedResults = $this->groupResults($results);
        $groupedResults['@namespaces'] = $namespaces; // Add namespaces to the root

        return $groupedResults;
    }


    /**
     * @param \DOMNode $element
     * @return string
     */
    private function getXpath(\DOMNode $element): string
    {
        $xpath = '';
        for (; $element && $element->nodeType == XML_ELEMENT_NODE; $element = $element->parentNode) {
            $position = 1;
            foreach ($element->parentNode->childNodes as $sibling) {
                if ($sibling === $element) {
                    break;
                }
                if ($sibling->nodeName == $element->nodeName) {
                    $position++;
                }
            }

            $name = $element->nodeName;
            $xpath = '/' . $name . ($position >= 1 ? "[$position]" : '') . $xpath;
        }
        return $xpath;
    }


    /**
     * Groups related results into a tree-like structure
     *
     * @param array $results
     * @return array|mixed
     */
    public function groupResults(array $results): array
    {
        $groupedResults = [];

        // Iterate over all the results
        foreach ($results as $result) {
            // Split the XPath expression into its components
            $pathParts = explode('/', trim($result['xpath'], '/'));

            // Initialize the current group element as the top level
            $currentGroup = &$groupedResults;

            // Traverse all the components of the XPath expression
            foreach ($pathParts as $part) {
                $part = preg_replace("/(\[)1(])/","",$part);
                // If the component does not exist as a key in the current group, add it
                if (!isset($currentGroup[$part])) {
                    $currentGroup[$part] = [];
                }

                // Move to the next group
                $currentGroup = &$currentGroup[$part];
            }

            // Add the text content as the last element in the tree structure
            $currentGroup['value'] = $result['text'];
            $currentGroup['xpath'] = $result['xpath'];
            $currentGroup['attributes'] = $result['attributes'];
        }
        return $groupedResults;
    }

    private function extractNamespaces(DOMXPath $xpath): array
    {
        $namespaces = [];
        foreach ($xpath->query('namespace::*') as $nsNode) {
            if ($nsNode->localName === 'xml') {
                continue; // Skip the 'xml' namespace
            }
            $namespaces[$nsNode->localName] = $nsNode->nodeValue;
        }
        return $namespaces;
    }
}



