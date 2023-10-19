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

namespace backend\components;

use common\classes\Images;
use common\models\repositories\NotFoundException;
use Yii;
use common\helpers\Seo;
use yii\helpers\FileHelper;

class Information {

  public static function form($adgrafics_information, $information_id, $title){
    global $language;
  
    $dir_listing = array(array('id' => '', 'text' => TEXT_NONE));
    if ($dir = @dir(DIR_FS_CATALOG)) {
      while ($file = $dir->read()) {
        if (!is_dir($module_directory . $file)) {
          if (substr($file, strrpos($file, '.')) == '.php') {
            $dir_listing[] = array('id' => $file, 'text' => $file);
          }
        }
      }
      sort($dir_listing);
      $dir->close();
    }
    
    $tabList = $tabLang = array();
    ob_start();
  ?> 
<div class="tab-pane" id="mainTabPane">
<?php
  $page = ob_get_contents();
  ob_end_clean();
  
  $languages = \common\helpers\Language::get_languages();
  for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
   ob_start();
    if ($adgrafics_information != 'Added') {
      $edit = self::read_data($information_id, $languages[$i]['id']);
    }
?>
      <div class="tab-page" id="tabDescriptionLanguages_<?php echo $languages[$i]['code']; ?>">

        <script type="text/javascript"><!--
//        mainTabPane.addTabPage( document.getElementById( "tabDescriptionLanguages_<?php echo $languages[$i]['code']; ?>" ) );
        //-->
        </script>  
        <div class="edp-line">
            <label><?php echo TITLE_PAGE_TITLE;?></label>
            <?php echo tep_draw_input_field('page_title[' . $languages[$i]['id'] . '][0]', "$edit[page_title]", 'maxlength=255 class="form-control form-control-small"'); ?>
        </div>
        <div class="edp-line">
            <label><?php echo TEXT_NAME_IN_MENU;?></label>
            <?php echo tep_draw_input_field('info_title[' . $languages[$i]['id'] . '][0]', "$edit[info_title]", 'maxlength=255 class="form-control form-control-small"'); ?>
        </div>
        <div class="edp-line">
            <label><?php echo DESCRIPTION_INFORMATION;?>:</label>
            <?php if(WYSIWYG_EDITOR_POPUP_INLINE=='popup') { ?>
            <?php echo tep_image(DIR_WS_ICONS . 'icon_edit.gif', TEXT_OPEN_WYSIWYG_EDITOR, 16, 16, 'onclick="loadedHTMLAREA(\'edit_info\',\'description[' . $languages[$i]['id'] . '][0]\');"'); ?>
            <?php } ?>
            <?php echo tep_draw_textarea_field('description[' . $languages[$i]['id'] . '][0]', '', '', '', "$edit[description]",'class="form-control ckeditor text-dox-01" id="description[' . $languages[$i]['id'] . '][0]"'); ?>
        </div>
        <div class="edp-line">
            <label><?php echo TITLE_PAGE_TYPE;?></label>
            <?php echo '<div class="edp-line-wra"><label>'.tep_draw_radio_field('page_type[' . $languages[$i]['id'] . ']', 'SSL', $edit['page_type'] == 'SSL') . '&nbsp;' . TEXT_SSL . '</label>&nbsp;&nbsp;&nbsp;<label>' . tep_draw_radio_field('page_type[' . $languages[$i]['id'] . ']', 'NONSSL', ($edit['page_type'] == 'NONSSL' || $edit['page_type'] == '')) . '&nbsp;' . TEXT_NONSSL.'</label></div>';?>
        </div>
     
        
        <table border="0" cellpadding="5" cellspacing="0">
          
          <tr>
            <td class="main" colspan="2">
          </td>
          </tr>
                    
        </table>
       </div>
<?php
    $tabLang[] = array('title' => $languages[$i]['name'],'content' =>ob_get_contents(), 'id' => $languages[$i]['code'], 'active'=> ($i==0 ? true : false));
    ob_end_clean();
  }
  ob_start();
?>       
</div>
<?php    
  $page .= ob_get_contents();
  ob_end_clean();
    
  $tabList[] = array(
      'title' => $title,
      'id' => 'mainTabPane',
      'content' => $page,
      'langtabs' => $tabLang,
      'active' => 1
  );
  return $tabList;
  }
  
  public static function browse_information ($where='1') {
    $languages_id = \Yii::$app->settings->get('languages_id');
    $daftar=tep_db_query("SELECT * FROM " . TABLE_INFORMATION . " WHERE languages_id='".$languages_id."' and affiliate_id = 0 and {$where} ORDER BY v_order");
    $result = array();
    while ($buffer = tep_db_fetch_array($daftar)) {
      $result[]=$buffer;
    }
    return $result;
  }

  public static function read_data ($information_id, $language_id, $platform_id, $affiliate_id = 0) {
    $result = tep_db_fetch_array(tep_db_query("SELECT * FROM " . TABLE_INFORMATION . " WHERE information_id='".$information_id."' and languages_id = '" . $language_id . "' and platform_id='".(int)$platform_id."' and affiliate_id = '" . $affiliate_id . "'"));
    return $result;
  }

  public static function add_information($data, $language_id, $platform_id, $affiliate_id = 0) {
    global $insert_id;
    if (!tep_not_null($data['seo_page_name'][$language_id])) {
      $data['seo_page_name'][$language_id] = Seo::makeSlug($data['info_title'][$language_id][$affiliate_id]);
    }
    $query ="INSERT INTO " . TABLE_INFORMATION . " (information_id, visible, v_order, info_title, description, languages_id, page_title, page, scope, seo_page_name, old_seo_page_name, meta_description, meta_key, affiliate_id, page_type, noindex_option, nofollow_option, rel_canonical) VALUES('" . $insert_id . "', '" . $data['visible'][$language_id] . "', '" . $data['v_order'][$language_id] . "', '" . tep_db_input($data['info_title'][$language_id][$affiliate_id]) . "', '" . tep_db_input($data['description'][$language_id][$affiliate_id]) . "','" . $language_id . "', '" . tep_db_input($data['page_title'][$language_id][$affiliate_id]) . "', '" . $data['page'][$language_id] . "', '" . (is_array($data['scope'][$language_id])?implode(',', $data['scope'][$language_id]):'') . "', '" . tep_db_input($data['seo_page_name'][$language_id]) . "', '" . tep_db_input($data['old_seo_page_name'][$language_id]) . "', '" . tep_db_input($data['meta_description'][$language_id]) . "', '" . tep_db_input($data['meta_key'][$language_id]) . "', '" . $affiliate_id . "', '" . $data['page_type'][$language_id] . "', '" . $data['noindex_option'][$language_id] . "', '" . $data['nofollow_option'][$language_id] . "', '" . $data['rel_canonical'][$language_id] . "')";
    tep_db_query($query);
    if ($insert_id == ''){
      $insert_id = tep_db_insert_id();
    }
  }

  public static function update_information ($data, $language_id, $platform_id, $affiliate_id = 0) {
    $info_id = $data['information_id'];

    if (is_array($data['information_h2_tag'][$language_id][$platform_id] ?? null)) {
       $data['information_h2_tag'][$language_id][$platform_id] = implode("\n", $data['information_h2_tag'][$language_id][$platform_id]);
    }
    if (is_array($data['information_h3_tag'][$language_id][$platform_id] ?? null)) {
       $data['information_h3_tag'][$language_id][$platform_id] = implode("\n", $data['information_h3_tag'][$language_id][$platform_id]);
    }

    $sql_data = array();
    foreach( array('v_order', 'meta_title', 'info_title', 'description', 'page_title', 'page', 'scope', 'seo_page_name', 'old_seo_page_name', 'meta_description', 'meta_key', 'information_h1_tag', 'information_h2_tag', 'information_h3_tag', 'page_type', 'noindex_option', 'nofollow_option', 'rel_canonical','description_short') as $field ) {
      if ( array_key_exists($field,$data) ) {
        if (isset($data[$field][$language_id][$platform_id]) && !is_array($data[$field][$language_id][$platform_id])) {
          $sql_data[$field] = $data[$field][$language_id][$platform_id];
        } elseif (isset($data[$field][$language_id][$platform_id][$affiliate_id]) && !is_array($data[$field][$language_id][$platform_id][$affiliate_id])) {
          $sql_data[$field] = $data[$field][$language_id][$platform_id][$affiliate_id];
        }
      }
    }
    if ( isset($data['visible_per_platform']) ) {
      $sql_data['visible'] = isset($data['visible'][$platform_id])?1:0;
    }
    if ( !isset($data['noindex_option']) ) {
      $sql_data['nofollow_option'] = 0;
    }
    if ( !isset($data['nofollow_option']) ) {
      $sql_data['nofollow_option'] = 0;
    }

    if ( empty($sql_data['seo_page_name']) && !empty($sql_data['page_title']) ) {
      $sql_data['seo_page_name'] = Seo::makeSlug($sql_data['page_title']);
    }
    if ( empty($sql_data['seo_page_name']) && !empty($sql_data['info_title']) ) {
      $sql_data['seo_page_name'] = Seo::makeSlug($sql_data['info_title']);
    }

    $check = tep_db_fetch_array(tep_db_query("select count(*) as c from " . TABLE_INFORMATION . " where information_id= '" . $info_id . "' and languages_id = '" . $language_id . "' and platform_id='".(int)$platform_id."' and affiliate_id = '" . $affiliate_id . "'"));
    if ( $check['c']>0 ) {
      $sql_data['last_modified'] = 'now()';
      tep_db_perform(TABLE_INFORMATION,$sql_data,'update',"information_id= '" . $info_id . "' and languages_id = '" . $language_id . "' and platform_id='".(int)$platform_id."' and affiliate_id = '" . $affiliate_id . "'");
    }else{
      $sql_data['information_id'] = $info_id;
      $sql_data['languages_id'] = $language_id;
      $sql_data['platform_id'] = $platform_id;
      $sql_data['affiliate_id'] = $affiliate_id;
      $sql_data['date_added'] = 'now()';
      tep_db_perform(TABLE_INFORMATION,$sql_data);
      $info_id = tep_db_insert_id();
    }
    return $info_id;
  }

    public static function updateAdditionalField($info_id)
    {
        $date_added = Yii::$app->request->post('date_added',date("Y-m-d H:i:s"));
        if(strtotime($date_added)===false){
            $date_added = date("Y-m-d H:i:s");
        }
        $maps_id = (int)Yii::$app->request->post('maps_id',0);
        $type = (int)Yii::$app->request->post('type',0);
        $hide_on_xml = Yii::$app->request->post('hide_on_xml') ? 1 : 0;

        $image = Yii::$app->request->post('image','');
        $imageGallery = Yii::$app->request->post('imageGallery','');
        $imageDelete = (int)Yii::$app->request->post('image_delete',0);
        $oldImage = \common\models\Information::find()->select(['image'])->where(['information_id'=>$info_id])->limit(1)->column();
        $oldImage = $oldImage[0] ?? null;

        $newImage = \common\helpers\Image::prepareSavingImage(
            $oldImage,
            $imageGallery,
            $image,
            trim(self::imagesLocation(), DIRECTORY_SEPARATOR),
            $imageDelete
        );

        \common\models\Information::updateAll([
            'image' => $newImage,
            'type' => $type,
            'hide_on_xml' => $hide_on_xml,
            'date_added'=>$date_added,
            'maps_id'=>$maps_id],
            ['information_id' => $info_id]
        );
    }

    public static function slugify($string) {
        $string = transliterator_transliterate("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();", $string);
        $string = preg_replace('/[-\s]+/', '-', $string);
        return trim($string, '-');
    }

    public static function imagesLocation()
    {
        return 'information'.DIRECTORY_SEPARATOR;
    }

  public static function update_no_logged ($data, $language_id, $platform_id, $info_id, $affiliate_id = 0) {

    $sql_data = array();
    $no_logged = 0;
    if (isset($data[$platform_id]) && !is_array($data[$platform_id])) {
      $no_logged = ($data[$platform_id] ? '1' : '0');
    } elseif (isset($data[$platform_id][$affiliate_id]) && !is_array($data[$platform_id][$affiliate_id])) {
      $no_logged = ($data[$platform_id][$affiliate_id] ? '1' : '0');
    }

    tep_db_query("update " . TABLE_INFORMATION . " set no_logged = '" . $no_logged . "' where information_id= '" . $info_id . "' and languages_id = '" . $language_id . "' and platform_id='".(int)$platform_id."' and affiliate_id = '" . $affiliate_id . "'");

    return $info_id;
  }
  
  public static function update_visible_status($information_id, $visible, $platform_id=null) {
    if ( is_null($platform_id) ) {
      tep_db_query("update " . TABLE_INFORMATION . " set visible = '" . ($visible ? '1' : '0') . "' where information_id = '" . $information_id . "'");
    }else {
        foreach (\common\helpers\Language::get_languages() as $language){
            $pages = \common\models\Information::find()
                ->where(['information_id'=>$information_id, 'platform_id'=>(int)$platform_id, ])
                ->andWhere(['languages_id'=>$language['id']])
                ->all();
            if ( count($pages)==0 ){
                $pages[] = new \common\models\Information([
                    'information_id' => $information_id,
                    'platform_id' => (int)$platform_id,
                    'languages_id' => $language['id'],
                    'date_added' => new \yii\db\Expression('NOW()'),
                ]);
            }
            foreach ($pages as $page){
                $page->setAttributes([
                    'visible' => $visible ? 1 : 0,
                    'last_modified' => new \yii\db\Expression('NOW()'),
                ],false);
                $page->save(false);
            }
        }
    }
  }
   public static function tep_set_information_visible($information_id, $visible) {
    if ($visible == '1') {
      return tep_db_query("update " . TABLE_INFORMATION . " set visible = '0' where information_id = '" . $information_id . "'");
    } else{
      return tep_db_query("update " . TABLE_INFORMATION . " set visible = '1' where information_id = '" . $information_id . "'");
    }
  }

  public static function updateHideStatus($info_id, $hide)
  {    
    tep_db_perform(TABLE_INFORMATION, array('hide' => ($hide ? 1 : 0)), 'update', "information_id= '" . $info_id . "'");    
  }

  public static function showHidePage()
  {
    global $login_id;
    $show = false;
    $admin = tep_db_fetch_array(tep_db_query("select admin_email_address from " . TABLE_ADMIN . " where admin_id = " . $login_id));
    $email = explode('@', $admin['admin_email_address']);
    if ($email[0] != 'trueloaded' && $email[1] == 'holbi.co.uk') {
      $show = true;
    }
    return $show;
  }


  public static function template($info_id = 0)
  {
    if ($info_id) {
      $platforms = tep_db_query("
            SELECT DISTINCT i.platform_id, t.theme_name, t.title
            FROM " . TABLE_INFORMATION . " i
                left join " . TABLE_PLATFORMS_TO_THEMES . " p2t on p2t.is_default = 1 and i.platform_id = p2t.platform_id
                left join " . TABLE_THEMES . " t on t.id = p2t.theme_id
            WHERE i.information_id = '" . $info_id . "'
            ");
    } else {
      $platforms = tep_db_query("
            SELECT DISTINCT p2t.platform_id, t.theme_name, t.title
            FROM " . TABLE_PLATFORMS_TO_THEMES . " p2t
                left join " . TABLE_THEMES . " t on t.id = p2t.theme_id");
    }
    $themes = array();
    $showBlock = false;
    while ($platform = tep_db_fetch_array($platforms)) {
      $templates = tep_db_query("
                select setting_value
                from " . TABLE_THEMES_SETTINGS . "
                where
                    theme_name = '" . $platform['theme_name'] . "' and
                    setting_group = 'added_page' and
                    (setting_name = 'info' or setting_name = 'inform')
            ");
      if (tep_db_num_rows($templates) > 0) {
        while ($item = tep_db_fetch_array($templates)) {
          $platform['themes'][] = $item['setting_value'];
        }
        $showBlock = true;
      }
      $themes[$platform['platform_id']] = $platform;
    }

    $list = \common\classes\platform::getList(false);
    foreach ($list as $key => $item) {

      if ($themes[$item['id']] ?? null) {

          $styles = \common\models\ThemesStyles::find()
              ->select(['name' => 'accessibility'])->distinct()
              ->where(['theme_name' => $themes[$item['id']]['theme_name']])
              ->andWhere(['like', 'accessibility', '.s-'])
              ->asArray()->all();

          if ($info_id) {
              $pageStyles = \common\models\PageStyles::find()->where([
                  'type' => 'info',
                  'page_id' => $info_id,
                  'platform_id' => $item['id']
              ])->asArray()->one();
              if ($pageStyles['style'] ?? null) {
                  $list[$key]['page_style'] = $pageStyles['style'];
              }
          }

        $list[$key]['styles'] = $styles;
        $list[$key]['active'] = 1;
        $list[$key]['theme_name'] = $themes[$item['id']]['theme_name'];
        $list[$key]['theme_title'] = $themes[$item['id']]['title'];

        $list[$key]['templates'] = $themes[$item['id']]['themes'] ?? '';

        $setTemplate = tep_db_fetch_array(tep_db_query("
                      select template_name
                      from " . TABLE_INFORMATION . "
                      where
                          information_id = '" . $info_id . "' and
                          platform_id = '" . $item['id'] . "'
                  "));
        if ($setTemplate['template_name'] ?? null) {
          $list[$key]['template'] = $setTemplate['template_name'];
        } else {
          $list[$key]['template'] = '';
        }

      } else {
        $list[$key]['active'] = 0;
      }
    }

    $template['list'] = $list;
    $template['show_block'] = $showBlock;

    return $template;
  }

    public static function templateSave($info_id, $pageTemplates, $pageStyle)
    {
        if ( !is_array($pageTemplates) ) $pageTemplates = array();
        foreach ($pageTemplates as $id => $template) {
            $informations = \common\models\Information::find()->where([
                    'information_id' => $info_id,
                    'platform_id' => $id,
            ])->all();
            if (is_array($informations)) {
                foreach ($informations as $information) {
                    $information->template_name = $template;
                    $information->save();
                }
            }
        }

        if ( !is_array($pageStyle) ) $pageStyle = array();
        foreach ($pageStyle as $id => $style) {
            $pageStyles = \common\models\PageStyles::findOne([
                'type' => 'info',
                'page_id' => $info_id,
                'platform_id' => $id,
            ]);
            if (!$style) {
                if ($pageStyles) {
                    $pageStyles->delete();
                }
                continue;
            }
            if (!$pageStyles) {
                $pageStyles = new \common\models\PageStyles();
                $pageStyles->type = 'info';
                $pageStyles->page_id = $info_id;
                $pageStyles->platform_id = $id;
            }
            $pageStyles->style = $style;
            $pageStyles->save();
        }
    }

    public static function delete_information ($information_id) {
        tep_db_query("DELETE FROM " . TABLE_INFORMATION . " WHERE information_id='".(int)$information_id."'");

        $pageStyles = \common\models\PageStyles::deleteAll([
            'type' => 'info',
            'page_id' => $information_id,
        ]);
    }
}