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
 * Class m230208_095749_paypal_text_upon_invoice
 */
class m230208_095749_paypal_text_upon_invoice extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('payment', [
          'MODULE_PAYMENT_PAYPAL_PARTNER_TEXT_UPON_INVOCE' => [
            'en' => 'By clicking on the button, you agree to the <a href="https://www.ratepay.com/legal-payment-terms" title="external link" target="_blank" rel="noopener noreferrer" class="dx-external-href" pa-marked="1">terms of payment</a> and <a href="https://www.ratepay.com/legal-payment-dataprivacy" title="external link" target="_blank" rel="noopener noreferrer" class="dx-external-href" pa-marked="1">performance of a risk check</a> from the payment partner, Ratepay. You also agree to PayPal’s <a href="https://www.paypal.com/us/webapps/mpp/ua/privacy-full" title="external link" target="_blank" rel="noopener noreferrer" class="dx-external-href" pa-marked="1">privacy statement</a>. If your request to purchase upon invoice is accepted, the purchase price claim will be assigned to Ratepay, and you may only pay Ratepay, not the merchant.',
            'de' => 'Mit Klicken auf den Button akzeptieren Sie die <a href="https://www.ratepay.com/legal-payment-terms">Ratepay Zahlungsbedingungen</a> und erklären sich mit der Durchführung einer <a href="https://www.ratepay.com/legal-payment-dataprivacy">Risikoprüfung durch Ratepay</a>, unseren Partner,  einverstanden. Sie akzeptieren auch PayPals <a href="https://www.paypal.com/de/webapps/mpp/ua/privacy-full?locale.x=de_DE">Datenschutzerklärung</a>. Falls Ihre Transaktion per Kauf auf Rechnung erfolgreich abgewickelt werden kann, wird der Kaufpreis an Ratepay abgetreten und Sie dürfen nur an Ratepay überweisen, nicht an den Händler.',
          ]
        ]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
//        echo "m230208_095749_paypal_text_upon_invoice cannot be reverted.\n";

  //      return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230208_095749_paypal_text_upon_invoice cannot be reverted.\n";

        return false;
    }
    */
}
