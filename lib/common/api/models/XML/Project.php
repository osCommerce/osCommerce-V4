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

namespace common\api\models\XML;

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
    public function __construct($fileName)
    {
        $this->fileName = $fileName;
        $this->Serializer = new RelatedSerialize();
        static::checkLocalProjects();
    }

    public static function checkLocalProjects()
    {
        $checkPrimaryProject = tep_db_fetch_array(tep_db_query(
            "SELECT COUNT(*) AS c FROM io_project WHERE is_local=1 AND department_id=0 AND platform_id=0"
        ));
        if ( $checkPrimaryProject['c']==0 ) {
            $project_code = static::allocateCode(defined('STORE_NAME')?STORE_NAME:\Yii::$app->name);
            static::createProject($project_code, [
                'is_local' => 1,
                'department_id' => 0,
                'platform_id' => 0,
            ]);
        }
    }

    public static function allocateCode($prefix)
    {
        do {
            $getServerUuid = tep_db_fetch_array(tep_db_query("SELECT HEX(UUID_SHORT()) AS short_u"));
            $prefixT = substr(strtoupper(preg_replace('/[^\da-z]/i', '', $prefix)), 0, 11) . '_' .$getServerUuid['short_u'] . date('ymd');
            $checkUniq = tep_db_fetch_array(tep_db_query(
                "SELECT COUNT(*) AS c FROM io_project WHERE project_code='".tep_db_input($prefixT)."'"
            ));
        }while($checkUniq['c']>0);

        return $prefixT;
    }

    public static function createProject($projectCode, $extraData)
    {
        $data = array(
            'project_code' => $projectCode,
        );
        if ( is_array($extraData) ) {
            $data = array_merge($data, $extraData);
        }
        tep_db_perform('io_project', $data);
        return tep_db_insert_id();
    }

    public function setStructure($structure)
    {
        $this->structure = $structure;
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
        $this->Serializer->import($this->fileName);
    }

}