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

namespace common\helpers;


use common\classes\extended\OrderAbstract;
use common\classes\platform_config;
use common\models\OrdersCommentTemplate;
use common\models\OrdersCommentTemplateText;
use common\models\OrdersStatus;
use yii\db\Expression;

class CommentTemplate
{

    public static function getVisibilityVariants()
    {
        $res = [
            'order' => BOX_CUSTOMERS_ORDERS,
            'subscription' => BOX_CUSTOMERS_SUBSCRIPTION,
        ];
        foreach (\common\helpers\Hooks::getList('comment-template/visibility-variants') as $file)
        {
            include($file);
        }
        return $res;
    }

    public static function getActiveVariants($includeId=0)
    {
        $fallbackLanguages = [];
        $fallbackLanguages[] = \common\helpers\Language::get_default_language_id();

        $list = [];
        $Templates = OrdersCommentTemplate::find()
            ->where(['OR', ['status'=>1],[OrdersCommentTemplate::tableName().'.comment_template_id'=>$includeId]])
            ->orderBy(['sort_order'=>SORT_ASC])
            ->all();
        foreach ($Templates as $Template) {
            $textModel = $Template->getTexts()
                ->where(['language_id'=>\Yii::$app->settings->get('languages_id')])
                ->andWhere(['!=','comment_template',''])
                ->one();
            if ( !$textModel ) {
                $textModel = $Template->getTexts()
                    ->where(['IN', 'language_id', $fallbackLanguages])
                    ->andWhere(['!=','comment_template',''])
                    ->orderBy(new Expression("IF(language_id='".(int)$fallbackLanguages[0]."',0,1)"))
                    ->one();
            }
            $list[] = [
                'id' => $Template->comment_template_id,
                'text' => $textModel->name,
                'visibility' => preg_split('/,/',$Template->visibility,-1,PREG_SPLIT_NO_EMPTY),
            ];
        }

        return $list;
    }

    public static function getCommentTemplateVariants($type, $order)
    {
        $template_vars = [
            'CUSTOMER_NAME' => '',
            'STORE_NAME' => '',
            'STORE_OWNER' => '',
            'EMAIL_FROM' => '',
            'STORE_OWNER_EMAIL_ADDRESS' => '',
            'STORE_ADDRESS' => '',
        ];
        if ( is_object($order) && $order instanceof OrderAbstract){
            $template_vars['CUSTOMER_NAME'] = $order->customer['name'];

            $platform_config = new platform_config($order->info['platform_id']);
            $template_vars['STORE_NAME'] = $platform_config->const_value('STORE_NAME');
            $template_vars['STORE_OWNER'] = $platform_config->const_value('STORE_OWNER');
            $template_vars['EMAIL_FROM'] = $platform_config->const_value('EMAIL_FROM');
            $template_vars['STORE_OWNER_EMAIL_ADDRESS'] = $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS');
            $template_vars['STORE_ADDRESS'] = $platform_config->const_value('STORE_ADDRESS');
        }
        $patterns = array();
        $replace = array();
        foreach ($template_vars as $k => $v) {
            $patterns[] = "(##" . preg_quote($k) . "##)";
            $replace[] = str_replace('$', '/$/', $v);
        }

        $fallbackLanguages = [];
        $fallbackLanguages[] = \common\classes\language::get_id($platform_config->getDefaultLanguage());
        $fallbackLanguages[] = \common\helpers\Language::get_default_language_id();
        if ( $fallbackLanguages[1]==$fallbackLanguages[0] ) unset($fallbackLanguages[1]);

        $list = [];
        $Templates = OrdersCommentTemplate::find()
            ->where(['LIKE','visibility',",{$type},"])
            ->andWhere(['NOT LIKE','hide_for_platforms',",".intval($order->info['platform_id']).","])
            ->andWhere(['NOT LIKE','hide_from_admin',",".(int)$_SESSION['login_id'].","])
            ->andWhere(['OR',['LIKE','show_for_admin_group',',*,'],['LIKE','show_for_admin_group',','.(int)$_SESSION['access_levels_id'].',']])
            ->andWhere(['status'=>1])
            ->orderBy(['sort_order'=>SORT_ASC])
            ->all();
        foreach ($Templates as $Template){
            $textModel = $Template->getTexts()
                ->where(['language_id'=>$order->info['language_id']])
                ->andWhere(['!=','comment_template',''])
                ->one();
            if ( !$textModel ) {
                $textModel = $Template->getTexts()
                    ->where(['IN', 'language_id', $fallbackLanguages])
                    ->andWhere(['!=','comment_template',''])
                    ->orderBy(new Expression("IF(language_id='".(int)$fallbackLanguages[0]."',0,1)"))
                    ->one();
            }

            $comment = $textModel->comment_template;
            // {{
            if ( count($patterns)>0 ) {
                $comment = str_replace('/$/', '$', preg_replace($patterns, $replace, $textModel->comment_template));
            }
            // }}

            $list[] = [
                'id' => $Template->comment_template_id,
                'name' => $textModel->name,
                'comment' => $comment,
            ];
        }
        return $list;
    }

    public static function renderFor($type, $order)
    {
        if ( defined('COMMENT_TEMPLATE_STATUS') && COMMENT_TEMPLATE_STATUS=='False' ) return '';

        $variants = static::getCommentTemplateVariants($type, $order);
        if ( count($variants)==0 ) return '';

        $mapArray = [];
        $mapped_statuses = OrdersStatus::find()
            ->distinct()
            ->select(['orders_status_id','comment_template_id'])
            ->where(['!=','comment_template_id','0'])
            ->asArray()
            ->all();
        foreach ($mapped_statuses as $mapped_status){
            $mapArray[$mapped_status['orders_status_id']] = $mapped_status['comment_template_id'];
        }

        $items = [''=>''];
        $items_options = [];
        foreach ( $variants as $variant ) {
            $items[$variant['id']] = $variant['name'];
            $items_options[$variant['id']]['comment'] = $variant['comment'];
        }
        ?>
        <div class="f_row">
            <div class="f_td">
                <label><?php echo TEXT_COMMENT_TEMPLATE_LABEL; ?>:</label>
            </div>
            <div class="f_td">
                <?php echo Html::dropDownList('','', $items, ['data-templates'=>$items_options, 'class'=>'form-control', 'id'=>'commentTemplateSel']); ?>
            </div>
        </div>
        <script type="text/javascript">
            $(document).ready(function(){
                var $templateSelector = $('#commentTemplateSel');
                if ( $templateSelector.length==0 ) return;
                <?php if (count($mapArray)>0){ ?>

                var mapArray = <?php echo json_encode($mapArray); ?>;
                $($templateSelector.get(0).form).find('select[name="status"]').on('change',function(){
                    var new_status = $(this).val();
                    if ( mapArray[new_status] ) {
                        $templateSelector.val(mapArray[new_status]);
                        $templateSelector.trigger('change');
                    }
                });
                <?php } ?>
                $templateSelector.on('change',function(event){
                    var $select = $(event.target);
                    var templates = $select.data('templates');
                    if (templates[$select.val()]){
                        $select.get(0).form.elements['comments'].value = templates[$select.val()]['comment'];
                    }
                    $select.val('');
                });
            });
        </script>
        <?php
    }
}