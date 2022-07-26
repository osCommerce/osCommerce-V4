<?php

/**
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 *
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 *
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\api\Xml;

/**
 * Responsible for parsing XML and returning a PHP object.
 */
class XmlParser
{
    /**
     * @var mixed
     */
    private $rootObject;

    /**
     * @var array
     */
    private $currentItem = [];
    private $NameNum = 0;
    
    /**
     * Parse the passed XML
     * 
     * @param object $rootObject
     * @param string $xml The xml string to parse.
     * @return mixed A PHP object
     */
    public function parse($rootObject, $xml)
    {
        $this->rootObject = $rootObject;
        $this->currentItem = [];
        $this->NameNum = 0;
                
        $parser = xml_parser_create_ns('UTF-8', '@');

        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_set_object($parser, $this);
        xml_set_element_handler($parser, 'startElement', 'endElement');
        xml_set_character_data_handler($parser, 'cdata');

        xml_parse($parser, $xml, true);

        xml_parser_free($parser);

        return $this->rootObject;
    }

    /**
     * Handler for the parser that is called at the start of each XML element.
     *
     * @param resource $parser Reference to the XML parser calling the handler.
     * @param string $name The name of the element.
     * @param array $attributes Associative array of the element's attributes.
     */
    private function startElement($parser, $name, array $attributes)
    {
        $class = get_class($this->rootObject);
        if (property_exists($class, $name)) {
            $this->currentItem[] = $name;
        } elseif (count($this->currentItem) > 0) {
            if ($name == 'item') {
                $this->NameNum++;
                $name = $this->NameNum;
            }
            $this->currentItem[] = $name;
        }
    }

    /**
     * Handler for the parser that is called for character data.
     *
     * @param resource $parser Reference to the XML parser calling the handler.
     * @param string $cdata The character data.
     */
    private function cdata($parser, $cdata)
    {
        if (isset($this->currentItem[0])) {
            $class = get_class($this->rootObject);
            if (property_exists($class, $this->currentItem[0])) {
                if (count($this->currentItem) == 1) {
                    $this->rootObject->{$this->currentItem[0]} = $cdata;
                } else {
                    $deep =& $this->rootObject->{$this->currentItem[0]};
                    foreach ($this->currentItem as $key => $value) {
                        if ($key == 0) {
                            continue;
                        }
                        $deep =& $deep[$value];
                    }
                    $deep .= $cdata;
                }
            }
        }
    }

    /**
     * Handler for the parser that is called at the end of each XML element.
     *
     * @param resource $parser Reference to the XML parser calling the handler.
     * @param string $name The name of the element.
     */
    private function endElement($parser, $name)
    {
        if (count($this->currentItem) > 0) {
            array_pop($this->currentItem);
        }
    }
   
}
