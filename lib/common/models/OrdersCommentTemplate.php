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

namespace common\models;


use yii\db\ActiveRecord;

class OrdersCommentTemplate extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'orders_comment_template';
    }

    public function getVisibilityArray()
    {
        return preg_split('/,/',$this->visibility,-1,PREG_SPLIT_NO_EMPTY);
    }

    public function getHideForPlatformsArray()
    {
        return preg_split('/,/',$this->hide_for_platforms,-1,PREG_SPLIT_NO_EMPTY);
    }

    public function getHideFromAdminArray()
    {
        return preg_split('/,/',$this->hide_from_admin,-1,PREG_SPLIT_NO_EMPTY);
    }

    public function getShowForAdminGroupsArray()
    {
        if ( strpos($this->show_for_admin_group,',*,')!==false ) {
            $AdminGroupsArray = [];
            foreach (AccessLevels::find()->select(['access_levels_id'])->asArray(true)->all() as $item){
                $AdminGroupsArray[] = $item['access_levels_id'];
            }
            return $AdminGroupsArray;
        }
        return preg_split('/,/',$this->show_for_admin_group,-1,PREG_SPLIT_NO_EMPTY);
    }

    public function getTexts()
    {
        return $this->hasMany(OrdersCommentTemplateText::className(), ['comment_template_id' => 'comment_template_id']);
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ( strpos($this->show_for_admin_group,',*,')===false ) {
            $AllAdminGroupsArray = [];
            foreach (AccessLevels::find()->select(['access_levels_id'])->asArray(true)->all() as $item){
                $AllAdminGroupsArray[$item['access_levels_id']] = $item['access_levels_id'];
            }
            foreach ( preg_split('/,/', $this->show_for_admin_group, -1, PREG_SPLIT_NO_EMPTY) as $saveId ){
                if ( isset($AllAdminGroupsArray[$saveId]) ) unset($AllAdminGroupsArray[$saveId]);
            }
            if( count($AllAdminGroupsArray)==0 ) {
                $this->show_for_admin_group = ',*,';
            }
        }
        return true;
    }


    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        OrdersCommentTemplateText::deleteAll(['comment_template_id'=>$this->comment_template_id]);

        return true;
    }


}