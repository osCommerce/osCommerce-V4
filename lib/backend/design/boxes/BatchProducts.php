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

namespace backend\design\boxes;

use common\helpers\Translation;
use Yii;
use yii\base\Widget;

class BatchProducts extends Widget
{

    public $id;
    public $params;
    public $settings;
    public $visibility;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $sortingOptions = \common\helpers\Sorting::getPossibleSortOptions();
        $sortingOptions[''] = TEXT_RANDOM;
        $sorting = \common\helpers\Html::dropDownList('setting[0][sort_order]',
            $this->settings[0]['sort_order'],
            $sortingOptions,
            ['class' => 'form-control']);

        $batchSelectedWidgets = \common\models\DesignBoxesTmp::find()->where([
            'widget_name' => 'BatchSelectedProducts',
            'theme_name' => $this->settings['theme_name'],
        ])->asArray()->all();

        $product_sources = [
            '' => '',
            'alsopurchased' => Translation::getTranslationValue('TEXT_ALSO_PURCHASED', 'admin/main'),
            'main_product' => TEXT_PRODUCT,
            'xsell_0' => Translation::getTranslationValue('FIELDSET_ASSIGNED_XSELL_PRODUCTS', 'admin/categories'),
        ];

        $extra_xsell_lists = [];
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UpSell'))
        {
            $extra_xsell_lists = $ext::getXsellTypeList();
        }

        foreach ($extra_xsell_lists as $extra_xsell_list){
            $product_sources['xsell_'.$extra_xsell_list['xsell_type_id']] = $extra_xsell_list['xsell_type_name'];
        }

        return $this->render('batch-products.tpl', [
            'id' => $this->id,
            'params' => $this->params,
            'settings' => $this->settings,
            'visibility' => $this->visibility,
            'sorting' => $sorting,
            'batchSelectedWidgets' => $batchSelectedWidgets,
            'product_sources' => $product_sources,
        ]);
    }
}