<?php
declare(strict_types=1);

namespace Xpathgenerator\Service;

use DOMDocument;
use DOMXPath;
use Exception;

/**
 * XPathExtractionService: Extracts XPaths and text content from XML data.
 *
 * Provides methods for transforming XML into a structured representation
 * of its elements, attributes, and corresponding XPaths. This is particularly
 * useful for tools that interact with XML data programmatically.
 */
class XPathExtractionService
{
    /**
     * Extracts XPaths, text content, and attributes from XML data.
     *
     * Parses the XML, identifies elements without children, and creates a nested structure
     * grouping related elements. Additionally, it extracts namespaces from the XML.
     *
     * @param string $xmlData The XML data to process
     * @return array A nested array representing the XML structure with XPaths, text, and attributes.
     * @throws Exception If the XML data is invalid.
     */
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
     * Generates the XPath expression for a given DOM element.
     *
     * @param \DOMNode $element The DOM element for which to generate the XPath.
     * @return string The XPath expression for the element.
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
     * Groups extracted results into a nested structure based on their XPaths.
     *
     * Transforms a flat array of XPaths, text, and attributes into a hierarchical
     * representation that mirrors the structure of the XML document.
     *
     * @param array $results The flat array of extracted results.
     * @return array The nested array representing the grouped results.
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

    /**
     * Extracts namespaces from the XML document.
     *
     * @param DOMXPath $xpath The DOMXPath object used to query the XML.
     * @return array An array of namespaces, where keys are prefixes and values are URIs.
     */
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



