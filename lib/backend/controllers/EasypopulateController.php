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

namespace backend\controllers;

use common\api\models\XML\IOCore;
use common\classes\platform;
use Yii;
use yii\helpers\Html;
use yii\helpers\FileHelper;
use backend\models\EP;
use yii\i18n\Formatter;


class EasypopulateController extends Sceleton
{

    public $acl = ['BOX_HEADING_CATALOG', 'BOX_CATALOG_EASYPOPULATE'];

    public $import_folder = 'import';

    /**
     *
     * @var EP/Directory
     */
    public $currentDirectory;
    public $selectedRootDirectoryId;

    public function __construct($id, $module = null){
        parent::__construct($id, $module);

        $request_directory = Yii::$app->request->post('directory_id');
        if ( empty($request_directory) ){
            $request_directory = Yii::$app->request->get('directory_id',1);
        }
        foreach( EP\Directory::getAll() as $Directory ) {
            if ( empty($this->currentDirectory) ) {
                $this->currentDirectory = $Directory;
                $this->selectedRootDirectoryId = $Directory->directory_id;
            }
            if ( $request_directory==$Directory->directory_id ) {
                $this->currentDirectory = $Directory;
                $this->selectedRootDirectoryId = $Directory->directory_id;
                break;
            }
        }
        if ( $this->currentDirectory->parent_id ) {
            $walkDirectory = $this->currentDirectory;
            while ( $walkDirectory = $walkDirectory->getParent()) {
                $this->selectedRootDirectoryId = $walkDirectory->directory_id;
            }
        }
        \common\helpers\Translation::init('admin/easypopulate');
    }


    public function actionIndex()
    {
        $messageStack = \Yii::$container->get('message_stack');

        $this->selectedMenu       = array( 'catalog', 'easypopulate' );
        $this->navigation[]       = array( 'link' => Yii::$app->urlManager->createUrl( 'easypopulate/index' ), 'title' => EP_HEDING_TITLE );
        if ( $this->selectedRootDirectoryId==5 && count(EP\DataSources::getAvailableList())>0 ) {
            $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl(['easypopulate/create-data-source']) . '" class="create_item js-create-datasource"><i class="icon-file-text"></i>' . 'Create data source' . '</a>';
        }
        $this->view->headingTitle = EP_HEDING_TITLE;

        if ( Yii::$app->request->isPost ) {
            $datasource = tep_db_prepare_input(Yii::$app->request->post('datasource'));

            foreach ( $datasource as $dsKey=>$dsSettings ) {
                if ( !class_exists('backend\\models\\EP\\Datasource\\'.$dsKey) ) continue;
                $dsSettings = call_user_func_array(
                    ['backend\\models\\EP\\Datasource\\'.$dsKey, 'beforeSettingSave'],
                    [$dsSettings]
                );

                $settings = json_encode($dsSettings);

                tep_db_query(
                    "INSERT INTO ep_datasources (code, settings) ".
                    "VALUES ".
                    " ('".tep_db_input($dsKey)."', '".tep_db_input($settings)."') ".
                    "ON DUPLICATE KEY UPDATE settings='".tep_db_input($settings)."'"
                );
            }
            $this->redirect( Yii::$app->urlManager->createUrl(['easypopulate/index']+Yii::$app->request->get()) );
        }

        /*
        $warn_products   = '';
        $warn_caregories = '';
        $office_limit    = 31998;
        $tpd_r           = tep_db_query( "SELECT max(length(products_description)) as pd_len, max(length(products_head_desc_tag)) as hd_len, max(length(products_head_keywords_tag )) as hk_len FROM " . TABLE_PRODUCTS_DESCRIPTION );
        $tpd_a           = tep_db_fetch_array( $tpd_r );
        foreach( $tpd_a as $col => $max_length ) {
            if( (int) $max_length > (int) $office_limit ) $warn_products = TEXT_WARN_LONGTEXT_EDIT;
        }
        $tpd_r = tep_db_query( "SELECT max(length(categories_description)) as cd_len, max(length(categories_head_desc_tag)) as hd_len, max(length(categories_head_keywords_tag)) as hk_len FROM " . TABLE_CATEGORIES_DESCRIPTION );
        $tpd_a = tep_db_fetch_array( $tpd_r );
        foreach( $tpd_a as $col => $max_length ) {
            if( (int) $max_length > (int) $office_limit ) $warn_caregories = TEXT_WARN_LONGTEXT_EDIT;
        }
        */
        if ( !is_dir(Yii::getAlias('@ep_files')) ) {
            $messageStack->add(sprintf(ERROR_DATA_DIRECTORY_MISSING, Yii::getAlias('@ep_files')));
        }elseif( !is_writeable(Yii::getAlias('@ep_files')) ){
            $messageStack->add(sprintf(ERROR_DATA_DIRECTORY_NOT_WRITEABLE, Yii::getAlias('@ep_files')));
        }
        $this->view->importFolder = $this->currentDirectory->filesRoot(EP\Directory::TYPE_IMAGES);
        if (!file_exists($this->view->importFolder)) {
            @mkdir($this->view->importFolder, 0777, true);
        }

        \common\helpers\Translation::init('admin/categories');
        $message_stack_output = '';
        if ($messageStack->size() > 0) {
          $message_stack_output = $messageStack->output();
        }

        $providers = new EP\Providers();

        $importProviders = $providers->pullDownVariants('Import', [
            'items'=>['' => TEXT_OPTION_AUTO,],
            'options' => [ 'class'=> 'form-control' ],
        ]);

        $export_options = $providers->pullDownVariants('Export',[
            'selection' => '',
            'items' => [
                '' => PULL_DOWN_DEFAULT,
            ],
            'options' => [
                'class' => 'form-control',
                'required' => "true",
                'options' => [
                ],
            ],
        ]);

        $download_format_down_data = [
            'selection' => 'CSV',
            'items' => [
                '' => PULL_DOWN_DEFAULT,
                'CSV' => TEXT_OPTION_EXPORT_CSV,
                'XLSX' => 'XLSX',
                'ZIP' => TEXT_OPTION_EXPORT_ZIP,
                'XML_orders_new' => TEXT_OPTION_EXPORT_ORDERS_NEW_XML,
                'XML' => TEXT_OPTION_EXPORT_ORDERS_NEW_XML,
                'XML-ZIP' => TEXT_OPTION_EXPORT_ORDERS_NEW_XML.' '.TEXT_OPTION_EXPORT_ZIP,
            ]
        ];

        
        $directories = [];
        foreach( EP\Directory::getAllRoots() as $Directory ) {
            if ( $Directory->parent_id!=0 || empty($Directory->name) ) continue;
            /**
             * @var EP\Directory $Directory
             */
            $directories[] = [
                'id' => $Directory->directory_id,
                'text' =>  $Directory->name,
                'link' => Yii::$app->urlManager->createUrl(['easypopulate/','directory_id'=>$Directory->directory_id]),
            ];
        }

        $order_year_start = $order_year_end = date('Y');
        $order_start_from_r = tep_db_query("SELECT MIN(YEAR(date_purchased)) AS min_year FROM ".TABLE_ORDERS);
        if ( tep_db_num_rows($order_start_from_r)>0 ) {
            $order_start_from = tep_db_fetch_array($order_start_from_r);
            $order_year_start = $order_start_from['min_year'];
        }
        $order_year_range = [];
        for( $i=$order_year_start; $i<=$order_year_end; $i++ ) {
            $order_year_range[(int)$i] = (int)$i;
        }
        $order_month_range = array_map(function($i){
            return $i==0?TEXT_ALL:sprintf('%02s',$i);
        },range(0,12));

        $filter_defaults = [
            'project' => [
                'value' => '',
                'items' => [''=>'']+IOCore::get()->getProjectList(),
            ],
            'order' => [
                'date_type_range' => [
                    'value' => 'presel',
                ],
                'year' => [
                    'value' => date('Y'),
                    'items' => $order_year_range,
                ],
                'month' => [
                    'items' => $order_month_range,
                    'value' => '',
                ],
                'interval' =>[
                    'value' => '',
                    'items' => [
                        '' => TEXT_ALL,
                        '1' => TEXT_TODAY,
                        'week' => TEXT_WEEK,
                        'month' => TEXT_THIS_MONTH,
                        'year' => TEXT_THIS_YEAR,
                        '3' => TEXT_LAST_THREE_DAYS,
                        '7' => TEXT_LAST_SEVEN_DAYS,
                        '14' => TEXT_LAST_FOURTEEN_DAYS,
                        '30' => TEXT_LAST_THIRTY_DAYS,
                    ],
                ],
            ]
        ];

        $view_data = array(
            'current_directory_id' => $this->currentDirectory->directory_id,
            'currentDirectory' => $this->currentDirectory,
            'selectedRootDirectoryId' => $this->selectedRootDirectoryId,
            'directories' => $directories,
            'message_stack_output' => $message_stack_output,
            'show_data_management' => true,
            'show_export_page' => $this->currentDirectory->directory_type == EP\Directory::TYPE_EXPORT,
            'show_import_page' => $this->currentDirectory->directory_type == EP\Directory::TYPE_IMPORT,
            'importProviders' => $importProviders,
            'selected_type' => Yii::$app->request->get('file_type', ''),
            'export_options' => $export_options,
            'easypopulate_command_action' => tep_href_link( FILENAME_EASYPOPULATE . '/command'),
            'upload_form_action_ajax' => Yii::$app->urlManager->createUrl(['easypopulate/upload-file-ajax','directory_id'=>$this->currentDirectory->directory_id]),
            'job_list_url' => Yii::$app->urlManager->createUrl(['easypopulate/files-list']),
            'get_job_messages_popup_action' => Yii::$app->urlManager->createUrl(['easypopulate/job-log-messages','directory_id'=>$this->currentDirectory->directory_id]),
            'upload_max_part_size' => 900*1024,
            'download_format_down_data' => $download_format_down_data,
            'download_form_action' => Yii::$app->urlManager->createUrl(['easypopulate/process-export','directory_id'=>$this->currentDirectory->directory_id]),
            'get_fields_action' => tep_href_link( FILENAME_EASYPOPULATE . '/get-fields'),
            'refresh_filter_action' => tep_href_link( FILENAME_EASYPOPULATE . '/refresh-filters'),
            //'select_filter_categories' => tep_draw_pull_down_menu('filter[category_id]', \common\helpers\Categories::get_category_tree(0,'','','',false,true), 0, ''),
            'select_filter_categories_auto_complete_url' => \Yii::$app->urlManager->createUrl(['easypopulate/get-categories-list']),
            'select_filter_products_auto_complete_url' => \Yii::$app->urlManager->createUrl(['easypopulate/get-products-list']),
            'select_filter_properties' => tep_draw_pull_down_menu('filter[properties_id]', \common\helpers\Properties::get_properties_tree(0,'','',false), 0, ''),
            'filter_defaults' => $filter_defaults,
            'dataSourcesHref' => Yii::$app->urlManager->createUrl(['easypopulate/','datasources'=>'']),
            'select_filter_platform_variants' => array_map(function ($item) { return ['id'=>$item['id'], 'value'=>$item['text']]; }, array_merge(array(array('id'=>'0','text'=>TEXT_ALL)),\common\classes\platform::getList(true,true))),
            'select_filter_platform_variants' => array_map(function ($item) { return ['id'=>$item['id'], 'value'=>$item['text']]; }, array_merge(array(array('id'=>'0','text'=>TEXT_ALL)),platform::getList(true,true))),
            'js_messages' => json_encode([
                'file_changed' => TEXT_FILE_CHANGED,
                'file_upload' => TEXT_FILE_UPLOAD,
                'file_uploaded' => TEXT_FILE_UPLOADED,
            ]),
            'datasource_run_form_action' => Yii::$app->urlManager->createUrl(['easypopulate/run-data-source','directory_id'=>$this->currentDirectory->directory_id]),
        );

        return $this->render( 'index', $view_data );
    }

    public function actionCreateDirectory()
    {
        $this->layout = false;

        if ( Yii::$app->request->isPost ) {

        }else{
            $directoryTypeVariants = [
                'import' => 'Import',
                'export' => 'Export',
            ];
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = [
                'dialog' => [
                    'title'=>'Job messages',
                    'message' => $this->render('create-directory',['directoryTypeVariants'=>$directoryTypeVariants]),
                    'buttons' => [
                        'cancel' => [
                            'label' => TEXT_OK,
                            'className' => 'btn-primary',
                        ]
                    ]
                ]
            ];
        }

    }

    public function actionCreateDataSource()
    {
        $this->layout = false;

        if ( Yii::$app->request->isPost ) {
            $new_datasource = Yii::$app->request->post('new_datasource');

            EP\DataSources::add($new_datasource);

        }else{
            $availableSources = [];
            foreach (EP\DataSources::getAvailableList() as $ds){
                $availableSources[$ds['class']] = $ds['name'];
            }

            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = [
                'dialog' => [
                    'title'=>'Create data source',
                    'message' => $this->render('create-data-source', ['availableSourcesVariants'=>$availableSources]),
                    'buttons' => [
                        'confirm' => [
                            'label' => TEXT_OK,
                            'className' => 'btn-primary',
                        ]
                    ]
                ]
            ];
        }
    }

    public function actionConfigureAutoDatasourceDirectory()
    {
        $this->layout = false;

        $id = Yii::$app->request->get('by_id',0);
        $id = Yii::$app->request->post('by_id',$id);
        $id = (int)$id;

        $directory = EP\Directory::loadById($id);
        if ( !$directory){
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = [
                'dialog' => [
                    'title'=> TEXT_DIRECTORY_CONFIGURE,
                    'message' => 'Directory not found',
                ]
            ];
            return;
        }
        try{
            $dataSourceObj = EP\DataSources::getByName($directory->directory);
        } catch (\Exception $ex){
            \Yii::warning('Datasource not found ' . $directory->directory );
        }
        if ( !$dataSourceObj ) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = [
                'dialog' => [
                    'title'=> TEXT_DIRECTORY_CONFIGURE,
                    'message' => 'Datasource not found',
                ]
            ];
            return;
        }
        $dataSourceClass = substr(get_class($dataSourceObj), strrpos(get_class($dataSourceObj),'\\')+1);

        $providers = new EP\Providers();
        $providersList = $providers->pullDownVariants('Datasource',[], $dataSourceClass);

        $formatReaders = [
            'selection' => '',
            'items' => [
                //'' => PULL_DOWN_DEFAULT,
            ]
        ];

        foreach( EP\DataSources::getAvailableList() as $dataSource){
            if ( $dataSourceClass != $dataSource['class'] ) continue;
            $formatReaders['items'][$dataSource['class']] = $dataSource['name'];
        }
        $launchFrequency = [
            'selection' => '-1',
            'items' => [
                -1 => TEXT_DISABLED,
                1 => TEXT_IMMEDIATELY,
                0 => TEXT_DEFINED_TIME,
                5 => TEXT_EVERY_5_MINUTES,
                15 => TEXT_EVERY_15_MINUTES,
                30 => TEXT_EVERY_30_MINUTES,
                60 => TEXT_EVERY_HOUR,
                120 => sprintf(TEXT_NN_HOURS, 2),
                180 => sprintf(TEXT_NN_HOURS, 3),
                240 => sprintf(TEXT_NN_HOURS, 4),
                300 => sprintf(TEXT_NN_HOURS, 5),
                360 => sprintf(TEXT_NN_HOURS, 6),
                720 => sprintf(TEXT_NN_HOURS, 12),
                1440 => TEXT_EVERY_DAY,
            ]
        ];

        if ( Yii::$app->request->isPost ) {
            $directory_config_input = tep_db_prepare_input(Yii::$app->request->post('directory_config', []));
            $directory_config = [];
            foreach($directory_config_input as $directory_file_config){
                if ( empty($directory_file_config['filename_pattern']) ) {
                    $directory_file_config['filename_pattern'] = str_replace('\\','_',$directory_file_config['job_provider']).'_'.rand(1000,9999);
                }
                $directory_file_config['run_time'] = date('H:i',strtotime('2000-01-01 '.($directory_file_config['run_time']??null)));
                $directory_config[] = $directory_file_config;
            }
            $directory->directory_config = $directory_config;

            tep_db_query(
                "UPDATE ".TABLE_EP_DIRECTORIES." ".
                "SET directory_config='".tep_db_input(json_encode($directory_config))."' ".
                "WHERE directory_id='".(int)$id."'"
            );
            $directory->applyDirectoryConfig();

            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = ['status'=>'ok'];
        }else{
            $directoryConfigs = [];
            foreach($directory->directory_config as $directory_config){
                $directory_config['run_time'] = date('g:i A',strtotime('2000-01-01 '.$directory_config['run_time']));
                $directoryConfigs[] = $directory_config;
            }

            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = [
                'dialog' => [
                    'title' => TEXT_DIRECTORY_CONFIGURE,
                    'message' => $this->render('configure-auto-datasource-directory',[
                        'directoryConfigs' => $directoryConfigs,
                        'providersList' => $providersList,
                        'formatReaders' => $formatReaders,
                        'launchFrequency' => $launchFrequency,
                        'runTimeDefault' => date('g:i A',strtotime('+2 minutes')),
                    ])
                ]
            ];
        }
    }

    public function actionConfigureDatasourceSettings()
    {
        $this->layout = false;

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $id = Yii::$app->request->get('by_id',0);
        $id = Yii::$app->request->post('by_id',$id);
        $id = (int)$id;
        $directory = EP\Directory::loadById($id);
        if (is_object($directory)) {
            $ds = EP\DataSources::getByName($directory->directory);
            if (is_object($ds)){
                if (Yii::$app->request->isPost){
                    $datasource = Yii::$app->request->post('datasource',[]);
                    try {
                        $ds->update(isset($datasource[$ds->code]) ? $datasource[$ds->code] : []);
                        Yii::$app->response->data = ['result'=>'ok'];
                    }catch (\InvalidArgumentException $ex) {
                        Yii::$app->response->data = ['result'=>'error','message'=>$ex->getMessage()];
                    }
                    return;
                }
                Yii::$app->response->data = [
                    'dialog' => [
                        'title'=> 'Datasource "'.$directory->directory.'" configure',
                        'message' => '<form id="frmDatasourceConfig"><input type="hidden" name="by_id" value="'.$id.'">'.call_user_func_array([$this, 'render'], $ds->configureView()).'</form>',
                    ]
                ];
                return;
            }
        }
        Yii::$app->response->data = [
            'dialog' => [
                'error'=> 'true',
                'message' => 'Datasource not found',
            ]
        ];
    }

    public function actionConfigureAutoImportDirectory()
    {
        $this->layout = false;

        $id = Yii::$app->request->get('by_id',0);
        $id = Yii::$app->request->post('by_id',$id);
        $id = (int)$id;

        $directory = EP\Directory::loadById($id);
        if ( !$directory ){
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = [
                'dialog' => [
                    'title'=> TEXT_DIRECTORY_CONFIGURE,
                    'message' => 'Directory not found',
                ]
            ];
        }

        $providers = new EP\Providers();
        $providersList = $providers->pullDownVariants('Import');

        $formatReaders = [
            'selection' => 'CSV',
            'items' => [
                //'' => PULL_DOWN_DEFAULT,
                'CSV' => TEXT_OPTION_EXPORT_CSV,
                'XLSX' => 'XLSX',
                'ZIP' => TEXT_OPTION_EXPORT_ZIP,
                'XML_orders_new' => TEXT_OPTION_EXPORT_ORDERS_NEW_XML,
            ]
        ];

        $launchFrequency = [
            'selection' => '-1',
            'items' => [
                -1 => TEXT_DISABLED,
                1 => TEXT_IMMEDIATELY,
                0 => TEXT_DEFINED_TIME,
                5 => TEXT_EVERY_5_MINUTES,
                15 => TEXT_EVERY_15_MINUTES,
                30 => TEXT_EVERY_30_MINUTES,
                60 => TEXT_EVERY_HOUR,
                120 => sprintf(TEXT_NN_HOURS, 2),
                180 => sprintf(TEXT_NN_HOURS, 3),
                240 => sprintf(TEXT_NN_HOURS, 4),
                300 => sprintf(TEXT_NN_HOURS, 5),
                360 => sprintf(TEXT_NN_HOURS, 6),
                720 => sprintf(TEXT_NN_HOURS, 12),
                1440 => TEXT_EVERY_DAY,
            ]
        ];

        if ( Yii::$app->request->isPost ) {
            $directory_config_input = tep_db_prepare_input(Yii::$app->request->post('directory_config', []));
            $directory_config = [];
            foreach($directory_config_input as $directory_file_config){
                if ( empty($directory_file_config['filename_pattern']) ) continue;
                $directory_file_config['run_time'] = date('H:i',strtotime('2000-01-01 '.$directory_file_config['run_time']));
                $directory_config[] = $directory_file_config;
            }
            $directory->directory_config = $directory_config;

            tep_db_query(
                "UPDATE ".TABLE_EP_DIRECTORIES." ".
                "SET directory_config='".tep_db_input(json_encode($directory_config))."' ".
                "WHERE directory_id='".(int)$id."'"
            );
            $directory->applyDirectoryConfig();

            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = ['status'=>'ok'];
        }else{
            $directoryConfigs = [];
            foreach($directory->directory_config as $directory_config){
                $directory_config['run_time'] = date('g:i A',strtotime('2000-01-01 '.$directory_config['run_time']));
                $directoryConfigs[] = $directory_config;
            }

            $directoryFilesSuggest = [];
            $get_files_r = tep_db_query(
                "SELECT DISTINCT file_name ".
                "FROM ".TABLE_EP_JOB." ".
                "WHERE directory_id='".$directory->directory_id."'"
            );
            if ( tep_db_num_rows($get_files_r)>0 ) {
                while( $get_file = tep_db_fetch_array($get_files_r) ){
                    $directoryFilesSuggest[$get_file['file_name']] = $get_file['file_name'];
                    $masked = preg_replace('/\d+/','*',$get_file['file_name']);
                    $directoryFilesSuggest[$masked] = $masked;
                    $masked2 = preg_replace('/\*.*\*/','*',$masked);
                    $directoryFilesSuggest[$masked2] = $masked2;
                }
            }

            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = [
                'dialog' => [
                    'title' => TEXT_DIRECTORY_CONFIGURE,
                    'message' => $this->render('configure-auto-import-directory',[
                        'directoryFilesSuggestSource' => implode(':',$directoryFilesSuggest),
                        'directoryConfigs' => $directoryConfigs,
                        'providersList' => $providersList,
                        'formatReaders' => $formatReaders,
                        'launchFrequency' => $launchFrequency,
                        'runTimeDefault' => date('g:i A',strtotime('+2 minutes')),
                    ])
                ]
            ];
        }
    }

    public function actionConfigureAutoProcessedDirectory()
    {
        $this->layout = false;

        $id = Yii::$app->request->get('by_id',0);
        $id = Yii::$app->request->post('by_id',$id);
        $id = (int)$id;

        $directory = EP\Directory::loadById($id);
        if ( !$directory ){
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = [
                'dialog' => [
                    'title'=> TEXT_DIRECTORY_CONFIGURE,
                    'message' => 'Directory not found',
                ]
            ];
        }

        $cleaningTerm = [
            'selection' => '-1',
            'items' => [
                -1 => TEXT_DISABLE_REMOVAL,
                '1 day' => TEXT_KEEP_1_DAY,
                '1 week' => TEXT_KEEP_1_WEEK,
                '2 week' => TEXT_KEEP_2_WEEKS,
                '1 month' => TEXT_KEEP_1_MONTH,
            ]
        ];

        if ( Yii::$app->request->isPost ) {
            $directory->directory_config = tep_db_prepare_input(Yii::$app->request->post('directory_config', []));

            tep_db_query(
                "UPDATE ".TABLE_EP_DIRECTORIES." ".
                "SET directory_config='".tep_db_input(json_encode($directory->directory_config))."' ".
                "WHERE directory_id='".(int)$id."'"
            );
            $directory->applyDirectoryConfig();

            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = ['status'=>'ok'];
        }else{
            $directoryConfigs = $directory->directory_config;
            if ( !isset($directoryConfigs['cleaning_term']) ) $directoryConfigs['cleaning_term'] = '-1';

            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = [
                'dialog' => [
                    'title'=>'Configure directory',
                    'message' => $this->render('configure-auto-processed-directory',[
                        'directoryConfigs' => $directoryConfigs,
                        'cleaningTerm' => $cleaningTerm,
                    ])
                ]
            ];
        }
    }

    public function actionRefreshFilters()
    {
      $this->layout = false;
      $data = array(
        //'select_filter_categories' => tep_draw_pull_down_menu('filter[category_id]', \common\helpers\Categories::get_category_tree(0,'','','',false,true), 0, ''),
        'select_filter_properties' => tep_draw_pull_down_menu('filter[properties_id]', \common\helpers\Properties::get_properties_tree(0,'','',false), 0, ''),
      );
      Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      Yii::$app->response->data = $data;
    }

    public function actionGetCategoriesList()
    {
      $this->layout = false;

      $all_data = \common\helpers\Categories::get_category_tree(0,'','','',false,true);
      $data = array_filter($all_data,function($option){
        $search_term = \Yii::$app->request->get('term','');
        $search_term = tep_db_prepare_input($search_term);
        $option_value = html_entity_decode($option['text'], ENT_HTML5, 'UTF-8');
        return preg_match('/'.preg_quote($search_term,'/').'/is', $option_value) || $option_value==$search_term;
      });
      $data = array_map(function($option){
        $option['value'] = html_entity_decode($option['text'], ENT_HTML5, 'UTF-8');
        $option['text'] = $option['value'];
        return $option;
      },$data);

      Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      Yii::$app->response->data = $data;
    }

    public function actionGetProductsList()
    {
        $this->layout = false;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        \Yii::$app->response->data = [];

        $languages_id = \Yii::$app->settings->get('languages_id');
        $search = Yii::$app->request->get('term', null);
        $exclude_pids = (array)Yii::$app->request->get('exclude_pids', []);
        $exclude_pids = array_map('intval', $exclude_pids);

        if (!empty($search)) {
            //$catalog = new \backend\components\ProductsCatalog();
            //$catalog->post['suggest'] = 1;
            //return $catalog->search($search);

            $pQ = (new \yii\db\Query())
                ->select("p.products_id, p.products_status, p.products_model ")
                ->addSelect(['products_name' => (new \yii\db\Expression(\backend\models\ProductNameDecorator::instance()->listingQueryExpression('pd', '')))])
                ->from(['p' => TABLE_PRODUCTS])
                ->leftJoin(TABLE_PRODUCTS_DESCRIPTION . " pd",
                    'p.products_id = pd.products_id and pd.language_id =:lid and pd.platform_id = :pid',
                    [':lid' => (int)$languages_id, ':pid' => intval(\common\classes\platform::defaultId())])
                ->andFilterWhere(['NOT IN', 'p.products_id', $exclude_pids])
                ->distinct()
                ->orderBy("p.sort_order ")
                ->addOrderBy(new \yii\db\Expression(\backend\models\ProductNameDecorator::instance()->listingQueryExpression('pd', '')))
                ->limit(100);
            $pQ->andWhere([
                'or',
                ['like', "p.products_model", tep_db_input($search)],
                ['like', "pd.products_name", tep_db_input($search)],
                ['like', "pd.products_internal_name", tep_db_input($search)]
            ]);

            $productsAll = $pQ->all();

            if (is_array($productsAll) && !empty($productsAll)) {
                foreach ($productsAll as $products) {
                    Yii::$app->response->data[] = [
                        'id' => $products['products_id'],
                        'text' => $products['products_name'],
                        'model' => $products['products_model'],
                        'status' => $products['products_status'],
                    ];
                }
            }
        }
    }

    public function actionExportColumns()
    {
        $this->layout = false;
        $job_id = Yii::$app->request->get('by_id', 0);
        $job_id = Yii::$app->request->post('by_id', $job_id);

        if ( Yii::$app->request->isPost ) {
            $selected_columns = tep_db_prepare_input(Yii::$app->request->post( 'selected_fields', '' ));
            if ( !empty($selected_columns) ) {
                $selected_columns = explode(',',$selected_columns);
            }else{
                $selected_columns = false;
            }

            if ( $job_id ) {
                $job = EP\Job::loadById($job_id);
                if ( $job ) {
                    $job->job_configure['export']['columns'] = $selected_columns;
                    tep_db_query("UPDATE ".TABLE_EP_JOB." SET job_configure='".tep_db_input(json_encode($job->job_configure))."' WHERE job_id='".(int)$job->job_id."' ");
                }
            }
        }
        die;
    }

    public function actionGetFields()
    {
        $this->layout = false;

        $selected = false;
        $export_provider = tep_db_prepare_input(Yii::$app->request->post('export_provider', ''));

        $job_id = Yii::$app->request->post('by_id', 0);
        if ( $job_id ) {
            $job = EP\Job::loadById($job_id);
            if ( $job ) {
                $export_provider = $job->job_provider;
                if ( isset($job->job_configure['export']['columns']) && is_array($job->job_configure['export']['columns']) ) {
                    $selected = array_flip($job->job_configure['export']['columns']);
                }
            }
        }

        $providers = new EP\Providers();

        $columns = array();

        $default_config = $providers->getProviderConfig($export_provider);
        $exportProvider = $providers->getProviderInstance($export_provider, $default_config);

        if (is_object($exportProvider) && $exportProvider instanceof EP\Provider\ExportInterface){
            $columns = $exportProvider->getColumns(['adm_export_hidden']);
        }

        if ( !is_array($selected) ) {
            $selected = array();
            $get_selected_fields_r = tep_db_query(
                "SELECT shop_field ".
                "FROM ".TABLE_EP_PROFILES." ".
                "WHERE ep_direction='export' AND ep_type='".tep_db_input($export_provider)."' "
            );
            if ( tep_db_num_rows($get_selected_fields_r)>0 ) {
                while( $_selected_field = tep_db_fetch_array($get_selected_fields_r) ){
                    if ( !isset($columns[$_selected_field['shop_field']]) ) continue;
                    $selected[$_selected_field['shop_field']] = $_selected_field['shop_field'];
                }
            }
            if ( count($selected)==0 ) {
                $selected = false;
            }
        }

        $out_columns = array();
        foreach( $columns as $key=>$column_title ) {
            $out_columns[] = array(
                'db_key' => $key,
                'selected' => (is_array($selected)?isset($selected[$key]):true),
                'title' => $column_title,
            );
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = $out_columns;
    }

    function actionProcessExport()
    {
        $this->layout = false;

        $export_filename = tep_db_prepare_input(Yii::$app->request->post('export_filename'));
        $export_provider = tep_db_prepare_input(Yii::$app->request->post('export_provider'));

        $format = tep_db_prepare_input(Yii::$app->request->post('format'));
        $feedFormat = 'CSV';
        $containerFormat = 'CSV';
        if ( strpos($format,'-')!==false ) {
            list($feedFormat,$containerFormat) = explode('-',$format,2);
        }elseif ( $format=='ZIP' ) {
            $containerFormat = 'ZIP';
        }elseif ( in_array($format,['CSV','XML','XLSX', 'XML_orders_new']) ) {
            $containerFormat = $format;
        }

        $selected_columns = tep_db_prepare_input(Yii::$app->request->post( 'selected_fields', '' ));
        if ( !empty($selected_columns) ) {
            $selected_columns = explode(',',$selected_columns);
        }else{
            $selected_columns = false;
        }

        $filter = tep_db_prepare_input(Yii::$app->request->post('filter'));
        if ( !is_array($filter) ) $filter = [];
        $file_name_mark = '';
        if ( !empty($filter['category_id']) ){
            $_filtered_categories = [(int)$filter['category_id']];
            \common\helpers\Categories::get_parent_categories($_filtered_categories,$_filtered_categories[0],false);
            $_filtered_categories = array_reverse($_filtered_categories);
            $file_name_mark = preg_replace('/[^\w\d]+/u', '_', implode('_',array_map(['\common\helpers\Categories','get_categories_name'],$_filtered_categories))).'_';
        }

        if ( !empty($filter['order']['date_from']) ) {
            $value_time = date_create_from_format(DATE_FORMAT_DATEPICKER_PHP, \common\helpers\Date::checkInputDate($filter['order']['date_from']));
            $filter['order']['date_from'] = '';
            if ( $value_time ) {
               $filter['order']['date_from'] = $value_time->format('Y-m-d');
            }
        }
        if ( !empty($filter['order']['date_to']) ) {
            $value_time = date_create_from_format(DATE_FORMAT_DATEPICKER_PHP, \common\helpers\Date::checkInputDate($filter['order']['date_to']));
            $filter['order']['date_to'] = '';
            if ( $value_time ) {
               $filter['order']['date_to'] = $value_time->format('Y-m-d');
            }
        }

        $providers = new EP\Providers();
        $default_config = $providers->getProviderConfig($export_provider);

        if ( $this->currentDirectory->cron_enabled && Yii::$app->request->post('new_job',0) ) {
            $error = false;
            $export_filename = ltrim(FileHelper::normalizePath('/'.$export_filename),'/');
            if ( empty($export_filename) ){
                $error = ERROR_EMPTY_FILENAME;
            }else{
                if ($this->currentDirectory->findJobByFilename($export_filename)){
                    $error = ERROR_FILENAME_NOT_UNIQUE;
                }
            }
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            if ($error){
                Yii::$app->response->data = [
                    'status' => 'error',
                    'dialog' => [
                        'title' => ICON_ERROR,
                        'message' => '<p>'.$error.'</p>',
                    ]
                ];
                return;
            }
            $new_job_data = [
                'directory_id' => $this->currentDirectory->directory_id,
                'file_name' => $export_filename,
                'direction' => $this->currentDirectory->directory_type,
                'job_provider' => $export_provider,
                'job_state' => 'configured',
                'job_configure' => [
                    'export' => [
                        'columns' => $selected_columns,
                        'filter' => $filter,
                        'format' => $containerFormat,
                    ]
                ],
            ];

            $exportProviders = $providers->getAvailableProviders('Export');
            foreach ($exportProviders as $exportProvider){
                if ( $exportProvider['key']!=$export_provider ) continue;
                $new_job_data['job_configure'] = array_merge($default_config, $new_job_data['job_configure']);
                if ( isset($exportProvider['export']) && isset($exportProvider['export']['write_config'][$format]) ){
                    if ( !isset($new_job_data['job_configure']['export']) ) $new_job_data['job_configure']['export'] = [];
                    $new_job_data['job_configure']['export']['write_config'] = $exportProvider['export']['write_config'][$format];
                }
            }
            $new_job_data['job_configure'] = \json_encode($new_job_data['job_configure']);

            tep_db_perform(TABLE_EP_JOB, $new_job_data);

            Yii::$app->response->data = ['status'=>'ok'];
            return;
        }

        for( $i=0; $i<ob_get_level();$i++ ) {
            ob_end_clean();
        }

        $job_id = Yii::$app->request->post('by_id',0);
        if ( $job_id ) {

            $messages = new \backend\models\EP\Messages([
                'job_id' => $job_id,
                'output' => 'none',
            ]);

            $job = EP\Job::loadById($job_id);

            if (true) {
                if ( $containerFormat=='ZIP' ) {
                    $mime_type = 'application/zip';
                    $extension = 'zip';
                }elseif(strpos($containerFormat,'XML')!==false) {    /*ZRADA - Adding headers for your format /**/
                    $mime_type = 'application/xml';
                    $extension = 'xml';
                }elseif ( $format=='XLSX' ) {
                    $mime_type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                    $extension = 'xlsx';
                }else{
                    $mime_type = 'application/vnd.ms-excel';
                    $extension = 'csv';
                }
                $export_provider = $job->job_provider;
                $filename  = (strpos($export_provider,'\\')===false?$export_provider:substr($export_provider,strpos($export_provider,'\\')+1) ) . '_'. $file_name_mark . strftime( '%Y%b%d_%H%M' ) . '.'.$extension;
                $feed_filename  = (strpos($export_provider,'\\')===false?$export_provider:substr($export_provider,strpos($export_provider,'\\')+1) ) . '_' . $file_name_mark . strftime( '%Y%b%d_%H%M' ) . '.csv';

                Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
                Yii::$app->response->setDownloadHeaders($filename, $mime_type, false);
                Yii::$app->response->content = null;
                Yii::$app->response->send();
            }

            $job->file_name = 'php://output';
            $job->job_configure['export'] = [
                'columns' => false,
                'filter' => [],
                'format' => $containerFormat,
            ];
            if ( $containerFormat=='ZIP' && $job->job_provider!='product\catalog' ) {
                $job->job_configure['export']['feed'] = [
                    'feed_filename' => $feed_filename,
                    'format' => $feedFormat,
                ];
            }

            $job->run($messages);
            die;
        }

        if (true) {
            if ( $containerFormat=='ZIP' ) {
                $mime_type = 'application/zip';
                $extension = 'zip';
            }elseif(strpos($containerFormat,'XML')!==false){    /*ZRADA - Adding headers for your format /**/
                $mime_type = 'application/xml';
                $extension = 'xml';
            }elseif ( $format=='XLSX' ) {
                $mime_type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                $extension = 'xlsx';
            }else{
                $mime_type = 'application/vnd.ms-excel';
                $extension = 'csv';
            }
            $filename  = (strpos($export_provider,'\\')===false?$export_provider:substr($export_provider,strpos($export_provider,'\\')+1) ) . '_' . $file_name_mark . strftime( '%Y%b%d_%H%M' ) . '.'.$extension;
            $feed_filename  = (strpos($export_provider,'\\')===false?$export_provider:substr($export_provider,strpos($export_provider,'\\')+1) ) . '_' . $file_name_mark . strftime( '%Y%b%d_%H%M' ) . '.csv';

            Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
            Yii::$app->response->setDownloadHeaders($filename, $mime_type, false);
            Yii::$app->response->content = null;
            Yii::$app->response->send();
        }

        $messages = new EP\Messages();
        $exportJob = new EP\JobFile();
        $exportJob->directory_id = $this->currentDirectory->directory_id;
        $exportJob->direction = 'export';
        $exportJob->file_name = 'php://output';
        $exportJob->job_provider = $export_provider;
        $exportJob->job_configure = array_merge($default_config, ['export' => [
            'columns' => $selected_columns,
            'filter' => $filter,
            'format' => $containerFormat,
        ]]);

        $providers = new EP\Providers();
        $exportProviders = $providers->getAvailableProviders('Export');
        foreach ($exportProviders as $exportProvider){
            if ( $exportProvider['key']!=$export_provider ) continue;
            if ( isset($exportProvider['export']) && isset($exportProvider['export']['write_config'][$format]) ){
                $exportJob->job_configure['export']['write_config'] = $exportProvider['export']['write_config'][$format];
            }
        }

        if ( $containerFormat=='ZIP' && $exportJob->job_provider!='product\catalog' ) {
            $exportJob->job_configure['export']['feed'] = [
                'feed_filename' => $feed_filename,
                'format' => $feedFormat,
            ];
        }

        $exportJob->run($messages);

        die;
    }

    public function actionUploadFileAjax()
    {
        include(DIR_FS_ADMIN . 'plugins/jQuery-File-Upload/php/UploadHandler.php');

        $file_type = Yii::$app->request->post('file_type','');

        $ep_files_dir = $this->currentDirectory->filesRoot(EP\Directory::TYPE_IMPORT);

        $UploadHandler = new \UploadHandler(array(
            'upload_dir' => $ep_files_dir,
            'accept_file_types' => '/.*/', //'/.+\.('.$ep_modules[$_GET['epID']]['epObj']->getAcceptedExtensions().')$/i',
            'param_name' => 'data_file',
            'access_control_allow_methods' => array('POST'),
        ));
        $response = $UploadHandler->get_response();
        if ( isset($response['data_file']) && is_array($response['data_file']) && isset($response['data_file'][0]) ) {
            if (is_object($response['data_file'][0]) && isset($response['data_file'][0]->name)) {
                if (isset($response['data_file'][0]->url)) {
                    // upload finished
                    $job_id = $this->currentDirectory->touchImportJob($response['data_file'][0]->name,'uploaded', $file_type);
                    $job = EP\Job::loadById($job_id);
                    if ( is_object($job) ) {
                        $job->tryAutoConfigure();
                    }
                }else{
                    // upload in progress
                    $this->currentDirectory->touchImportJob($response['data_file'][0]->name,'upload', $file_type);
                }
            }
        }
        die;
    }

    public function actionFilesList()
    {
        $this->layout = false;

        $recordsTotal = 0;
        $recordsFiltered = 0;
        $start = (int)Yii::$app->request->get('start',0);
        $length = (int)Yii::$app->request->get('length',25);

        $this->currentDirectory->synchronizeFiles();

        $providers = new EP\Providers();

        $formatter = new Formatter();

        $listDirectoryIds = [ $this->currentDirectory->directory_id ];
        $subdirectories = $this->currentDirectory->getSubdirectories();

        foreach($subdirectories as $subdir){
            $listDirectoryIds[] = $subdir->directory_id;
        }

        $search_condition = '';
        $search_array = Yii::$app->request->get('search');
        if (is_array($search_array) && isset($search_array['value']) && !empty($search_array['value']) ) {
            $search_word = tep_db_prepare_input($search_array['value']);
            $search_condition .= " AND file_name like '%".tep_db_input(str_replace(' ','%',$search_word))."%' ";
            //
            $get_providers_r = tep_db_query(
                "SELECT DISTINCT job_provider ".
                "FROM ".TABLE_EP_JOB." ".
                "WHERE directory_id='".$this->currentDirectory->directory_id."' "
            );
            $providersIn = [];
            if ( tep_db_num_rows($get_providers_r)>0 ) {
                while($_provider = tep_db_fetch_array($get_providers_r)) {
                    $providerName = $providers->getProviderName($_provider['job_provider']);
                    if ( preg_match('/'.str_replace('\s{1,}','.*',preg_quote($search_word)).'/i',$providerName) ) {
                        $providersIn[] = tep_db_input($_provider['job_provider']);
                    }
                }
            }
            if ( count($providersIn)>0 ) {
                $search_condition = " AND ( 1 {$search_condition} OR job_provider IN ('".implode("','",$providersIn)."') ) ";
            }
        }

        $dir_files = array();
        $get_db_files_r = tep_db_query(
            "SELECT SQL_CALC_FOUND_ROWS job_id ".
            "FROM ".TABLE_EP_JOB." ".
            "WHERE directory_id='".$this->currentDirectory->directory_id."' ".
            " {$search_condition} ".
            //"WHERE directory_id IN('".implode("','", $listDirectoryIds)."') ".
            "ORDER BY file_time DESC, last_cron_run DESC ".
            "LIMIT {$start}, {$length} "
        );
        $totalRecordArray = tep_db_fetch_array(tep_db_query("SELECT FOUND_ROWS() AS total_records"));
        $recordsTotal += $totalRecordArray['total_records'];
        if ( tep_db_num_rows($get_db_files_r)>0 ) {
            $recordsFiltered += $totalRecordArray['total_records'];//tep_db_num_rows($get_db_files_r);
            while( $_db_file = tep_db_fetch_array($get_db_files_r) ){
                $dir_files[] = EP\Job::loadById($_db_file['job_id']);
            }
        }

        $directoryRoot = $this->currentDirectory->filesRoot();
        $files = array();
        // {{
        if ( $levelUpDirectory = $this->currentDirectory->getParent() ) {
            $files[] = array(
                '<div data-directory_id="'.$levelUpDirectory->directory_id.'"><span class="parent_cats"><i class="icon-circle"></i><i class="icon-circle"></i><i class="icon-circle"></i></span></div>',
                '--',
                '',
                '',
                '<div class="job-actions">'.
                ($this->currentDirectory->canConfigureDatasource()?'<a class="job-button js-action-link" href="javascript:void(0);" data-action="configure_datasource_settings" data-type="'.$this->currentDirectory->directory_type.'" data-directory_id="'.(int)$this->currentDirectory->directory_id.'"><i class="icon-wrench"></i></a>':'').
                ($this->currentDirectory->canConfigure()?'<a class="job-button js-action-link" href="javascript:void(0);" data-action="configure_dir" data-type="'.$this->currentDirectory->directory_type.'" data-directory_id="'.(int)$this->currentDirectory->directory_id.'"><i class="icon-cog"></i></a>':'').
                '</div>'
            );
        }
        $recordsTotal += count($subdirectories);
        $recordsFiltered += count($subdirectories);
        foreach($subdirectories as $subdir){
            $files[] = array(
                '<div class="cat_name cat_name_attr" data-directory_id="'.$subdir->directory_id.'">'.$subdir->directory.'</div>',
                '--',
                '',
                '',
                '<div class="job-actions">'.
                ($subdir->canRemove()?'<a class="job-button" href="javascript:void(0);" onclick="return ep_directory_remove('.(int)$subdir->directory_id.');"><i class="icon-trash"></i></a>':'').
                ($subdir->canConfigureDatasource()?'<a class="job-button js-action-link" href="javascript:void(0);" data-action="configure_datasource_settings" data-type="'.$subdir->directory_type.'" data-directory_id="'.(int)$subdir->directory_id.'"><i class="icon-wrench"></i></a>':'').
                ($subdir->canConfigure()?'<a class="job-button js-action-link" href="javascript:void(0);" data-action="configure_dir" data-type="'.$subdir->directory_type.'" data-directory_id="'.(int)$subdir->directory_id.'"><i class="icon-cog"></i></a>':'').
                '</div>'
            );
        }
        // }}
        if ($this->currentDirectory->cron_enabled && (in_array($this->currentDirectory->directory_type, ['import', 'processed', 'datasource'] )) ){
            foreach( array_keys($files) as $__idx ) {
                $files[$__idx][5] = $files[$__idx][4];
                $files[$__idx][4] = '';
            }
        }

        foreach ($dir_files as $job){
            /**
             * @var EP/Job $job
             */
            if ( $job instanceof EP\JobFile) {
                $file_info = $job->getFileInfo();
                $showFilename = $job->file_name;
                if (!empty($file_info['pathFilename'])) {
                    $showFilename = str_replace($directoryRoot, '', $file_info['pathFilename']);
                }

                if (!empty($file_info['fileSystemName']) && is_file($file_info['fileSystemName'])) {
                    $fileNameCell = '<div style="white-space: nowrap"><a href="' . Yii::$app->urlManager->createUrl(['easypopulate/download', 'id' => $job->job_id]) . '" target="_blank"><i class="' . (strpos($job->direction,'import')===0 ? 'icon-upload' : 'icon-download fieldRequired') . '"></i></a> ' . $showFilename . '</div>';
                } else {
                    $fileNameCell = '<div style="white-space: nowrap"><i class="icon-download fieldRequired"></i> ' . $showFilename . '</div>';
                }
            }else{
                $fileNameCell = '<div style="white-space: nowrap"> ' . $job->file_name . '</div>';
                $file_info = false;
            }

            $showJobProvider = $providers->getProviderName($job->job_provider);
            if ( $this->currentDirectory->directory_type=='import' ) {
                $showJobProvider = '<a href="javascript:void(0)" class="js-change-job-type" onclick="uploader(\'need_choose_file_type\', {\'id\':\''.$job->job_id.'\'});">'.$showJobProvider.'</a>';
            }

            $file_row = array(
              $fileNameCell,
                $showJobProvider,
              (is_array($file_info) && $file_info['fileSize']?$formatter->asShortSize($file_info['fileSize'],3):'--'),
                (($file_info['fileTime']??null)>0?\common\helpers\Date::datetime_short(date('Y-m-d H:i:s',$file_info['fileTime'])):(
                    ($job->last_cron_run>2000?\common\helpers\Date::datetime_short($job->last_cron_run):'--')
                ))
              ,
              '<div class="job-actions">'.
              ($job->canRemove()?'<a class="job-button" href="javascript:void(0);" onclick="return ep_file_remove('.(int)$job->job_id.');"><i class="icon-trash"></i></a>':'').
              ($job->canConfigureExport()?'<a class="job-button" href="javascript:void(0);" onclick="return ep_command(\'configure_export_columns\', '.(int)$job->job_id.');"><i class="icon-reorder"></i></a>':'').
              ($job->canConfigureImport()?'<a class="job-button" href="javascript:void(0);" onclick="return ep_command(\'configure\', '.(int)$job->job_id.');"><i class="icon-reorder"></i></a>':'').
              ($job->canSetupRunFrequency()?'<a class="job-button" href="javascript:void(0);" onclick="return ep_command(\'run_frequency\', '.(int)$job->job_id.');"><i class="icon-time" style="color:'.($job->run_frequency==-1?'red':'green').'"></i></a>':'').
              ($job->canRun()?'<a class="job-button" href="javascript:void(0);" onclick="return ep_command(\''.$job->direction.'\', '.(int)$job->job_id.');"><i class="icon-play"></i></a>':'').
              ($job->haveMessages()?'<a class="job-button" href="javascript:void(0);" onclick="return showJobMessages('.(int)$job->job_id.');"><i class="icon-file-text"></i></a>':'').
              '</div>'
            );
            if ($this->currentDirectory->cron_enabled && (in_array($this->currentDirectory->directory_type, ['import', 'processed', 'datasource'] )) ){
                $file_row[5] = $file_row[4];
                $file_row[4] = $job->job_state;
                if ( $job->job_state==EP\Job::PROCESS_STATE_IN_PROGRESS ) {
                    $file_row[4] .= ' '.$job->process_progress.'%';
                }
            }
            $files[] = $file_row;
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = [
            'data' => $files,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
        ];
    }

    public function actionDownload()
    {
        $this->layout = false;
        $job_id = Yii::$app->request->get('id',0);
        $job = EP\Job::loadById($job_id);
        if ( $job && $job instanceof EP\JobFile && is_file($job->getFileSystemName()) ){

            for( $i=0; $i<ob_get_level();$i++ ) {
                ob_end_clean();
            }

            $filename = basename($job->file_name);

            $mime_type = FileHelper::getMimeTypeByExtension($job->file_name);
            if ( $mime_type=='text/plain' ) {
                $mime_type = 'application/vnd.ms-excel';
            }

            header('Content-Type: ' . $mime_type);
            header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Content-Disposition: attachment; filename="' . urlencode($filename) . '"');

            if (preg_match('@MSIE ([0-9].[0-9]{1,2})@', $_SERVER['HTTP_USER_AGENT'], $log_version)) {
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
            } else {
                header('Pragma: no-cache');
            }

            readfile($job->getFileSystemName());
        }
        die;
    }

    public function actionRemoveDirectory()
    {
        $this->layout = false;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $directory_id = intval(Yii::$app->request->post('id',0));

        $directory = EP\Directory::loadById($directory_id);
        if ( $directory && $directory->delete() ) {
            Yii::$app->response->data = ['status'=>'ok'];
        }else{
            Yii::$app->response->data = ['status'=>'error'];
        }
    }

    public function actionRemoveEpFile()
    {
        $this->layout = false;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $file_id = intval(Yii::$app->request->post('id',0));

        $job = EP\Job::loadById($file_id);
        if ( $job && $job->delete() ) {
            Yii::$app->response->data = ['status'=>'ok'];
        }else{
            Yii::$app->response->data = ['status'=>'error'];
        }

    }

    public function actionCommand()
    {
        $cmd = Yii::$app->request->post('cmd','');
        if ( !empty($cmd) && method_exists($this,$cmd) ) {
            return call_user_func(array($this,$cmd));
        }
    }

    public function actionChooseProvider()
    {
        $this->layout = false;
        $id = intval(Yii::$app->request->post('id'));
        $update_type = tep_db_prepare_input(Yii::$app->request->post('file_type'));
        $job = EP\Job::loadById((int)$id);
        if ( $job ) {
            $job->tryAutoConfigure($update_type);
        }
        return '';
    }

    public function actionImportConfigure()
    {

        $providers = new EP\Providers();

        // load ep_job
        $job_record = false;

        $open_full_match = false;
        $job_by_filename = Yii::$app->request->post('by_file_name','');
        if ( !empty($job_by_filename) ) {
            $job_record = $this->currentDirectory->findJobByFilename($job_by_filename);
        }else{
            $job_by_id = Yii::$app->request->post('by_id', '');
            if (!empty($job_by_id)) {
                $job_record = EP\Job::loadById($job_by_id);
                $open_full_match = true;
            }
        }

        if ( !is_object($job_record) || !$job_record->canConfigureImport() ) {
            die;
        }

        $process_filename = Yii::$app->request->post('process_filename','');
        $process_filename_navigate = Yii::$app->request->post('navigate','');

        $command_params = [
            'id' => $job_record->job_id,
            'filename' => $job_record->file_name,
            'process_filename' => $job_record->file_name,
            'navigation' => false,
        ];

        if ( !empty($job_record->job_provider) && $job_record->job_provider!='auto' ) {
            $checkValid = $providers->getProviderInstance($job_record->job_provider);
            if ( !is_object($checkValid) ) {
                $job_record->job_provider = '';
            }
        }

        $multiSheets = [];
        $isMultiSheets = false;
        if (defined('EP_MULTI_SHEETS') && !empty(EP_MULTI_SHEETS)) {
          $multiSheets = array_map('strtolower', explode(',', EP_MULTI_SHEETS));
        }
        $fileSystemName = $job_record->getFileSystemName();
        if ( is_array($job_record->job_configure) && isset($job_record->job_configure['import']) && !empty($job_record->job_configure['import']['format']) ) {
            $readerClass = $job_record->job_configure['import']['format'];
            $readerConfig = array_merge([
                'class' => 'backend\\models\\EP\\Reader\\' . $readerClass,
                'filename' => $fileSystemName,
            ], (isset($this->job_configure['import']) && is_array($this->job_configure['import'])?$this->job_configure['import']:[]));
            if (!empty($readerConfig['class']) && class_exists ($readerConfig['class'])) {
              $reader = Yii::createObject($readerConfig);
            }
        }elseif ($job_record->direction == 'datasource') {
          //datasource import options
          //isset($this->job_configure['import']) && is_array($this->job_configure['import'])?$this->job_configure['import']:[];

        }else
        if ( preg_match('/\.zip/i',$fileSystemName) ) {
            $reader = new EP\Reader\ZIP([
                'filename' => $fileSystemName,
            ]);
        }elseif(preg_match('/\.xml/i',$fileSystemName)) {
            $reader = new EP\Reader\XML_orders_new([
                'filename' => $fileSystemName,
            ]);/**/
        }elseif(preg_match('/\.xlsx/i',$fileSystemName)) {
            $reader = new EP\Reader\XLSX([
                'filename' => $fileSystemName,
            ]);
            if (in_array('xlsx', $multiSheets)) {
              $isMultiSheets = true;
            }
            $reader->filename = $fileSystemName; //2check why doesn't work else not from baseobject
        }elseif(preg_match('/\.xls/i',$fileSystemName)) {
            $reader = new EP\Reader\XLS([
                'filename' => $fileSystemName,
            ]);
            $reader->filename = $fileSystemName; //2check why doesn't work else
        }else {
            $reader = new EP\Reader\CSV([
                'filename' => $fileSystemName,
            ]);
        }

        if ( $job_record instanceof EP\JobZipFile ){
            $uploadedFileColumns = $job_record->getArchivedFileColumns();
        }else {
          if ($isMultiSheets && $job_record instanceof EP\JobSheetsFile) {
            $uploadedFileColumns = $job_record->getArchivedFileColumns();
          } elseif (is_object($reader)) {
            $uploadedFileColumns = $reader->readColumns();
          } else {
            //import options only w/o columns mapping
            $uploadedFileColumns = [];
          }
        }

        if ( empty($job_record->job_provider) || $job_record->job_provider=='auto' ) {
            $possibleProviders = $job_record->tryAutoConfigure();
            if ( empty($job_record->job_provider) || $job_record->job_provider=='auto' ) {
                $command_params['selected_provider'] = '';
                if ( count($possibleProviders)>0 ) {
                    $command_params['selected_provider'] = current(array_keys($possibleProviders));;
                }
                echo '<script>window.parent.uploader(\'need_choose_file_type\', '.json_encode($command_params).')</script>';
                die;
            }
        }


        if ( $job_record instanceof EP\JobZipFile || ($isMultiSheets && $job_record instanceof EP\JobSheetsFile) ){
            $fileList = array_keys($uploadedFileColumns);
            $sequenceFeedIdx = array_search('process_sequence.csv', $fileList);
            if ( $sequenceFeedIdx!==false ) {
                unset($fileList[$sequenceFeedIdx]);
                $fileList = array_values($fileList);
            }
            $currentFilePointer = $process_filename?array_search($process_filename,$fileList):0;
            if ( $currentFilePointer===false ) $currentFilePointer = 0;
            $innerFilename = $fileList[$currentFilePointer];

            if ( $process_filename_navigate=='next' && isset($fileList[$currentFilePointer+1]) ) {
                $innerFilename = $fileList[$currentFilePointer + 1];
            }elseif ( $process_filename_navigate=='prev' && isset($fileList[$currentFilePointer-1]) ) {
                $innerFilename = $fileList[$currentFilePointer-1];
            }

            $_fileColumns = $uploadedFileColumns[$innerFilename];
            $job_remap =
                (isset($job_record->job_configure['containerFilesSetting'][$innerFilename]['remap_columns']) && is_array($job_record->job_configure['containerFilesSetting'][$innerFilename]['remap_columns']))?
                    $job_record->job_configure['containerFilesSetting'][$innerFilename]['remap_columns']:[];
            $job_configure =
                (isset($job_record->job_configure['containerFilesSetting'][$innerFilename]) && is_array($job_record->job_configure['containerFilesSetting'][$innerFilename]))?
                    $job_record->job_configure['containerFilesSetting'][$innerFilename]:[];
            $fileColumns = $_fileColumns['columns'];
            $command_params['filename'] = $job_record->file_name.'\\'.$innerFilename;
            $command_params['process_filename'] = $innerFilename;
            $command_params['file_columns'] = $fileColumns;
            $command_params['navigation'] = count($uploadedFileColumns)>1?true:false;
            $possibleProviders = $providers->bestMatch($fileColumns);
            reset($possibleProviders);
            $command_params['matched_providers'] = array_keys($possibleProviders);
            $firstProvider = $providers->getProviderInstance($command_params['matched_providers'][0], $job_configure);
            //$command_params['provider_name'] = $firstProvider;
            $command_params['provider_columns'] = array_merge(array(''),$firstProvider->getColumns());

            if ( count($job_remap)==0 ) {
                $pMap = array_flip($firstProvider->getColumns());
                foreach ($fileColumns as $fileColumn) {
                    $job_remap[$fileColumn] = isset($pMap[$fileColumn]) ? $pMap[$fileColumn] : '';
                }
            }

            if ( method_exists($firstProvider,'importNewColumns') ) {
                $command_params['provider_columns'] = array_merge($command_params['provider_columns'], $firstProvider->importNewColumns($fileColumns, $job_remap));
            }
            
            $command_params['remap_columns'] = $job_remap;
            if ( method_exists($firstProvider,'importOptions') ) {
                $command_params['import_options'] = $firstProvider->importOptions();
            }

            echo '<script>window.parent.uploader(/*3*/\'need_choose_import_map\','.json_encode($command_params).')</script>';
        }else{
            if ( $job_record->job_provider == 'orders\orders' || (isset($job_record->job_configure['import']['format']) && stripos($job_record->job_configure['import']['format'],'XML')!==false  ) ) {
                die;
            }
            $job_remap = (isset($job_record->job_configure['remap_columns']) && is_array($job_record->job_configure['remap_columns']))?$job_record->job_configure['remap_columns']:[];
            $fileColumns = $uploadedFileColumns;
            $command_params['file_columns'] = $fileColumns;
            $possibleProviders = $providers->bestMatch($fileColumns);
            if ( empty($job_record->job_provider) && count($possibleProviders)==0 && count($fileColumns)>0) {
                echo '<script>window.parent.uploader(\'wrong_file_type\')</script>';
                die;
            }

            if (!empty($job_record->job_provider)) {
              $command_params['matched_providers'][0] = $job_record->job_provider;
            } else {
              reset($possibleProviders);
              $command_params['matched_providers'] = array_keys($possibleProviders);
            }
            $firstProvider = $providers->getProviderInstance($command_params['matched_providers'][0], $job_record->job_configure);
            if (method_exists($firstProvider, 'getColumns')) {
              $command_params['provider_columns'] = array_merge(array(''),$firstProvider->getColumns());
            }

            if ( count($job_remap)==0 && method_exists($firstProvider, 'getColumns')) {
                $pMap = array_flip($firstProvider->getColumns());
                foreach ($fileColumns as $fileColumn) {
                    $job_remap[$fileColumn] = isset($pMap[$fileColumn]) ? $pMap[$fileColumn] : '';
                }
            }

            if ( method_exists($firstProvider,'importNewColumns') ) {
                $command_params['provider_columns'] = array_merge($command_params['provider_columns'], $firstProvider->importNewColumns($fileColumns, $job_remap));
            }
            
            $command_params['remap_columns'] = $job_remap;

            if ( method_exists($firstProvider,'importOptions') ) {
                $command_params['import_options'] = $firstProvider->importOptions();
            }

            echo '<script>window.parent.uploader(/*3*/\'need_choose_import_map\','.json_encode($command_params).')</script>';
        }
        die;

        if ( empty($job_record->job_provider) || $job_record->job_provider=='auto' ) {
            // guess
            /**
             * @var $reader EP\Reader\ReaderInterface
             */
            if ( count($fileColumns)==0 ) {
                echo '<script>window.parent.uploader(\'wrong_file_type\')</script>';
                die;
            }

            $possibleProviders = $providers->bestMatch($fileColumns);
            reset($possibleProviders);
            if ( count($possibleProviders)==0 ) {
                echo '<script>window.parent.uploader(\'need_choose_file_type\', '.json_encode($command_params).')</script>';
                die;
            }elseif ( current($possibleProviders)==1 ) {
                $fileProvider = current(array_keys($possibleProviders));
                $job_record->job_provider = $fileProvider;
                tep_db_query(
                    "UPDATE ".TABLE_EP_JOB." ".
                    "SET job_state='configured', job_provider='".tep_db_input($fileProvider)."' ".
                    "WHERE job_id='".$job_record->job_id."' "
                );
                echo '<script>window.parent.uploader(\'reload_file_list\')</script>';
            }else{
                // not sure, something match, but not 100%
                $command_params['matched_providers'] = array_keys($possibleProviders);
                tep_db_query(
                    "UPDATE ".TABLE_EP_JOB." ".
                    "SET job_provider='".tep_db_input($command_params['matched_providers'][0])."' ".
                    "WHERE job_id='".$job_record->job_id."' "
                );
                $command_params['file_columns'] = $fileColumns;
                $firstProvider = $providers->getProviderInstance($command_params['matched_providers'][0]);
                //$command_params['provider_name'] = $firstProvider;
                $command_params['provider_columns'] = array_merge(array(''),$firstProvider->getColumns());

                $command_params['remap_columns'] = [];
                $pMap = array_flip($firstProvider->getColumns());
                foreach( $fileColumns as $fileColumn ) {
                    $command_params['remap_columns'][$fileColumn] = isset($pMap[$fileColumn])?$pMap[$fileColumn]:'';
                }

                echo '<script>window.parent.uploader(/*1*/\'need_choose_import_map\','.json_encode($command_params).')</script>';
                die;
            }
        }

        $job_configure = $job_record->job_configure;

        $providerObj = $providers->getProviderInstance( $job_record->job_provider );

        if ( $providerObj->getColumnMatchScore( $fileColumns )!=1 || $open_full_match ) {
            $command_params['file_columns'] = $fileColumns;
            $command_params['provider_columns'] = array_merge(array(''=>''),$providerObj->getColumns());

            if ( isset($job_configure['remap_columns']) && is_array($job_configure['remap_columns']) ) {
                $command_params['remap_columns'] = $job_configure['remap_columns'];
            }else{
                $command_params['remap_columns'] = [];
                $pMap = array_flip($providerObj->getColumns());
                foreach( $fileColumns as $fileColumn ) {
                    $command_params['remap_columns'][$fileColumn] = isset($pMap[$fileColumn])?$pMap[$fileColumn]:'';
                }
            }

            echo '<script>window.parent.uploader(/*2*/\'need_choose_import_map\','.json_encode($command_params).')</script>';
            die;
        }

        // guess job_provider for auto
        // check columns map - init dialog for map missing
        // suggest start import
        die;
    }

    public function actionConfirmMapping()
    {
        $this->layout = false;

        $result = ['status'=>'ok'];

        $job_id = intval(Yii::$app->request->post('id', 0 ));
        $process_filename = Yii::$app->request->post('process_filename', '');
        $map = Yii::$app->request->post('map', array() );
        $importConfig = Yii::$app->request->post('import_config', array() );

        $job_record = EP\Job::loadById((int)$job_id);

        if ( !is_object($job_record) ) {
            $result['status'] = 'error';
            $result['message'] = 'Job not found';
        }elseif( empty($job_record->job_provider) || $job_record->job_provider=='auto' ){
            $result['status'] = 'error';
            $result['message'] = 'Need select job type';
        }else{
            $processProvider = $job_record->job_provider;
            if ( $job_record instanceof EP\JobZipFile ||  $job_record instanceof EP\JobSheetsFile ){
                if ( isset($job_record->job_configure['containerFilesSetting']) && isset($job_record->job_configure['containerFilesSetting'][$process_filename]) ) {
                    $processProvider = $job_record->job_configure['containerFilesSetting'][$process_filename]['job_provider'];
                }
            }

            $providers = new EP\Providers();

            $provider = $providers->getProviderInstance($processProvider);
            if ( $provider instanceof EP\Provider\ProviderAbstract)
            {
                $providerColumns = $provider->getColumns();
                $remap_columns = array_flip($providerColumns);
                if ( is_array($map) && count($map)>0 ) {
                    $__map_columns = array();
                    foreach ($map as $fileColumnName=>$importFieldName){
                        if ( isset($providerColumns[$importFieldName]) ) {
                            $__map_columns[$fileColumnName] = $importFieldName;
                        }
                    }
                    if ( count($__map_columns)>0 ) {
                        $remap_columns = $__map_columns;
                    }
                }

                if ( $job_record instanceof EP\JobZipFile ||  $job_record instanceof EP\JobSheetsFile ){
                    if ( isset($job_record->job_configure['containerFilesSetting']) && isset($job_record->job_configure['containerFilesSetting'][$process_filename]) ) {
                        $job_record->job_configure['containerFilesSetting'][$process_filename]['remap_columns'] = $remap_columns;
                        $job_record->job_configure['containerFilesSetting'][$process_filename]['import_config'] = $importConfig;
                    }
                }else {
                    $job_record->job_configure['remap_columns'] = $remap_columns;
                    $job_record->job_configure['import_config'] = $importConfig;
                }

                tep_db_query(
                    "UPDATE ".TABLE_EP_JOB." ".
                    "SET job_state='configured', job_configure='".tep_db_input(json_encode($job_record->job_configure))."' ".
                    "WHERE job_id='".$job_record->job_id."' "
                );
            }elseif ($job_record->canConfigureImport()){
              //datasource - import options only
                $job_record->job_configure['import_config'] = $importConfig;
                tep_db_query(
                    "UPDATE ".TABLE_EP_JOB." ".
                    "SET job_state='configured', job_configure='".tep_db_input(json_encode($job_record->job_configure))."' ".
                    "WHERE job_id='".$job_record->job_id."' "
                );

            }else{
                $result['status'] = 'error';
                $result['message'] = 'Wrong job type';
            }
        }



        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = $result;
    }

    public function actionImport()
    {
        $this->layout = false;

        for( $i=0; $i<ob_get_level();$i++ ) {
            ob_end_clean();
        }
        header('X-Accel-Buffering: no');

        $job_id = intval(Yii::$app->request->post('by_id', 0 ));

        $job_record = EP\Job::loadById($job_id);

        if ( !is_object($job_record) ) {
            $result['status'] = 'error';
            $result['message'] = 'Job not found';
        }else{
            $messages = new EP\Messages();
            $messages->setEpFileId($job_record->job_id);

            try {
                $job_record->run($messages);
            }catch (\Exception $ex){
                Yii::error('EasyPopulate manual import exception: '.$ex->getMessage()."\n".$ex->getTraceAsString());
                $messages->info($ex->getMessage());
            }
            $messages->command('reload_file_list');
        }
        die;
    }

    public function actionRunDataSource()
    {
        $this->layout = false;

        $job_id = intval(Yii::$app->request->post('by_id', 0 ));

        $messages = new EP\Messages();
        $messages->command('start_import');
        $messages->command('set_title','Job process');

        /**
         * @var $job_record EP\JobDatasource
         */
        $job_record = EP\Job::loadById($job_id);
        if ( !is_object($job_record) || !is_a($job_record, '\\backend\\models\\EP\\JobDatasource') ||  !$job_record->canRun() ) {
            $messages->info("Job can not be runned");
            $messages->progress(100);
            $messages->command('reload_file_list');
            die;
        }

        if ( $job_record->canRunInBrowser() ) {
            $messages = new EP\Messages();
            $messages->setEpFileId($job_record->job_id);

            $now = strtotime('now');

            try {
                $job_record->setJobStartTime($now);

                $messages->progress(0);
                tep_db_query(
                    "UPDATE ".TABLE_EP_JOB." ".
                    "SET last_cron_run='".date('Y-m-d H:i:s',$now)."' ".
                    "WHERE job_id='".$job_record->job_id."'"
                );

                $job_record->run($messages);

            }catch (\Exception $ex){
                $messages->info($ex->getMessage());
            }
            $job_record->jobFinished();

            $messages->info('Done');
            $messages->command('reload_file_list');
        }else{
            $job_record->runASAP();
            $messages->info('Job start scheduled');
            $messages->progress(100);
            $messages->command('reload_file_list');
        }

        die;
    }

    public function actionJobLogMessages()
    {
        $this->layout = false;
        $id = Yii::$app->request->get('id');
        $messages = [];
        $message_string = '';
        $get_job_messages_r = tep_db_query(
            "SELECT message_text ".
            "FROM ".TABLE_EP_LOG_MESSAGES." ".
            "WHERE job_id='".(int)$id."' ".
            "ORDER BY ep_log_message_id ".
            "/*LIMIT 3000*/"
        );
        if ( tep_db_num_rows($get_job_messages_r)>0 ){
            while( $message = tep_db_fetch_array($get_job_messages_r) ) {
                $message_string .= $message['message_text'].'<br>';
                //$messages[] = $message;
            }
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
        return $this->render('log-messages',['messages'=>$messages,'message_string'=>$message_string]);
        /*Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = [
            'dialog' => [
                'title'=>'Job messages',
                'message' => $this->render('log-messages',['messages'=>$messages,'message_string'=>$message_string]),
                'buttons' => [
                    'cancel' => [
                        'label' => TEXT_OK,
                        'className' => 'btn-primary',
                    ]
                ]
            ]
        ];*/
    }

    public function actionJobFrequency()
    {
        $this->layout = false;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $by_id = Yii::$app->request->get('by_id', 0);
        $by_id = Yii::$app->request->post('by_id', $by_id);

        $run_frequency = Yii::$app->request->post('run_frequency', -1);
        $run_time = Yii::$app->request->post('run_time', '00:00');
        $freq_period = Yii::$app->request->post('freq_period', 'job');

        if ( Yii::$app->request->isPost ) {
            $time_APM = strtotime('2000-01-01 '.$run_time);
            tep_db_query(
                "UPDATE ".TABLE_EP_JOB." ".
                "SET ".((int)$run_frequency==0?"last_cron_run=IF(run_time='".tep_db_input(date('H:i',$time_APM))."',last_cron_run,NULL), ":'').
                " run_frequency='".(int)$run_frequency."', run_time='".tep_db_input(date('H:i',$time_APM))."' ".
                "WHERE job_id='".(int)$by_id."' "
            );
            if ( $freq_period=='directory' ){
                $jobObj = EP\Job::loadById($by_id);
                if ( $jobObj ) {
                    $directoryObj = $jobObj->getDirectory();
                    $directoryObj->updateJobDirectoryConfig($by_id,[
                        'run_frequency' => (int)$run_frequency,
                        'run_time' => date('H:i',$time_APM),
                    ]);
                }
            }
            Yii::$app->response->data = ['status'=>'ok'];
            return;
        }

        $freq_period = 'directory';
        $jobObj = EP\Job::loadById($by_id);
        if ( $jobObj ) {
            $run_frequency = $jobObj->run_frequency;
            $run_time = $jobObj->run_time;
            $configs = $jobObj->getDirectory()->getJobConfigTemplate($jobObj->job_id);
            if ( !empty($configs) ){
                $firstConfig = reset($configs);
                if ( $firstConfig['run_time']!=$run_time || $firstConfig['run_frequency']!=$run_frequency ){
                    $freq_period = 'job';
                }
            }
        }

        $runFrequencyVariants = [
            -1 => TEXT_DISABLED,
            1 => TEXT_IMMEDIATELY,
            0 => TEXT_DEFINED_TIME,
            5 => TEXT_EVERY_5_MINUTES,
            15 => TEXT_EVERY_15_MINUTES,
            30 => TEXT_EVERY_30_MINUTES,
            60 => TEXT_EVERY_HOUR,
            120 => sprintf(TEXT_NN_HOURS, 2),
            180 => sprintf(TEXT_NN_HOURS, 3),
            240 => sprintf(TEXT_NN_HOURS, 4),
            300 => sprintf(TEXT_NN_HOURS, 5),
            360 => sprintf(TEXT_NN_HOURS, 6),
            720 => sprintf(TEXT_NN_HOURS, 12),
            1440 => TEXT_EVERY_DAY,
        ];

        $time_APM = strtotime('2000-01-01 '.$run_time);

        Yii::$app->response->data = [
            'dialog' => [
                'title'=>'Job run frequency',
                'message' => $this->render('popup-job-frequency',[
                    'run_frequency' => $run_frequency,
                    'runFrequencyVariants' => $runFrequencyVariants,
                    'freq_period' => $freq_period,
                    'run_time' => date('g:i A',strtotime('2000-01-01 '.$run_time)),
                ]),
                'buttons' => [
                    'confirm' => [
                        'label' => TEXT_OK,
                        'className' => 'btn-primary',
                    ]
                ]
            ]
        ];
    }

    public function actionDatasourceAction()
    {
        $directoryId = Yii::$app->request->get('id',0);
        if ( $directory = EP\Directory::findById($directoryId) ){
            $datasource = $directory->getDatasource();
            if ( $datasource && method_exists($datasource, 'datasourceActions') ){
                return $datasource->datasourceActions($directory, $datasource);
            }
        }
    }

    public function actionEmpty()
    {
        if (\Yii::$app->request->post('products')){
          $query = tep_db_query("select * from " . TABLE_CATEGORIES);
          while ($data = tep_db_fetch_array($query)){
            @unlink(DIR_FS_CATALOG_IMAGES . $data['categories_image']);
          }

          \common\classes\Images::cleanImageReference();
          $productImagesDirPath = \common\classes\Images::getFSCatalogImagesPath().'products'.DIRECTORY_SEPARATOR;
          if ( is_dir($productImagesDirPath) ) {
              $imagesDirHandle = opendir($productImagesDirPath);
              while (($productImageDirectory = readdir($imagesDirHandle)) !== false) {
                  if (!is_numeric($productImageDirectory) || intval($productImageDirectory)!=$productImageDirectory) continue;
                  $removeImageDirectory = $productImagesDirPath . DIRECTORY_SEPARATOR . $productImageDirectory;
                  if ( is_file($removeImageDirectory) ) continue; //??
                  try {
                      FileHelper::removeDirectory($removeImageDirectory);
                  }catch (\Exception $ex){}
              }
              closedir($imagesDirHandle);
          }
          \common\helpers\Product::trunk_products();
          \common\helpers\Categories::trunk_categories();

          tep_db_query("TRUNCATE TABLE " . TABLE_FILTERS);

          tep_db_query("TRUNCATE TABLE " . \common\models\WarehousesProducts::tableName());
          $query = tep_db_query("select * from " . TABLE_MANUFACTURERS);
          while ($data = tep_db_fetch_array($query)){
            @unlink(DIR_FS_CATALOG_IMAGES . $data['manufacturers_image']);
          }
          tep_db_query("TRUNCATE TABLE " . TABLE_MANUFACTURERS);
          tep_db_query("TRUNCATE TABLE " . TABLE_MANUFACTURERS_INFO);

          tep_db_query("TRUNCATE TABLE " . TABLE_PROPERTIES_CATEGORIES);
          tep_db_query("TRUNCATE TABLE " . TABLE_PROPERTIES_CATEGORIES_DESCRIPTION);
          tep_db_query("TRUNCATE TABLE " . TABLE_PROPERTIES_TO_PROPERTIES_CATEGORIES);
          tep_db_query("TRUNCATE TABLE " . TABLE_PROPERTIES);
          tep_db_query("TRUNCATE TABLE " . TABLE_PROPERTIES_DESCRIPTION);
          tep_db_query("TRUNCATE TABLE " . TABLE_PROPERTIES_TO_PRODUCTS);
          tep_db_query("TRUNCATE TABLE " . TABLE_PROPERTIES_VALUES);

          if ( defined('TABLE_PRODUCTS_IMAGES_EXTERNAL_URL') ){
             tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_IMAGES_EXTERNAL_URL);
          }

          tep_db_query("TRUNCATE TABLE " . TABLE_DOCUMENT_TYPES);

          tep_db_query("TRUNCATE TABLE ep_holbi_soap_link_products");
          tep_db_query("TRUNCATE TABLE ep_holbi_soap_link_categories");
          tep_db_query("TRUNCATE TABLE ep_holbi_soap_mapping");

          tep_db_query(
             "INSERT IGNORE INTO " . TABLE_PLATFORMS_CATEGORIES . " (platform_id, categories_id) " .
             "SELECT platform_id, 0 FROM ".TABLE_PLATFORMS
          );

          tep_db_query("TRUNCATE TABLE ep_holbi_soap_link_products");
          tep_db_query("TRUNCATE TABLE ep_holbi_soap_products_flags");
          tep_db_query("TRUNCATE TABLE ep_holbi_soap_link_categories");
          tep_db_query("TRUNCATE TABLE ep_holbi_soap_kv_storage");
          tep_db_query("TRUNCATE TABLE ep_holbi_soap_kw_id_storage");

            $schemaCheck = Yii::$app->get('db')->schema->getTableSchema('gapi_search');
            if ( $schemaCheck ) {
                tep_db_query("TRUNCATE TABLE gapi_search");
            }
            $schemaCheck = Yii::$app->get('db')->schema->getTableSchema('gapi_search_to_products');
            if ( $schemaCheck ) {
                tep_db_query("TRUNCATE TABLE gapi_search_to_products");
            }

          $schemaCheck = Yii::$app->get('db')->schema->getTableSchema('products_groups');
          if ( $schemaCheck ) {
              tep_db_query("TRUNCATE TABLE products_groups");
          }

        }

        if (\Yii::$app->request->post('orders') == 1) {
            \common\helpers\Order::trunk_orders();

            \common\models\OrdersProductsAllocate::deleteAll();

            foreach ([
                'amazon_payment_orders',
                'klarna_order_reference',
                'orders_payment',
                'orders_transactions',
                'orders_transactions_children',
                'tracking_numbers',
                'tracking_numbers_to_orders_products',
                'ep_holbi_soap_link_orders',
                'ep_holbi_soap_kv_storage',
                'ga'
            ] as $truncate_table) {
                $schemaCheck = Yii::$app->getDb()->schema->getTableSchema($truncate_table);
                if ($schemaCheck) {
                    Yii::$app->getDb()->createCommand("TRUNCATE TABLE " . $truncate_table)->execute();
                }
            }
        }


        if (\Yii::$app->request->post('customers') == 1){
            \common\helpers\Customer::trunk_customers();
            
            foreach ([
                'products_notify',
                'virtual_gift_card_basket',
                'wedding_registry',
                'wedding_registry_inviting',
                'wedding_registry_products',
                'ep_holbi_soap_link_customers',
                'ep_holbi_soap_kv_storage',
                'gdpr_check',
                'guest_check',
                'personal_catalog'
            ] as $truncate_table) {
                $schemaCheck = Yii::$app->getDb()->schema->getTableSchema($truncate_table);
                if ($schemaCheck) {
                    Yii::$app->getDb()->createCommand("TRUNCATE TABLE " . $truncate_table)->execute();
                }
            }
        }
        $messageStack = \Yii::$container->get('message_stack');
        $messageStack->add_session(ICON_SUCCESS, 'header', 'success');

        return $this->redirect(Yii::$app->urlManager->createUrl('easypopulate/'));
    }

    private static function nocompress($v, $d=0) {
        return $v;
    }

    private function presetEncodeDecode($data, $encode)
    {
        if (function_exists('bzcompress')) {
            $_comp = 'bzcompress';
            $_uncomp = 'bzdecompress';
        } elseif (function_exists('gzcompress')) {
            $_comp = 'gzcompress';
            $_uncomp = 'gzuncompress';
        } else {
            $_comp = $_uncomp = ['self', 'nocompress'];
        }
        if ((int)$encode > 0) {
            return @base64_encode(call_user_func($_comp, json_encode($data), 9));
        } else {
            $return = [];
            try {
                $return = @json_decode(call_user_func($_uncomp, base64_decode($data)), true);
            } catch (\Exception $exc) {}
            return (is_array($return) ? $return : []);
        }
    }

    function actionPresetLoad()
    {
        $this->layout = false;
        if (Yii::$app->request->isPost) {
            $return = ['status' => 'error'];
            $type = trim(Yii::$app->request->post('type', ''));
            if ($type != '') {
                $exportPresetRecord = \common\models\DataStorage::findOne(['pointer' => 'easypopulate_export_preset']);
                if ($exportPresetRecord instanceof \common\models\DataStorage) {
                    $return = ['status' => 'ok', 'presetArray' => []];
                    $presetArray = $this->presetEncodeDecode($exportPresetRecord->data, false);
                    if (isset($presetArray[$type])) {
                        foreach ($presetArray[$type] as $preset => $presetList) {
                            $return['presetArray'][$preset] = implode(';', $presetList);
                        }
                        ksort($return['presetArray'], SORT_STRING);
                    }
                }
            }
            echo json_encode($return);
        }
        die();
    }

    function actionPresetSave()
    {
        $this->layout = false;
        if (Yii::$app->request->isPost) {
            $return = ['status' => 'error'];
            $type = trim(Yii::$app->request->post('type', ''));
            $preset = trim(Yii::$app->request->post('preset', ''));
            $selection = Yii::$app->request->post('selection', '');
            $selection = (is_array($selection) ? $selection : []);
            if ($type != '' AND $preset != '' AND count($selection) > 0)  {
                $presetArray = [];
                $exportPresetRecord = \common\models\DataStorage::findOne(['pointer' => 'easypopulate_export_preset']);
                if (!($exportPresetRecord instanceof \common\models\DataStorage)) {
                    $exportPresetRecord = new \common\models\DataStorage();
                    $exportPresetRecord->pointer = 'easypopulate_export_preset';
                } else {
                    $presetArray = $this->presetEncodeDecode($exportPresetRecord->data, false);
                }
                $presetArray[$type][$preset] = $selection;
                $exportPresetRecord->data = $this->presetEncodeDecode($presetArray, true);
                if (strlen($exportPresetRecord->data) <= 65530) {
                    $exportPresetRecord->date_modified = date('Y-m-d H:i:s', strtotime('+100 years'));
                    $exportPresetRecord->detachBehavior('date_modified_now');
                    try {
                        $exportPresetRecord->save();
                        $return = ['status' => 'ok'];
                    } catch (\Exception $exc) {
                        $return = ['status' => 'error', 'message' => $exc->getMessage()];
                    }
                } else {
                    $return = ['status' => 'error', 'message' => TEXT_EASYPOPULATE_EXPORT_MAXLENGTH];
                }
            }
            echo json_encode($return);
        }
        die();
    }

    function actionPresetDelete()
    {
        $this->layout = false;
        if (Yii::$app->request->isPost) {
            $return = ['status' => 'error'];
            $type = trim(Yii::$app->request->post('type', ''));
            $preset = trim(Yii::$app->request->post('preset', ''));
            if ($type != '' AND $preset != '')  {
                $exportPresetRecord = \common\models\DataStorage::findOne(['pointer' => 'easypopulate_export_preset']);
                if ($exportPresetRecord instanceof \common\models\DataStorage) {
                    $presetArray = $this->presetEncodeDecode($exportPresetRecord->data, false);
                    if (isset($presetArray[$type][$preset])) {
                        unset($presetArray[$type][$preset]);
                        $exportPresetRecord->data = $this->presetEncodeDecode($presetArray, true);
                        $exportPresetRecord->date_modified = date('Y-m-d H:i:s', strtotime('+100 years'));
                        $exportPresetRecord->detachBehavior('date_modified_now');
                        try {
                            $exportPresetRecord->save();
                            $return = ['status' => 'ok'];
                        } catch (\Exception $exc) {
                            $return = ['status' => 'error', 'message' => $exc->getMessage()];
                        }
                    }
                }
            }
            echo json_encode($return);
        }
        die();
    }
    public function actionIoProject()
    {
        $this->layout = false;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $formData = [];
        if(Yii::$app->request->isGet) {
            $project_id = intval(Yii::$app->request->get('project_id', 0));
            $formData = tep_db_fetch_array(tep_db_query("SELECT * FROM io_project WHERE project_id = '{$project_id}'"));
        }elseif(Yii::$app->request->isPost){
            $project_id = intval(Yii::$app->request->post('project_id', 0));
            $table_data = [
                'project_code' => Yii::$app->request->post('project_code',''),
                'description' => Yii::$app->request->post('description',''),
            ];
            $is_local = Yii::$app->request->post('is_local');
            if ( !is_null($is_local) ) {
                $table_data['is_local'] = $is_local?1:0;
            }
            if ( $project_id ) {
                tep_db_perform('io_project',$table_data,'update',"project_id = '{$project_id}'");
            }else{
                tep_db_perform('io_project',$table_data);
                $project_id = tep_db_insert_id();
            }
            $formData = tep_db_fetch_array(tep_db_query("SELECT * FROM io_project WHERE project_id = '{$project_id}'"));
        }

        Yii::$app->response->data = array(
            'formData' => is_array($formData)?$formData:[],
        );
    }

    public function actionIoProjectsList()
    {
        $this->layout = false;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $projects = array();

        $get_projects_r = tep_db_query("SELECT * FROM io_project WHERE 1 ORDER BY 1");
        $recordsTotal = tep_db_num_rows($get_projects_r);
        $recordsFiltered = $recordsTotal;
        if ( tep_db_num_rows($get_projects_r)>0 ) {
            while( $project = tep_db_fetch_array($get_projects_r) ) {
                $projects[] = array(
                    $project['project_code'],
                    '<div class="job-actions">'.
                    '<a class="job-button js-project-edit" href="javascript:void(0);" data-project_id="'.(int)$project['project_id'].'"><i class="icon-edit"></i></a>'.
                    /*($this->currentDirectory->canConfigure()?'<a class="job-button js-action-link" href="javascript:void(0);" data-action="configure_dir" data-type="'.$this->currentDirectory->directory_type.'" data-directory_id="'.(int)$this->currentDirectory->directory_id.'"><i class="icon-remove"></i></a>':'').*/
                    '</div>'
                );
            }
        }
        Yii::$app->response->data = array(
            'data' => $projects,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
        );
    }

}
