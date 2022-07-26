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

namespace common\components\google\modules;

abstract class AbstractGoogle implements GoogleInterface {
    
    protected $provider;

    abstract public function getParams();

    abstract public function renderWidget();
    
    public function setProvider(\common\components\google\ModuleProvider $provider){
        $this->provider = $provider;
    }

    public function loaded(array $params) {

        $elements = $this->config[$this->code];
        foreach ($elements as $key => $element) {
            if (isset($params[$key])) {
                if ($key == 'fields') {
                    for ($i = 0; $i < count($element); $i++) {
                        if ($elements[$key][$i]['type'] == 'checkbox') {
                            $elements[$key][$i]['value'] = 0;
                            if (!isset($params[$key][$i])) {
                                $params[$key][$i] = [$elements[$key][$i]['name'] => 0];
                            } else {
                                $params[$key][$i] = [$elements[$key][$i]['name'] => 1];
                            }
                        }
                        if (is_array($params[$key][$i])) {
                            foreach ($params[$key][$i] as $field => $value) {
                                if ($field == $elements[$key][$i]['name']) {
                                    if ($elements[$key][$i]['type'] == 'checkbox') {
                                        $elements[$key][$i]['value'] = $value;
                                    } else {
                                        $elements[$key][$i]['value'] = $value;
                                    }
                                }
                            }
                        }
                    }
                } else if ($key == 'type') {
                    $elements[$key]['selected'] = $params[$key];
                } elseif ($key == 'pages') {
                    $elements[$key] = $params[$key];
                }
            }
        }
        $this->config[$this->code] = $elements;

        return $this;
    }

    public function render() {
        return \common\components\google\widgets\ModuleWidget::widget(['module' => $this]);
    }
    
    public function overloadConfig($config, array $params = []) {
        $this->config = unserialize($config);
        if ($params){
            $this->config = array_merge($params, $this->config);
        }
        return $this;
    }
    
    public function getAvailablePages() {
        $_pages = [];
        if (isset($this->config[$this->code]['pages'])) {
            foreach ($this->config[$this->code]['pages'] as $key => $_page) {
                $_pages[$key] = strtolower($_page);
            }
        }
        return (count($_pages) ? $_pages : ['all']);
    }
    
    public function getPriority() {
        return (isset($this->config[$this->code]['priority']) ? $this->config[$this->code]['priority'] : 99);
    }

    public function parseFields($fields) {
      $ret = [];
      if (is_array($fields)) {
        foreach ($fields as $field) {
          $ret[$field['name']]  = $field['value'];
        }
      }
      return $ret;
    }

    public function isTrackingAdded($orderId, $type='') {
      $m = \common\models\EcommerceTracking::find()->andWhere([
        'orders_id' => $orderId,
        'services' => $this->code,
        'message_type' => (!empty($type)?$type:'purchase'),
      ]);
      return $m->exists();
    }

/**
 *
 * @param array $data [orders_id => NNN , < 'via'=>ssss> ]
 */
    public function saveTracking($data) {
      $m = new \common\models\EcommerceTracking();
      $m->loadDefaultValues();
      try {
        $m->setAttributes(array_merge(
                [
                  'date_added' => date(\common\helpers\Date::DATABASE_DATETIME_FORMAT),
                  'services' => $this->code,
                  'message_type' => 'purchase',
                  'via' => 'js',
                  'extra_info' => ''
                ], $data), false);

        $m->save(false);
        

      } catch (\Exception $ex) {
        \Yii::warning(" #### " .print_r($ex->getMessage(), 1), 'TLDEBUG');
      }
    }

}
