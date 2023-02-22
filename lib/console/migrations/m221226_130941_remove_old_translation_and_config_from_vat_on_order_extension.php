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

use common\classes\Migration;

/**
 * Class m221226_130941_remove_old_translation_and_config_from_vat_on_order_extension
 */
class m221226_130941_remove_old_translation_and_config_from_vat_on_order_extension extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (method_exists($this, 'isOldExtension') && !$this->isOldExtension('VatOnOrder')) {

            $old_tax = false;
            if (class_exists('\common\modules\orderTotal\ot_tax')) {
                $ot_tax = new \common\modules\orderTotal\ot_tax();
                $old_tax = in_array('MODULE_ORDER_TOTAL_TAX_VAT_ID', $ot_tax->configure_keys());
                unset($ot_tax);
            }
            if (!$old_tax) {
                $this->removePlatformConfigurationKeys(['MODULE_ORDER_TOTAL_TAX_VAT_ID', 'MODULE_ORDER_TOTAL_TAX_VAT_ID_STRICT']);
            }

            $this->removeTranslation('main', [
                'TEXT_VALID',
                'TEXT_FORMAT_OK',
                'TEXT_NOT_VALID',
                'TEXT_OTHER_COUNTRY',
                'TEXT_NOT_VALIDATED',
            ]);
            $this->removeTranslation('admin/main', [
                'TEXT_VALID',
                'TEXT_FORMAT_OK',
                'TEXT_NOT_VALID',
                'TEXT_OTHER_COUNTRY',
                'TEXT_NOT_VALIDATED',
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221226_130941_remove_old_translation_and_config cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221226_130941_remove_old_translation_and_config cannot be reverted.\n";

        return false;
    }
    */
}
