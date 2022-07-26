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

namespace OscLink\XML;

class Project
{

    protected $structure;
    /**
     * @var RelatedSerialize
     */
    protected $Serializer;

    /**
     * IOProject constructor.
     * @param string $fileName
     */
    public function __construct($fileName = null)
    {
        $this->fileName = $fileName;
        $this->Serializer = new RelatedSerialize();
        static::checkLocalProjects();
    }

    public static function checkLocalProjects()
    {
    }

    public static function allocateCode($prefix)
    {
        return '1';
    }

    public static function createProject($projectCode, $extraData)
    {
        return 1;
    }

    public function setStructure($structure, $tuning)
    {
        $this->structure = $structure;
        $this->structure['importTuning'] = $tuning;
        $this->Serializer->setConfigureMap($this->structure);
    }


    public function detectStructure()
    {
        $detectedStructure = false;
        $xmlParser = new XMLtoArrayParser();
        $xmlParser->parseFile($this->fileName);
        $xmlParser->setCollectPath('/data/Header');
        $xmlHeader = $xmlParser->read();

        if ( is_array($xmlHeader) && !empty($xmlHeader['type']) ) {
            foreach (glob(dirname(__FILE__).'/structure/*.php') as $structureFile){
                $testArray = include($structureFile);
                if ( is_array($testArray) && isset($testArray['Header']) ) {
                    $checkHeader = $testArray['Header'];
                    if ( !is_array($checkHeader) ) $checkHeader = array('type'=>$checkHeader);
                    if ( $checkHeader['type'] == $xmlHeader['type'] ) {
                        $detectedStructure = pathinfo($structureFile, PATHINFO_FILENAME);
                        break;
                    }
                }
            }
        }

        return $detectedStructure;
    }

    public function export()
    {
        $writer = new XMLWriter($this->fileName);
        if ( isset($this->structure['XSL']) && is_array($this->structure['XSL']) && !empty($this->structure['XSL']['export']) ) {
            if ( is_file($this->structure['XSL']['export']) ) {
                $writer->applyXSLT($this->structure['XSL']['export']);
            }
        }
        $this->Serializer->export($writer);
    }

    public function import()
    {
        return $this->Serializer->import($this->fileName);
    }

    public function clean()
    {
        return $this->Serializer->clean();
    }

}