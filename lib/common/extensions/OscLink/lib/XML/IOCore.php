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


use yii\base\InvalidParamException;
use yii\helpers\FileHelper;
use \common\helpers\Assert;

class IOCore
{
    public $project_id;

    protected $project_data;

    private $attributeMapper;

    private $typeClassMap = array();

    private $locations = array();
    private $attachmentModes = array();

    private $tablenamesWithMirrorIds = [];

    private function __construct()
    {
        $this->attributeMapper = new AttributeMapper();
        $project_id = 1;
        $this->setProjectId($project_id);

        $this->typeClassMap = array(
            'IOMap' => '\\OscLink\\XML\\IOMap',
            'IOCurrencyMap' => '\\OscLink\\XML\\IOCurrencyMap',
            'IOLanguageMap' => '\\OscLink\\XML\\IOLanguageMap',
            'IOPK' => '\\OscLink\\XML\\IOPK',
            'IOPlatformMap' => '\\OscLink\\XML\\IOPlatformMap',
            'IOAttachment' => '\\OscLink\\XML\\IOAttachment',
            'IOGalleryAttachment' => '\\OscLink\\XML\\IOGalleryAttachment',
            'IOCountryMap' => '\\OscLink\\XML\\IOCountryMap',
            'IOCountryZoneMap' => '\\OscLink\\XML\\IOCountryZoneMap',
            'IOOrderStatus' => '\\OscLink\\XML\\IOOrderStatus',
        );
        if ( class_exists('\Yii') ) {
            foreach ($this->typeClassMap as $shortName=>$fullName) {
                \Yii::$container->set($shortName, $fullName);
            }
        }

        $this->appendLocation(
            '@home',
            DIR_FS_CATALOG,
            \Yii::$app->get('platform')->config()->getCatalogBaseUrl()
        );
        $this->appendLocation(
            '@images',
            '@home/images'
        );
        $this->appendLocation(
            '@documents',
            '@home/documents'
        );
        $this->appendLocation(
            '@documents',
            '@home/documents'
        );
    }

    public function setProjectId($projectId)
    {
//        $this->project_id = $projectId;
//        $getProjectCode_r = tep_db_query("SELECT * FROM io_project WHERE project_id='".intval($this->project_id)."'");
//        if ( tep_db_num_rows($getProjectCode_r)>0 ) {
//            $this->project_data = tep_db_fetch_array($getProjectCode_r);
//        }
//        $this->attributeMapper->setProjectId($this->project_id);
    }

//    public function setProjectByCode($projectCode)
//    {
//        $getProjectId_r = tep_db_query("SELECT project_id FROM io_project WHERE project_code='".tep_db_input($projectCode)."'");
//        if ( tep_db_num_rows($getProjectId_r)>0 ) {
//            $projectIdArr = tep_db_fetch_array($getProjectId_r);
//            $this->setProjectId((int)$projectIdArr['project_id']);
//        }
//    }

    public function isLocalProject()
    {
//        if (is_array($this->project_data) ){
//            return !!$this->project_data['is_local'];
//        }
        return false;
    }

//    public function getProjectCode()
//    {
//        if (is_array($this->project_data) ){
//            return $this->project_data['project_code'];
//        }
//        return '';
//    }

    static public function get()
    {
        static $instance;
        if ( !is_object($instance) ) {
            $instance = new self();
        }
        return $instance;
    }

    public function getLookupTool()
    {
        static $objLookup = false;
        if ( !is_object($objLookup) ) {
            $objLookup = new IOLookup();
        }
        return $objLookup;
    }

//    public function getProjectList()
//    {
//        $projectList = [];
//        $getProjectId_r = tep_db_query("SELECT project_id, project_code FROM io_project WHERE 1 ORDER BY project_id");
//        if ( tep_db_num_rows($getProjectId_r)>0 ) {
//            while ($projectIdArr = tep_db_fetch_array($getProjectId_r)){
//                $projectList[ $projectIdArr['project_id'] ] = $projectIdArr['project_code'];
//            }
//        }
//        return $projectList;
//    }

    /**
     * @return AttributeMapper
     */
    public function getAttributeMapper()
    {
        return $this->attributeMapper;
    }

    public static function createObject($type, array $params = [])
    {
        $obj = self::get();

        if ( class_exists('\Yii') ) {
            return \Yii::createObject($type, $params);
        }else{
            if ( isset($obj->typeClassMap[$type]) ) {
                $className = $obj->typeClassMap[$type];
                $object = new $className;
                foreach ($params as $name => $value) {
                    $object->$name = $value;
                }
                return $object;
            }
        }
        return false;
    }

    public static function constructObjectInstance($objectArray, $params)
    {
        if (\Yii::$container->has($objectArray[0])) {
            $Definitions = \Yii::$container->getDefinitions();
            $fullClassName = $Definitions[$objectArray[0]]['class'];
            return call_user_func_array([$fullClassName,$objectArray[1]],$params);
        }
        return $params;
    }

    public static function getExportStructure($structure)
    {
        $configFN = dirname(__FILE__).'/structure/'.$structure.'.php';
        \common\helpers\Assert::assert(file_exists($configFN), 'Config file is not found: ' . $configFN);
        $config = include $configFN;
        $config['XSL'] = array(
            'export' => false,
            'import' => false,
        );
        $transformXSL = dirname(__FILE__).'/transform/export/'.$structure.'.xsl';
        if ( is_file($transformXSL) ) {
            $config['XSL']['export'] = $transformXSL;
        }
        $transformXSL = dirname(__FILE__).'/transform/import/'.$structure.'.xsl';
        if ( is_file($transformXSL) ) {
            $config['XSL']['import'] = $transformXSL;
        }
        return $config;
    }

    public function appendLocation($alias, $fileSystemPath, $urlPath='')
    {
        $this->locations[$alias] = array(
            'local' => rtrim($fileSystemPath,'/'),
            'public' => rtrim((empty($urlPath)?$fileSystemPath:$urlPath),'/'),
        );
    }

    public function getLocalLocation($path)
    {
        return $this->computeLocationValue($path, 'local');
    }

    public function getPublicLocation($path)
    {
        return $this->computeLocationValue($path, 'public');
    }

    protected function computeLocationValue($path, $target)
    {
        if ( substr($path,0,1)=='@' ) {
            $pos = strpos($path, '/');
            $root = $pos === false ? $path : substr($path, 0, $pos);
            if ( isset($this->locations[$root][$target]) ) {
                return $this->computeLocationValue($pos === false ? $this->locations[$root][$target] : $this->locations[$root][$target] . substr($path, $pos), $target);
            }elseif( class_exists('\Yii') ){
                return \Yii::getAlias($path,false);
            }
        }

        return $path;
    }

    /**
     * @return array
     */
    public function getAttachmentModes()
    {
        return array_values($this->attachmentModes);
    }

    public function isAttachmentModePresent($checkMode)
    {
        return isset($this->attachmentModes[$checkMode]);
    }

    /**
     * @param array|string $attachmentModes
     */
    public function setAttachmentMode($attachmentModes)
    {
        if ( !is_array($attachmentModes) ) $attachmentModes = array($attachmentModes);
        $IOAttachment = static::createObject('IOAttachment');
        /**
         * @var $IOAttachment IOAttachment
         */
        $knownAttachmentModes = $IOAttachment->getAttachmentModeVariants();
        $unknown = array_diff($attachmentModes,$knownAttachmentModes);
        if ( count($unknown)>0 ) {
            throw new InvalidParamException('Wrong mode "'.implode('", "',$unknown).'" Possible values for AttachmentModes is ['.implode(', ',$knownAttachmentModes).']');
        }
        $this->attachmentModes = array();
        foreach ($attachmentModes as $attachmentMode) {
            $this->attachmentModes[$attachmentMode] = $attachmentMode;
        }
    }

    public function normalizeLocalFileName($fn)
    {
        return str_replace(' ', '_', $fn);
    }

    public function download($sourceFile, &$physicalFile, $prefix = '')
    {
        $prefix = empty($prefix)? '': $prefix . ': ';
        $dir = dirname($physicalFile);
        try {
            if ( !is_dir($dir) ) {
                FileHelper::createDirectory($dir, 0777);
            }
            Assert::assertNotEmpty($sourceFile, 'Source file is empty');
            Assert::assertNotEmpty($physicalFile, 'Destination file is empty');
            $sourceFile = str_replace(' ', '%20', $sourceFile);
            $physicalFile = $this->normalizeLocalFileName($physicalFile);
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);
            Assert::assert(
                @copy($sourceFile, $physicalFile, $context),
                "Could not download file '$sourceFile': " . (error_get_last()['message'] ?? 'unknown reason')
            );

            if (!@chmod($physicalFile, 0666)) {
                \OscLink\Logger::print($prefix . 'warning: failed chmod: ' . (error_get_last()['message'] ?? 'unknown reason'));
            }
            return true;
        } catch (\Exception $e) {
            \OscLink\Logger::print($prefix . $e->getMessage());
            return false;
        }
    }

    /**
     * @param $tablenames string|array like 'products,orders' or ['products,orders']
     * @return void
     */
    public function setTablenamesWithMirrorIds($tablenames)
    {
        if (is_string($tablenames)) {
            \common\helpers\Assert::assert( strpos($tablenames, ' ') === false, 'Spaces are not allowed');
            $this->tablenamesWithMirrorIds = explode(',', $tablenames);
        } elseif (is_array($tablenames)) {
            $this->tablenamesWithMirrorIds = $tablenames;
        }
    }

    /**
     * @return array
     */
    public function getTablenamesWithMirrorIds()
    {
        return $this->tablenamesWithMirrorIds;
    }
}