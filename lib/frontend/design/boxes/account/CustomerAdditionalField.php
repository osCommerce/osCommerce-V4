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

namespace frontend\design\boxes\account;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;
use yii\helpers\ArrayHelper;

class CustomerAdditionalField extends Widget
{

    public $file;
    public $params;
    public $settings;
    private $field;
    private $value;
    private $customersId;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        if (!\common\helpers\Acl::checkExtensionAllowed('CustomerAdditionalFields')) return '';

        global $languages_id;
        if ($this->params['customers_id']) {
            $this->customersId = $this->params['customers_id'];
        } else {
            $this->customersId = Yii::$app->user->id;
        }

        $field = \common\extensions\CustomerAdditionalFields\models\AdditionalFields::find()
            ->alias('f')
            ->select('f.*, fd.title')
            ->leftJoin('additional_fields_description fd', 'fd.additional_fields_id = f.additional_fields_id')
            ->where(['fd.language_id' => $languages_id]);

        if (!$this->settings[0]['field'] && $this->settings[0]['field_code']) {
            $field->andWhere(['f.additional_fields_code' => $this->settings[0]['field_code']]);
        } else {
            $field->andWhere(['f.additional_fields_id' => $this->settings[0]['field']]);
        }
        $field = $field->asArray()->one();
        $this->field = $field;


        $value = [];
        if ($field['additional_fields_id'] && $this->customersId){
            $value = \common\extensions\CustomerAdditionalFields\models\CustomersAdditionalFields::find()
                ->where([
                    'additional_fields_id' => $field['additional_fields_id'],
                    'customers_id' => $this->customersId,
                ])
                ->asArray()
                ->one();
        }
        if (!($value['value'] ?? false) && ArrayHelper::getValue($this->settings, [0, 'default_fields_id']) && $this->customersId){
            $value = \common\extensions\CustomerAdditionalFields\models\CustomersAdditionalFields::find()
                ->where([
                    'additional_fields_id' => $this->settings[0]['default_fields_id'],
                    'customers_id' => $this->customersId,
                ])
                ->asArray()
                ->one();

            if (!$value['value']) {
                $fieldType = \common\extensions\CustomerAdditionalFields\models\AdditionalFields::findOne(['additional_fields_id' => $this->settings[0]['default_fields_id']])->field_type;
                if ($fieldType && in_array($fieldType, ['customer_gender', 'customer_firstname', 'customer_lastname', 'customer_email_address', 'customer_phone', 'customer_email'])){

                    $customer = \common\models\Customers::findOne($this->customersId);
                    if ($customer) {
                        $type = str_replace('customer_', 'customers_', $fieldType);
                        $type = str_replace('customers_email', 'customers_email_address', $type);
                        $type = str_replace('customers_phone', 'customers_telephone', $type);
                        $value['value'] = $customer->$type;
                    }
                }
            }
        }
        $this->value = ($value['value'] ?? false);


        $valuesList = [];
        if ($field['field_type'] == 'radio' || $field['field_type'] == 'select') {
            $values = \common\extensions\CustomerAdditionalFields\models\AdditionalFieldsDescription::findOne($this->field['additional_fields_id'])->values;
            $valuesList = explode("\n", $values);
        }

        $this->addressBookValue();
        $this->customerDataValue();

        $countries = [];
        $iso = '';
        if (in_array($field['field_type'], ['country_id', 'addressbook_country_id', 'country_code', 'vat_number'])) {
            $countries = \common\helpers\Country::get_countries();
            $iso = \yii\helpers\ArrayHelper::map($countries, 'countries_id', 'countries_iso_code_2');
            Info::addJsData(['countries' => $countries]);
        }
        if ($field['field_type'] == 'file') {
            Info::includeJsFile('boxes/TradeForm/TradeFormFiles');
        }

        if (Info::isTotallyAdmin()) {
            $downloadAction = 'customers/download-customer-file';
        } elseif ($this->params['downloadUrl'] ?? false) {
            $downloadAction = $this->params['downloadUrl'];
        } else {
            $downloadAction = 'account/download-customer-file';
        }

        if ($this->settings[0]['pdf'] || $this->params['pdf']/* || $this->settings[0]['show'] || $this->params['show']*/){
            if ($field['field_type'] == 'checkbox') {
                $img = ''. 'themes/basic/img/';
                if ($this->value) {
                    $img .= 'checked.jpg';
                } else {
                    $img .= 'not-checked.jpg';
                }

                if ( is_file(DIR_FS_CATALOG . $img) ) {
                    if (ArrayHelper::getValue($this->settings, [0, 'show']) || ($this->params['show'] ?? false)) {
                        return '<img src="' . DIR_WS_CATALOG . $img . '" width="20">';
                    } else {
                        return '<img src="@' . base64_encode(file_get_contents(DIR_FS_CATALOG . $img)) . '" width="20">';
                    }
                }
            } elseif (in_array($field['field_type'], ['gender', 'customer_gender', 'addressbook_gender'])) {
                $title = \common\extensions\CustomerAdditionalFields\models\AdditionalFieldsDescription::findOne($this->field['additional_fields_id'])->title;
                $gendersList = \common\helpers\Address::getGendersList();
                return $this->value ? $gendersList[$this->value] : (Info::isAdmin() ? 'field: ' . $title : ' ');
            } elseif (in_array($field['field_type'], ['country_id', 'addressbook_country_id'])) {

                foreach ($countries as $country) {
                    if ($country['id'] == $this->value) {
                        return $country['text'];
                    }
                }

            } elseif (in_array($field['field_type'], ['radio'])) {

                $radio = '';
                $foundValue = false;
                foreach ($valuesList as $key => $item) {
                    $img = 'themes/basic/img/';
                    if ((string)$this->value == (string)$key) {
                        $img .= 'checked.jpg';
                        $foundValue = true;
                    } else {
                        $img .= 'not-checked.jpg';
                    }
                    if ( is_file(DIR_FS_CATALOG . $img) ) {
                        if (($this->settings[0]['show'] ?? false) || ($this->params['show'] ?? false)) {
                            $radio .= '<table width="100%" cellpadding="2"><tr><td style="width: 25px"><img src="' . DIR_WS_CATALOG . $img . '" width="20" style="vertical-align: top;" valign="top"></td><td width="100%">' . $item . '</td></tr></table></div>';
                        } else {
                            $radio .= '<table width="100%" cellpadding="2"><tr><td style="width: 25px"><img src="@' . base64_encode(file_get_contents(DIR_FS_CATALOG . $img)) . '" width="20" style="vertical-align: top;" valign="top"></td><td width="100%">' . $item . '</td></tr></table>';
                        }
                    }
                }
                if ($field['option'] && !$foundValue && $this->value) {
                    $img = 'themes/basic/img/checked.jpg';
                    if ( is_file(DIR_FS_CATALOG . $img) ) {
                        if ($this->settings[0]['show'] || $this->params['show']) {
                            $radio .= '<table width="100%"><tr><td style="width: 25px"><img src="' . DIR_WS_CATALOG . $img . '" width="20" style="vertical-align: top;" valign="top"></td><td width="100%">' . $this->value . '</td></tr></table>';
                        } else {
                            $radio .= '<table width="100%"><tr><td style="width: 25px"><img src="@' . base64_encode(file_get_contents(DIR_FS_CATALOG . $img)) . '" width="20" style="vertical-align: top;" valign="top"></td><td width="100%">' . $this->value . '</td></tr></table>';
                        }
                    }
                }

                return $radio;

            }  elseif (in_array($field['field_type'], ['file'])) {

                $files = '';
                foreach (explode("\n", $this->value) as $file){
                    if ($file){
                        $files .= '
                    <div class="tf-file">
                        <a href="' . Yii::$app->urlManager->createUrl([$downloadAction, 'file' => $file]) . '">' . $file . '</a>
                    </div>';
                    }
                }
                return $files;

            } elseif (in_array($field['field_type'], ['text']) && $field['option'] && $this->value) {
                $tmpVal = json_decode($this->value);

                $_values = '';
                foreach (json_decode($this->value) as $_value){
                    $_values .= '<table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr><td width="35%">' . $field['title'] . ':</td><td width="65%">' . $_value . '</td></tr>
                    </table><div style="font-size: 5px"></div>';
                }
                return $_values;

            } elseif (ArrayHelper::getValue($this->settings, [0, 'style_view']) == 'table') {

                $cellsRows = explode('*', $this->settings[0]['cells']);
                $cells = trim($cellsRows[0]) ?? 5;
                $rows = trim($cellsRows[1]) ?? 1;
                $cellSize = 15;
                $valueArrey = str_split($this->value);
                $table = '<table cellpadding="0" width="' . ($cellSize * $cells) . '" style="border-collapse: collapse; font-size: 10px; line-height: 150%"><tr>';
                $count = 0;
                $rowCount = 0;
                foreach ($valueArrey as $char) {
                    $table .= '<td style="border: 1px solid #000; width: ' . $cellSize . 'px; height: ' . $cellSize . 'px; line-height: ' . $cellSize . 'px; text-align: center">' . $char . '</td>';
                    $count++;
                    if ($count >= $cells) {
                        $rowCount ++;
                        if ($rowCount < $rows || count($valueArrey) > $cells) {
                            $table .= '</tr><tr>';
                            $count = 0;
                        }
                    }
                }
                for ($i = $count; $i < $cells; $i++) {
                    $table .= '<td style="border: 1px solid #000; width: ' . $cellSize . 'px; height: ' . $cellSize . 'px; line-height: ' . $cellSize . 'px; text-align: center"> </td>';
                }
                $rowCount ++;
                for ($j = $rowCount; $j < $rows; $j++) {
                    $table .= '</tr><tr>';
                    for ($i = 0; $i < $cells; $i++) {
                        $table .= '<td style="border: 1px solid #000; width: ' . $cellSize . 'px; height: ' . $cellSize . 'px; line-height: ' . $cellSize . 'px; text-align: center"> </td>';
                    }
                }

                $table .= '</tr></table>';
                return $table;

            } else {
                if ($this->params['pdf'] ?? false) {
                    $title = \common\extensions\CustomerAdditionalFields\models\AdditionalFieldsDescription::findOne($this->field['additional_fields_id'])->title;
                    return $this->value ? $this->value : (Info::isAdmin() ? 'field: ' . $title : ' ');
                } else {
                    return $this->value ? $this->value : ' ';
                }
            }
            return '';
        }
        if (in_array($field['field_type'], ['text']) && $field['option'] && $this->value) {
            $tmpVal = json_decode($this->value);
            $this->value = $tmpVal[0];
            $multifield = $tmpVal;
            Info::addJsData(['multifields' => [
                $field['additional_fields_id'] => $tmpVal
            ]]);
        }

        if ($this->settings[0]['show'] || $this->params['show']) {
            $file = 'customer-additional-field-show.tpl';
        } else {
            $file = 'customer-additional-field.tpl';
        }

        return IncludeTpl::widget(['file' => 'boxes/account/' . $file, 'params' => [
            'settings' => $this->settings,
            'id' => $this->id,
            'field' => $field,
            'value' => htmlspecialchars($this->value),
            'multifield' => $multifield,
            'countries' => $countries,
            'iso' => addslashes(json_encode($iso)),
            'valuesList' => $valuesList,
            'downloadAction' => $downloadAction,
            'customers_id' => $this->params['customers_id'] ? $this->params['customers_id'] : Yii::$app->request->get('customers_id'),
            'checked' => '<img src="' . DIR_WS_CATALOG . 'themes/basic/img/checked.jpg" width="20">',
            'notChecked' => '<img src="' . DIR_WS_CATALOG . 'themes/basic/img/not-checked.jpg" width="20">',
        ]]);
    }

    private function addressBookValue()
    {
        if (!in_array($this->field['field_type'], ['addressbook_gender', 'addressbook_firstname', 'addressbook_lastname', 'addressbook_company', 'addressbook_phone', 'addressbook_email', 'addressbook_postcode', 'addressbook_street_address', 'addressbook_suburb', 'addressbook_city', 'addressbook_state'])) {
            return;
        }

        $allFieldsThisType = \common\extensions\CustomerAdditionalFields\models\AdditionalFields::find()
            ->select(['additional_fields_id'])
            ->alias('af')
            ->where(['field_type' => $this->field['field_type']])
            ->leftJoin(\common\extensions\CustomerAdditionalFields\models\AdditionalFieldsGroup::tableName() . ' afg', 'af.additional_fields_group_id = afg.additional_fields_group_id')
            ->orderBy('afg.sort_order')
            ->asArray()
            ->all();

        $addressBook = \common\helpers\Customer::get_address_book_data($this->customersId);

        if (!is_array($allFieldsThisType)) {
            return;
        }
        foreach ($allFieldsThisType as $key => $addField) {
            if ($addField['additional_fields_id'] == $this->field['additional_fields_id'] && $addressBook[$key]['firstname']) {
                $this->value = $addressBook[$key][str_replace('addressbook_', '', $this->field['field_type'])];
                return;
            }
        }
    }

    private function customerDataValue()
    {
        if (!in_array($this->field['field_type'], ['customer_gender', 'customer_firstname', 'customer_lastname', 'customer_email_address', 'customer_phone', 'customer_email'])) {
            return;
        }

        $type = str_replace('customer_', 'customers_', $this->field['field_type']);
        $type = str_replace('customers_email', 'customers_email_address', $type);
        $type = str_replace('customers_phone', 'customers_telephone', $type);

        $this->value = \common\models\Customers::findOne($this->customersId)->$type;
    }
}
