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
 * Class m220902_075330_remove_quotation_stock_ind_and_terms
 */
class m220902_075330_remove_quotation_stock_ind_and_terms extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!\common\helpers\Acl::checkExtensionAllowed('Quotations')) {
            if (\common\models\ProductsStockIndicationText::findOne(['stock_indication_id' => 9, 'stock_indication_short_text' => 'RFQ'])) {
                \common\models\ProductsStockStatusesCrossLink::deleteAll(['stock_indication_id' => 9]);
                \common\models\ProductsStockIndicationText::deleteAll(['stock_indication_id' => 9]);
                \common\models\ProductsStockIndication::deleteAll(['stock_indication_id' => 9]);
            }
            if (\common\models\ProductsStockDeliveryTermsText::findOne(['stock_delivery_terms_id' => 9, 'stock_delivery_terms_short_text' => 'RFQ'])) {
                \common\models\ProductsStockDeliveryTermsText::deleteAll(['stock_delivery_terms_id' => 9]);
                \common\models\ProductsStockDeliveryTerms::deleteAll(['stock_delivery_terms_id' => 9]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m220902_075330_remove_quotation_stock_ind_and_terms cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220902_075330_remove_quotation_stock_ind_and_terms cannot be reverted.\n";

        return false;
    }
    */
}
