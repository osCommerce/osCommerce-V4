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

namespace backend\models\ProductEdit;

use common\helpers\Seo;
use yii;
use common\models\Products;

class SaveDescription
{
    protected $product;
    protected $departmentId = 0;

    public function __construct(Products $product, $selectedDepartmentId)
    {
        $this->product = $product;
        $this->departmentId = (int)$selectedDepartmentId;
    }

    public function save()
    {
        $products_id = $this->product->products_id;
        $selectedDepartmentId = $this->departmentId;

        $languages = \common\helpers\Language::get_languages();
        $platforms = \common\models\Platforms::getPlatformsByType("non-virtual")->all();
        if ($platforms){
            $posted_description = Yii::$app->request->post('pDescription',[]);

            foreach($platforms as $platform){
                for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                    $language_id = $languages[$i]['id'];
                    if (isset($posted_description[$platform->platform_id][$language_id])){
                        $pDescription = \common\models\ProductsDescription::find()->where(['products_id' => $products_id, 'language_id' => $language_id, 'platform_id' => $platform->platform_id, 'department_id' => $selectedDepartmentId ])->one();
                        if (!is_object($pDescription)) {
                            $pDescription = \common\models\ProductsDescription::create($products_id, $language_id, $platform->platform_id, $selectedDepartmentId);
                        }
                        if ($selectedDepartmentId > 0) {
                            $pDescriptionOriginal = \common\models\ProductsDescription::find()->where(['products_id' => $products_id, 'language_id' => $language_id, 'platform_id' => $platform->platform_id, 'department_id' => 0 ])->one();
                            if (is_object($pDescriptionOriginal)) {
                                if ($pDescriptionOriginal->products_name == ($posted_description[$platform->platform_id][$language_id]['products_name'] ?? '')) {
                                    $posted_description[$platform->platform_id][$language_id]['products_name'] = '';
                                }
                                if ($pDescriptionOriginal->products_internal_name == ($posted_description[$platform->platform_id][$language_id]['products_internal_name'] ?? '')) {
                                    $posted_description[$platform->platform_id][$language_id]['products_internal_name'] = '';
                                }
                                if ($pDescriptionOriginal->products_description_short == ($posted_description[$platform->platform_id][$language_id]['products_description_short'] ?? '')) {
                                    $posted_description[$platform->platform_id][$language_id]['products_description_short'] = '';
                                }
                                if ($pDescriptionOriginal->products_description == ($posted_description[$platform->platform_id][$language_id]['products_description'] ?? '')) {
                                    $posted_description[$platform->platform_id][$language_id]['products_description'] = '';
                                }
                                //$posted_description[$platform->platform_id][$language_id]['products_seo_page_name'] = $pDescriptionOriginal->products_seo_page_name;
                            }
                        }
                        if (isset($posted_description[$platform->platform_id][$language_id]['products_h2_tag']) && is_array($posted_description[$platform->platform_id][$language_id]['products_h2_tag']))
                          $posted_description[$platform->platform_id][$language_id]['products_h2_tag'] = implode("\n", $posted_description[$platform->platform_id][$language_id]['products_h2_tag']);
                        if (isset($posted_description[$platform->platform_id][$language_id]['products_h3_tag']) && is_array($posted_description[$platform->platform_id][$language_id]['products_h3_tag']))
                          $posted_description[$platform->platform_id][$language_id]['products_h3_tag'] = implode("\n", $posted_description[$platform->platform_id][$language_id]['products_h3_tag']);
                        $pDescription->overwrite_head_title_tag = (int)($posted_description[$platform->platform_id][$language_id]['overwrite_head_title_tag'] ?? null);
                        $pDescription->overwrite_head_desc_tag = (int)($posted_description[$platform->platform_id][$language_id]['overwrite_head_desc_tag'] ?? null);
                        $pDescription->setAttributes($posted_description[$platform->platform_id][$language_id], false);
                        if ($selectedDepartmentId == 0) {
                            $pDescription->products_seo_page_name = Seo::makeProductSlug($pDescription, $this->product);
                            if ($ext = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed')){
                                $ext::trackProductLinks($products_id, $language_id, $platform->platform_id, $pDescription->getAttributes(), $pDescription->getOldAttributes());
                            }
                        }
                        $pDescription->save(false);
                    }else{
                        // not posted - add empty record if not exist
                        $pDescription = \common\models\ProductsDescription::find()->where(['products_id' => $products_id, 'language_id' => $language_id, 'platform_id' => $platform->platform_id, 'department_id' => $selectedDepartmentId ])->one();
                        if (!is_object($pDescription)) {
                            $pDescription = \common\models\ProductsDescription::create($products_id, $language_id, $platform->platform_id, $selectedDepartmentId);
                            $pDescription->loadDefaultValues();
                            $pDescription->save(false);
                        }
                    }
                }
            }
        }
    }
}