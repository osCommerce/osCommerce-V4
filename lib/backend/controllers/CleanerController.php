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

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

class CleanerController extends Sceleton {

    public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_TOOLS', 'BOX_TOOLS_CLEANER'];
    
    public $config;
    private static $files = [];
    private static $keys = [];
    private static $contents = [];
	 
    public function __construct($id, $module=null){
      
      $this->config = [
                                      'dirs' =>[  
                                                 Yii::$aliases['@backend'] . '/assets/',
                                                 Yii::$aliases['@backend'] . '/components/',
                                                 Yii::$aliases['@backend'] . '/config/',
                                                 Yii::$aliases['@backend'] . '/controllers/',
                                                 Yii::$aliases['@backend'] . '/design/',
                                                 Yii::$aliases['@backend'] . '/models/',
                                                 Yii::$aliases['@backend'] . '/themes/',
                                                
                                                 Yii::$aliases['@common'] . '/',
                                                 Yii::$aliases['@frontend'] . '/assets/',
                                                 Yii::$aliases['@frontend'] . '/components/',
                                                 Yii::$aliases['@frontend'] . '/config/',
                                                 Yii::$aliases['@frontend'] . '/controllers/',
                                                 Yii::$aliases['@frontend'] . '/design/',
                                                 Yii::$aliases['@frontend'] . '/models/',
                                                 Yii::$aliases['@frontend'] . '/themes/',
                                                
                                                 DIR_FS_CATALOG . 'admin/',
                                                 DIR_FS_CATALOG . 'ext/',
                                                 DIR_FS_CATALOG .  DIR_WS_INCLUDES,
                                                 DIR_FS_CATALOG . 'mspcheckout/'
                                              ],
                                      'exclude' => [
                                          DIR_FS_CATALOG . 'admin/includes/javascript/',
                                          DIR_FS_CATALOG . 'admin/js/',
                                          DIR_FS_CATALOG . 'admin/plugins/',
                                          DIR_FS_CATALOG . 'admin/uploads/',
                                          DIR_FS_CATALOG . 'admin/backups/',
                                          DIR_FS_CATALOG . 'admin/css/',
                                          DIR_FS_CATALOG . 'admin/images/',
                                          DIR_FS_CATALOG . 'admin/img/',
                                          DIR_FS_CATALOG . 'admin/themes/',
                                          DIR_FS_CATALOG . DIR_WS_INCLUDES . 'fonts/',
                                       ],
                                      'groups' => [],
                                      'invisible_group' => []
                      ];
      
        $languages_id = \Yii::$app->settings->get('languages_id');
        $cfg_group_query = tep_db_query("select configuration_group_id from configuration where 1 group by configuration_group_id");
        while ($group = tep_db_fetch_array($cfg_group_query)) {
            $title = \common\helpers\Translation::getTranslationValue($group['configuration_group_id'], 'admin/main', $languages_id);
            $this->config['groups'][$group['configuration_group_id']] = ['title' => (empty($title) ? '-' : $title), 'visible' => 1];
        }
        
      parent::__construct($id, $module);
    }
    
    private static function getContent($file){
      
      if (isset(self::$contents[$file])){
        return self::$contents[$file];
      }
      
      self::$contents[$file] = file_get_contents($file);
      
      return self::$contents[$file];
      
    }
	
    public function actionIndex() {
        global $login_groups_id;

        $languages_id = \Yii::$app->settings->get('languages_id');
        $this->selectedMenu = array('settings', 'tools', 'cleaner');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('cleaner/index'), 'title' => HEADING_TITLE);
        
        if ($login_groups_id == 1){
          $this->topButtons[] = '<a href="'.Yii::$app->urlManager->createUrl('cleaner/process').'" class="create_item update_config"><i class="icon-file-text"></i>' . IMAGE_UPDATE . '</a>';	
        }
        
        $this->view->headingTitle = HEADING_TITLE;

        //trash
        $trash = ['trashed' => []];
        $trash_query = tep_db_query("select configuration_id, configuration_key, configuration_title, configuration_group_id from configuration_trash where 1 ");
        if (tep_db_num_rows($trash_query)){
          while($row = tep_db_fetch_array($trash_query)){
            $trash['trashed'][] = ['configuration_id' => $row['configuration_id'], 'title'=>$row['configuration_title'].'&nbsp;('.$row['configuration_key'].')', 'source' => $row['configuration_group_id']];
          }
          $trash['destination'] = [];
          $dest_query = tep_db_query("select configuration_group_id from configuration where 1 group by configuration_group_id");
          while ($row = tep_db_fetch_array($dest_query)) {
            $title = \common\helpers\Translation::getTranslationValue($row['configuration_group_id'], 'admin/main', $languages_id);
            $trash['destination'][] = ['configuration_group_id' => $row['configuration_group_id'], 'title' => (empty($title) ? '-' : $title)];
          }
            $trash['destination'] = \yii\helpers\ArrayHelper::map($trash['destination'], 'configuration_group_id', 'title');
        }
		//echo '<pre>';print_r($trash);die;
        return $this->render('index', ['params' => $this->config, 'trash'=> $trash]);
    }
    
    public function actionProcess(){
      
      set_time_limit(0);
      
      $this->layout = false;
      
      $group_id = Yii::$app->request->get('group_id', '');
      $not_found = [];
      
      $cKeys = [];
      if ($group_id){
        $_query = "select trim(GROUP_CONCAT(configuration_key separator '|')) as items from " . TABLE_CONFIGURATION . " where configuration_group_id = '$group_id' group by configuration_group_id";
        $configuration_items = tep_db_fetch_array(tep_db_query( $_query ));
        
        $keys = explode("|", $configuration_items['items']);
        $existed_keys = [];

        $echo = '';
        
        foreach($this->config['dirs'] as $dir){

          $this->getAllFiles($dir);
       
        }
        
        $matches_all = [];
        if (is_array(self::$files)){
          
            foreach(self::$files as $filename){
              $echo .= $filename."<br>";
              
              $content = self::getContent($filename);

              preg_match_all("/" . $configuration_items['items'] . "/is", $content, $matches);
              if (count($matches[0]) > 0){
                //found
                $matches = array_unique($matches[0]);
                $matches_all[] =  $matches;
                $existed_keys = array_merge($existed_keys, array_values ($matches));
                $existed_keys = array_unique($existed_keys);
              } 

              if (count($existed_keys) >= count($keys)) break;
            }
            
          $not_found = array_diff($keys, $existed_keys);
          
        }
        
      }
      

      
      if (count($not_found)){
        $_tmp = [];
        
        foreach($not_found as $item){
          $_tmp[] = '<div class="row"><b class="nfdata" data-key="' . $item . '">'. $item.'</b>' . ": move to " . \yii\helpers\Html::dropDownList('group', null, yii\helpers\ArrayHelper::map($this->config['invisible_group'], 'id', 'title'), ['prompt' => 'Select Group to move to', 'class' => 'move-to']).'</div>';
        }
        
        $not_found = implode("<br>", $_tmp);
      } else {
        $not_found = '';
      }
      
      return json_encode(['group_id' => $group_id,
                          'search' => $configuration_items['items'],
                          'not_found' => $not_found, 
                          'found' => $existed_keys,
                          ]);
      
    }
    
    
    private function getAllFiles($dir){
      foreach(glob($dir.'*') as $filename){
        if (is_file($filename)){
          self::$files[] = $filename;
        }elseif (is_dir($filename)){
          if (in_array($filename . '/', $this->config['exclude'])) continue;
          if ( ($result = $this->getAllFiles($filename.'/')) !== true){
            array_merge(self::$files, $result);
          }
          
        }
      }
      return true;
    }
	
  public function actionMovekey(){
    $key = Yii::$app->request->post('key');
    $group_id  = Yii::$app->request->post('group_id');
    
    $answer = [];
    $this->layout = false;
    $answer['result'] = false;
    if ($key && $group_id){
      tep_db_query("insert into configuration_trash select */*configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function*/ from ". TABLE_CONFIGURATION . " where configuration_key = '" . tep_db_input($key) . "'");      
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key = '" . tep_db_input($key) . "'");
      
      $answer['result'] = true;
    } 
    echo json_encode($answer);
    exit;
  }
  
  public function actionMoveBack(){
    $messageStack = \Yii::$container->get('message_stack');
    $id = Yii::$app->request->get('id', 0);
    $group_id  = Yii::$app->request->post('group_id');
    
    if ($id && $group_id){
      tep_db_query("insert into " . TABLE_CONFIGURATION . " select configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, '" . $group_id. "', sort_order, last_modified, date_added, use_function, set_function from configuration_trash where configuration_id = '" . (int)$id . "'");
      tep_db_query("delete from configuration_trash where configuration_id = '" . (int)$id . "'");      
      $messageStack->add_session('Moved successfully', 'header', 'success');
    } else {
      $messageStack->add_session('Not Moved');
    }    
    echo 'ok';
  }

}
