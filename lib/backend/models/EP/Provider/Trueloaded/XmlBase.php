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

namespace backend\models\EP\Provider\Trueloaded;

use backend\models\EP\Directory;
use backend\models\EP\Messages;
use backend\models\EP\Provider\ExportInterface;
use backend\models\EP\Provider\ImportInterface;
use backend\models\EP\Provider\ProviderAbstract;
use common\api\models\XML\IOAttachment;
use common\api\models\XML\IOCore;
use common\api\models\XML\IOData;
use common\api\models\XML\Project;
use common\api\models\XML\RelatedSerialize;
use common\api\models\XML\XMLtoDataParser;
use yii\db\ActiveQuery;
use yii\db\BatchQueryResult;

class XmlBase extends ProviderAbstract implements ImportInterface, ExportInterface
{
    /**
     * @var BatchQueryResult
     */
    protected $batchQuery;

    protected $processQueue = [];

    /**
     * @var RelatedSerialize
     */
    protected $serializer;
    /**
     * @var  XMLtoDataParser
     */
    protected $xmlParser;
    protected $ConfigureMap = [];

    private $firstWrite = true;

    /**
     * @var ActiveQuery
     */
    protected $activeQuery;
    protected $withImages = false;

    public $job_configure;

    public function init()
    {
        $this->firstWrite = true;

        $this->xmlParser = new XMLtoDataParser();
        $this->xmlParser->setConfigureMap($this->ConfigureMap);

        $this->serializer = new RelatedSerialize();
        $this->serializer->setConfigureMap($this->ConfigureMap);

        Project::checkLocalProjects();
        IOCore::get();
        if ( is_array($this->job_configure) && isset($this->job_configure['import']) ) {
            if ( !empty($this->job_configure['import']['projectCode']) ) {
                IOCore::get()->setProjectByCode($this->job_configure['import']['projectCode']);
            }
        }

        if ( $this->directoryObj ) {
            $this->setImagesDirectory($this->directoryObj->filesRoot());
        }

        \common\api\models\XML\Project::checkLocalProjects();
        $Data = $this->ConfigureMap['Data'];
        $collection = key($Data);

        $this->activeQuery = $collection::find()->where([]);

        if ( !empty($Data[$collection]['where']) ) {
            $this->activeQuery->andWhere($Data[$collection]['where']);
        }
        if ( !empty($Data[$collection]['orderBy']) ) {
            $this->activeQuery->orderBy($Data[$collection]['orderBy']);
        }

        parent::init();
    }

    public function setImagesDirectory($imagesFolder)
    {
        $this->import_folder = $imagesFolder;
        IOCore::get()->appendLocation('@attachment_root', $this->import_folder);

    }

    public function clearLocalData()
    {
        if (is_array($this->ConfigureMap['covered_tables'] ?? null)) {
            foreach ($this->ConfigureMap['covered_tables'] as $table) {
                tep_db_query('TRUNCATE TABLE ' . $table);
            }
        }
    }

    public function exchangeXml()
    {
        $rootConfig = current($this->ConfigureMap['Data']);
        list($rowsTag,$rowTag) = explode('>',$rootConfig['xmlCollection'],2);
        $header = $this->ConfigureMap['Header'];
        if ( !is_array($header) ) {
            $header = ['type'=>$header];
        }
        return [
            [
                'Header' => $header,
                'rowsTag' => $rowsTag,
                'rowTag' => $rowTag,
                'importData' => 'SimpleXml',
            ],
        ];
    }

    public function prepareExport($useColumns, $filter)
    {
        IOCore::get()->setProjectId(1);
        if ( is_array($filter) ) {
            if ( isset($filter['projectId']) && $filter['projectId'] > 0 ) {
                IOCore::get()->setProjectId((int)$filter['projectId']);
            }

            $this->withImages = ( isset($filter['with_images']) && $filter['with_images']);
            if ( $this->withImages ) {
                IOCore::get()->setAttachmentMode(['attach_file']);
            }
        }

        //echo $this->activeQuery->createCommand()->rawSql; die;

        $this->batchQuery = $this->activeQuery->each();
        $this->batchQuery->rewind();
    }

    public function exportRow()
    {
        $data = $this->batchQuery->current();
        if ( is_object($data) ) {
            $this->batchQuery->next();

            $collectionConfig = current($this->ConfigureMap['Data']);
            list($_dummy,$elementTag) = explode('>',$collectionConfig['xmlCollection'],2);

            $iodata = $this->serializer->exportModel($data, $collectionConfig);

            $writeData = array(
                ':xmlConfig' => array(),
                ':feed_data' => array(),
            );
            if ($this->firstWrite){
                $writeData[':xmlConfig'] = current($this->exchangeXml());
                if ( empty($writeData[':xmlConfig']['Header']['projectCode']) ) {
                    $writeData[':xmlConfig']['Header']['projectCode'] = IOCore::get()->getProjectCode();
                }
            }


            foreach ($iodata->getAttachmentList() as $IOAttachment){
                /**
                 * @var IOAttachment $IOAttachment
                 */
                if ( $file = $IOAttachment->getAttachmentFileName() ) {
                    if (!isset($writeData[':attachments'])) $writeData[':attachments'] = array();
                    $inArchiveName = 'images/' .  (( $IOAttachment->archiveFileName )?$IOAttachment->archiveFileName:$IOAttachment->value);
                    // {{ themes archive hack
                    if ( strpos($IOAttachment->value,'/')===0 ) {
                        $inArchiveName = 'images/' . substr($IOAttachment->value, strrpos($IOAttachment->value,'/'));
                    }
                    // }} themes archive hack
                    $writeData[':attachments'][] = array(
                        'filename' => $file,
                        'localname' => $inArchiveName,
                    );
                    $IOAttachment->attach_file = $inArchiveName;
                }
            }
            $writeData[':feed_data'][0] = IOData::serializeToSimpleXml($iodata, $elementTag);

            return $writeData;
        }
        return false;
    }

    public function importRow($data, Messages $message)
    {
        if ( !($data instanceof \SimpleXMLElement) ) return;

        $ioData = $this->xmlParser->makeIoData($data);

        $processModel = key($this->ConfigureMap['Data']);
        $this->serializer->importModel($processModel, $ioData, current($this->ConfigureMap['Data']));

    }

    public function postProcess(Messages $message)
    {

    }


}