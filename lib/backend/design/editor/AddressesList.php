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

namespace backend\design\editor;

use Yii;
use yii\base\Widget;

class AddressesList extends Widget
{

    public $file;
    public $params;
    public $settings;
    public $manager;
    public $type; //shipping or billing
    public $mode;
    public $ab_id;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        if (!is_object($this->manager)) throw new \Exception ('order manager should be defined');
        if (!in_array($this->mode, ['single', 'select', 'edit'])) throw new \Exception ('mode type should be defined');
        
        $this->params['manager'] = $this->manager;
        $this->params['type'] = $this->type;
        $this->params['mode'] = $this->mode;

        if ($this->ab_id) {
            $_selectedABid = $this->ab_id;
        } elseif ($this->type == 'shipping'){
            $_selectedABid = $this->manager->getSendto();
        } else {
            $_selectedABid = $this->manager->getBillto();
        }
        $this->params['selected_ab_id'] = $_selectedABid;
        
        if ($this->mode == 'single'){
            $this->params['address'] = $this->manager->getCustomersAddress($_selectedABid, true, true);
            $this->_defineForm();
            if (is_null($this->params['address']) || !$this->params['model']->customerAddressIsReady() || $this->params['model']->hasErrors()){
                $this->params['error'] = true;
            }
        } elseif ($this->mode == 'select'){
            $this->ab_id = $_selectedABid;
            $this->_defineForm();
            if (!$this->params['model']->customerAddressIsReady()){
                $this->params['error'] = true;
            }
            $this->params['addresses'] = $this->manager->getCustomersAddresses(true, true, $this->type);
            if (!count($this->params['addresses'])) {
                $this->params['mode'] = 'edit';
            }

        } else {
            $this->_defineForm();
        }
        if ($this->params['mode'] == 'edit'){
            $this->params['postcoder'] = ($ext = \common\helpers\Acl::checkExtensionAllowed('AddressLookup')) ? $ext::getTool() : null;
        }
        return $this->render('addresses-list', $this->params);
    }
    
    private function _defineForm(){
        if ($this->type == 'shipping'){
                $this->params['model'] = $this->manager->getShippingForm($this->ab_id);
            } else {
                $this->params['model'] = $this->manager->getBillingForm($this->ab_id);
            }
    }
}