<?php
namespace frontend\design\boxes;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\ListingSql;

class FiltersAll extends Widget
{

  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    \common\helpers\Translation::init('admin/main');
    $languages_id = \Yii::$app->settings->get('languages_id');
    //$currencies = \Yii::$container->get('currencies');
    $exclude_params = array('page');
    $filters_array = array();

    $this->params['this_filename'] = $this->params['this_filename'] ?? null;
    $listing_sql_array = \frontend\design\ListingSql::get_listing_sql_array($this->params['this_filename']);
    $listing_sql_array['left_join'] = " left join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c on p.products_id = p2c.products_id left join " . TABLE_MANUFACTURERS . " m on p.manufacturers_id = m.manufacturers_id left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on pd.products_id = p.products_id and pd.language_id = '" . (int) $languages_id . "' and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id = '" . (int) $languages_id . "' and pd1.platform_id = '" . (int)Yii::$app->get('platform')->config()->getPlatformToDescription() . "' " . $listing_sql_array['left_join'];
    if ($this->params['this_filename'] == FILENAME_SPECIALS) {
        $listing_sql_array['left_join'] = " left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id " . $listing_sql_array['left_join'];
    }

    $name = 'category_id';
    $filters_array[] = array(
        'title' => TEXT_CATEGORY,
        'name' => $name,
        'type' => 'pulldown',
        'pulldown' => tep_draw_pull_down_menu($name, \common\helpers\Categories::get_categories(array(array('id' => '', 'text' => TEXT_ALL_CATEGORIES))), Yii::$app->request->get($name), 'class="property js-filter_param"'),
        'params' => Yii::$app->request->get($name),
    );
    $exclude_params[] = $name;

    $name = 'keywords';
    $filters_array[] = array(
        'title' => TEXT_KEYWORDS,
        'name' => $name,
        'type' => 'input',
        'params' => Yii::$app->request->get($name),
    );
    $exclude_params[] = $name;
    
    return IncludeTpl::widget([
      'file' => 'boxes/filtersall.tpl',
      'params' => [
        //'filters_url' => tep_href_link($this->params['this_filename'], tep_get_all_get_params($exclude_params)),
    //    'filters_hiddens' => tep_get_all_get_params(array_merge(array('cPath'), $exclude_params), true),
        'filters_array' => $filters_array,
      ]
    ]);
  }
}