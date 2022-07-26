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

namespace frontend\design\boxes\contact;

use frontend\design\Info;
use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\JsonLd;

class Contacts extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {

        $data = Info::platformData();
        $address = $data;
        $address['name'] = '';
        $address['reg_number'] = '';

        if ($this->settings[0]['time_format'] == '24' && is_array($data['open'])){
            foreach ($data['open'] as $key => $item){
                $data['open'][$key]['time_from'] = date("G:i", strtotime($item['time_from']));
                $data['open'][$key]['time_to'] = date("G:i", strtotime($item['time_to']));
            }
        }

        $addressTxt =  \common\helpers\Address::address_format(\common\helpers\Address::get_address_format_id($data['country_id']), $address, 0, ' ', '<br>', true);
        if (isset($this->settings[0]['tag_street_address']) && $this->settings[0]['tag_street_address']) {
            $addressTxt = str_replace('<!--street start-->', '<' . $this->settings[0]['tag_street_address'] . '>', $addressTxt);
            $addressTxt = str_replace('<!--street end-->', '</' . $this->settings[0]['tag_street_address'] . '>', $addressTxt);
        } else {
            $this->settings[0]['tag_street_address'] = false;
        }
        if (isset($this->settings[0]['tag_city']) && $this->settings[0]['tag_city']) {
            $addressTxt = str_replace('<!--city start-->', '<' . $this->settings[0]['tag_city'] . '>', $addressTxt);
            $addressTxt = str_replace('<!--city end-->', '</' . $this->settings[0]['tag_city'] . '>', $addressTxt);
        } else {
            $this->settings[0]['tag_city'] = false;
        }
        if (isset($this->settings[0]['tag_state']) && $this->settings[0]['tag_state']) {
            $addressTxt = str_replace('<!--state start-->', '<' . $this->settings[0]['tag_state'] . '>', $addressTxt);
            $addressTxt = str_replace('<!--state end-->', '</' . $this->settings[0]['tag_state'] . '>', $addressTxt);
        } else {
            $this->settings[0]['tag_state'] = false;
        }
        if (isset($this->settings[0]['tag_country']) && $this->settings[0]['tag_country']) {
            $addressTxt = str_replace('<!--country start-->', '<' . $this->settings[0]['tag_country'] . '>', $addressTxt);
            $addressTxt = str_replace('<!--country end-->', '</' . $this->settings[0]['tag_country'] . '>', $addressTxt);
        } else {
            $this->settings[0]['tag_country'] = false;
        }
        if (isset($this->settings[0]['tag_post_code']) && $this->settings[0]['tag_post_code']) {
            $addressTxt = str_replace('<!--postcode start-->', '<' . $this->settings[0]['tag_post_code'] . '>', $addressTxt);
            $addressTxt = str_replace('<!--postcode end-->', '</' . $this->settings[0]['tag_post_code'] . '>', $addressTxt);
        } else {
            $this->settings[0]['tag_post_code'] = false;
        }
        if (isset($this->settings[0]['tag_company']) && $this->settings[0]['tag_company']) {
            $addressTxt = str_replace('<!--company start-->', '<' . $this->settings[0]['tag_company'] . '>', $addressTxt);
            $addressTxt = str_replace('<!--company end-->', '</' . $this->settings[0]['tag_company'] . '>', $addressTxt);
        } else {
            $this->settings[0]['tag_company'] = false;
        }
        if (isset($this->settings[0]['tag_suburb']) && $this->settings[0]['tag_suburb']) {
            $addressTxt = str_replace('<!--suburb start-->', '<' . $this->settings[0]['tag_suburb'] . '>', $addressTxt);
            $addressTxt = str_replace('<!--suburb end-->', '</' . $this->settings[0]['tag_suburb'] . '>', $addressTxt);
        } else {
            $this->settings[0]['tag_suburb'] = false;
        }
        if (isset($this->settings[0]['tag_phone_label']) && $this->settings[0]['tag_phone_label']) {
        } else {
            $this->settings[0]['tag_phone_label'] = false;
        }
        if (isset($this->settings[0]['tag_phone']) && $this->settings[0]['tag_phone']) {
        } else {
            $this->settings[0]['tag_phone'] = false;
        }
        if (isset($this->settings[0]['add_link_on_phone']) && $this->settings[0]['add_link_on_phone']) {
        } else {
            $this->settings[0]['add_link_on_phone'] = false;
        }
        if (isset($this->settings[0]['tag_email_label']) && $this->settings[0]['tag_email_label']) {
        } else {
            $this->settings[0]['tag_email_label'] = false;
        }
        if (isset($this->settings[0]['tag_email']) && $this->settings[0]['tag_email']) {
        } else {
            $this->settings[0]['tag_email'] = false;
        }
        if (isset($this->settings[0]['add_link_on_email']) && $this->settings[0]['add_link_on_email']) {
        } else {
            $this->settings[0]['add_link_on_email'] = false;
        }
        if (isset($this->settings[0]['use_at_in_email']) && $this->settings[0]['use_at_in_email']) {
        } else {
            $this->settings[0]['use_at_in_email'] = false;
        }
        if (isset($this->settings[0]['tag_opening_hours_label']) && $this->settings[0]['tag_opening_hours_label']) {
        } else {
            $this->settings[0]['tag_opening_hours_label'] = false;
        }
        if (isset($this->settings[0]['tag_opening_hours']) && $this->settings[0]['tag_opening_hours']) {
        } else {
            $this->settings[0]['tag_opening_hours'] = false;
        }

        self::jsonLdData($data, $this->settings[0]);


    return IncludeTpl::widget(['file' => 'boxes/contact/contacts.tpl', 'params' => [
        'data' => $data,
        'phone' => '+' . preg_replace("/[^0-9]/i", "", $data['telephone']),
        'address' => $addressTxt,
        'settings' => $this->settings
    ]]);

  }

  public static function jsonLdData($data, $settings){

      $address_format_id = \common\helpers\Address::get_address_format_id($data['country_id']);
      $addressFormat = \common\helpers\Address::getFormatById($address_format_id);
      $addressFormatArr = json_decode($addressFormat->address_format);
      $addressFormatArrFlat = [];
      foreach ($addressFormatArr as $row) {
          $addressFormatArrFlat = array_merge($addressFormatArrFlat, $row);
      }

      $ldAddress['@type'] = 'PostalAddress';
      if (isset($data['street_address']) && in_array('street_address', $addressFormatArrFlat)) {
          $ldAddress['streetAddress'] = $data['street_address'];
      }
      if (isset($data['city']) && in_array('city', $addressFormatArrFlat)) {
          $ldAddress['addressLocality'] = $data['city'];
      }
      if (isset($data['state']) && in_array('state', $addressFormatArrFlat)) {
          $ldAddress['addressRegion'] = $data['state'];
      }
      if (isset($data['postcode']) && in_array('postcode', $addressFormatArrFlat)) {
          $ldAddress['postalCode'] = $data['postcode'];
      }
      if (isset($data['country']) && in_array('country', $addressFormatArrFlat)) {
          $ldAddress['addressCountry'] = $data['country'];
      }
      if (isset($data['suburb']) && in_array('suburb', $addressFormatArrFlat)) {
          $ldAddress['addressLocality'] = $data['suburb'];
      }

      JsonLd::addData(['Organization' => [
          'address' => $ldAddress
      ]], ['Organization', 'address']);

      if (isset($data['company_vat']) && in_array('company_vat', $addressFormatArrFlat)) {
          JsonLd::addData(['Organization' => [
              'vatID' => $data['entry_company_vat']
          ]], ['Organization', 'vatID']);
      }

      if (isset($data['telephone'])) {
          JsonLd::addData(['Organization' => [
              'telephone' => $data['telephone']
          ]], ['Organization', 'telephone']);
      }
      if (isset($data['email_address'])) {
          JsonLd::addData(['Organization' => [
              'email' => $data['email_address']
          ]], ['Organization', 'email']);
      }
      if (isset($data['reg_number'])) {
          JsonLd::addData(['Organization' => [
              'leiCode' => $data['reg_number']
          ]], ['Organization', 'leiCode']);
      }
      if (isset($data['entry_company_vat'])) {
          JsonLd::addData(['Organization' => [
              'vatID' => $data['entry_company_vat']
          ]], ['Organization', 'vatID']);
      }

      $jsonOurs = [];
      if (isset($data['open']) && is_array($data['open'])) {
        foreach ($data['open'] as $key => $item) {
            $jsonOurs[] = [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => $item['days_arr'],
                'opens' => date("G:i", strtotime($item['time_from'])),
                'closes' => date("G:i", strtotime($item['time_to'])),
            ];
        }
      }

      JsonLd::addData(['Organization' => [
          'openingHoursSpecification' => $jsonOurs
      ]], ['Organization', 'openingHoursSpecification']);
  }
}