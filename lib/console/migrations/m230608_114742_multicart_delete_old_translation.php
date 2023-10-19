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
 * Class m230608_114742_multicart_delete_old_translation
 */
class m230608_114742_multicart_delete_old_translation extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (method_exists($this, 'isOldExtension')) {
            if (!$this->isOldExtension('MultiCart')) {
                $this->removeTranslation('main', [
                    'TEXT_CREATE_NEW_CART',
                    'TEXT_COPY_TO_NEW_CART',
                    'TEXT_CHANGE_CART_NAME',
                    'TEXT_CLEAR_CART',
                    'TEXT_DELETE_CART',
                    'TEXT_COPY_TO_CART',
                    'TEXT_MOVE_TO_CART',
                    'TEXT_DELETE_FROM_CART',
                    'TEXT_VIEW_CART',
                    'TEXT_CART_ACTIONS',
                    'TEXT_PRODUCT_ACTIONS',
                    'TEXT_CLEAR_CURENT_CART',
                    'TEXT_REPLACE_SAME_PRODUCTS',
                    'TEXT_APPEND_SAME_PRODUCTS',
                    'ERROR_MULTICART_CART_NAME_NOT_CHANGED',
                    'TEXT_CART_NAME_CHANGED',
                    'TEXT_CART_COPIED',
                    'ERROR_MULTICART_CART_NOT_COPIED',
                    'ERROR_MULTICART_EMPTY_CART_NAME',
                    'TEXT_COMPARE_CART',
                ]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addTranslation('main', [
            'TEXT_CREATE_NEW_CART' => 'Create New Cart',
            'TEXT_COPY_TO_NEW_CART' => 'Save',
            'TEXT_CHANGE_CART_NAME' => 'Change Cart Name',
            'TEXT_CLEAR_CART' => 'Clear Cart',
            'TEXT_DELETE_CART' => 'Delete Cart',
            'TEXT_COPY_TO_CART' => 'Copy to Cart',
            'TEXT_MOVE_TO_CART' => 'Move to Cart',
            'TEXT_DELETE_FROM_CART' => 'Delete from Cart',
            'TEXT_VIEW_CART' => 'View Cart',
            'TEXT_CART_ACTIONS' => 'Cart Actions',
            'TEXT_PRODUCT_ACTIONS' => 'Product Actions',
            'TEXT_CLEAR_CURENT_CART' => 'Clear current cart',
            'TEXT_REPLACE_SAME_PRODUCTS' => 'Replace same products',
            'TEXT_APPEND_SAME_PRODUCTS' => 'Append same products',
            'ERROR_MULTICART_CART_NAME_NOT_CHANGED' => 'Error: Cart Name was not changed',
            'TEXT_CART_NAME_CHANGED' => 'New Cart Name Applied',
            'TEXT_CART_COPIED' => 'Cart was copied',
            'ERROR_MULTICART_CART_NOT_COPIED' => 'Error: Cart was not copied',
            'ERROR_MULTICART_EMPTY_CART_NAME' => 'Error: Cart name is empty',
            'TEXT_COMPARE_CART' => 'Compare Carts',
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230608_114742_multicart_delete_old_translation cannot be reverted.\n";

        return false;
    }
    */
}
