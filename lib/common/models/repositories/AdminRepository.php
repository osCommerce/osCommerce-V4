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
namespace common\models\repositories;
use common\models\Admin;


/**
 * Class AdminRepository
 * @package common\models\repositories
 */
class AdminRepository
{
    /**
     * @param array|int $id
     * @return array|Admin|null|\yii\db\ActiveRecord
     */
    public function findById($id)
    {
        $admin = Admin::find()->where(['admin_id'=> $id])->limit(1)->one();
        return $admin;
    }

    /**
     * @param string $email
     * @return array|Admin|null|\yii\db\ActiveRecord
     */
    public function findByEmail($email)
    {
        $admin = Admin::find()->where(['admin_email_address'=> $email])->limit(1)->one();
        return $admin;
    }

    public function getAdminData($admin_id) {
        $admin = Admin::find()
            ->select('admin.*,access_levels.access_levels_name')
            ->with(['posPlatform','posCurrency'])
            ->joinWith('accesslevel',false)
            ->where(['admin_id' => $admin_id])
            ->limit(1)
            ->asArray()
            ->one();

        if (empty($admin)){
            throw new NotFoundException('Wrong data.');
        }
        return $admin;
    }
    /**
     * @param array|int $id
     * @return array|Admin|null|\yii\db\ActiveRecord
     */
    public function get($id)
    {
        if (!$admin = $this->findById($id)) {
            throw new NotFoundException('Admin is not found.');
        }
        return $admin;
    }
    /**
     * @param Admin $admin
     */
    public function save(Admin $admin)
    {
        if (!$admin->save()) {
            throw new \RuntimeException('Admin saving  error.');
        }
    }

    /**
     * @param Admin $admin
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function remove(Admin $admin)
    {
        if (!$admin->delete()) {
            throw new \RuntimeException('Admin remove error.');
        }
    }

    /**
     * @param Admin $admin
     * @param array $params
     * @param bool $safeOnly
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit(Admin $admin,$params = [], $safeOnly = false)
    {
        foreach ($params as $attribute => $param){
            if(!$admin->hasAttribute($attribute)){
                unset($params[$attribute]);
            }
        }
        $admin->setAttributes($params, $safeOnly);
        if(!$admin->update(false, array_keys($params))){
            return $admin->getErrors();
        }
        return true;
    }
    public function isAssignedCustomer($adminPosId,$customerId){
        return Admin::find()->where(['AND',['customers_id'=>$customerId],['admin_id'=>$adminPosId]])->exists();
    }
}
