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
        foreach ($elements as $element) {
            $results[] = [
                'xpath' => $this->getXpath($element),
                'text' => $element->textContent,
            ];
        }

        // Group related elements in a tree-like structure
        $groupedResults = $this->groupResults($results);
        $groupedResults['@namespaces'] = $namespaces; // Add namespaces to the root

        return $groupedResults;
    }


    /**
     * Generates an XPath expression for the given DOM element
     *
     * @param \DOMNode $element The DOM element to generate the XPath for
     * @return string The generated XPath expression
     */
    private function getXpath(\DOMNode $element): string
    {
        $xpath = '';

        // Generate the XPath
        for (; $element && $element->nodeType == XML_ELEMENT_NODE; $element = $element->parentNode) {
            $position = 1;
            $previous_sibling = $element->previousSibling;
            while ($previous_sibling) {
                if ($previous_sibling->nodeName == $element->nodeName) {
                    $position += 1;
                }
                $previous_sibling = $previous_sibling->previousSibling;
            }

            $name = $element->nodeName;
            if ($position > 1) {
                $name .= "[$position]";
            }

            $xpath = "/$name" . $xpath;
        }

        // Return the XPath
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



