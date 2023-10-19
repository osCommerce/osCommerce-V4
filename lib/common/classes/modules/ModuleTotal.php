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

namespace common\classes\modules;

abstract class ModuleTotal extends Module {
    
    protected static $adjusting;
    protected $processing_order = [];

    public function setProcessingOrder($processing_order)
    {
        $this->processing_order = $processing_order;
    }

    public function process() {
        
    }

    function visibility($platform_id = 0, $area = '') {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ModulesVisibility', 'allowed')) {
            return $ext::visibility($platform_id, $area, $this);
        }
        return true;
    }
    
    function displayText($platform_id = 0, $area = '', $totals = '') {
        
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ModulesVisibility', 'allowed')) {
            $totals = $ext::displayText($platform_id, $area, $totals, $this);
        }
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ModulesZeroPrice', 'allowed')) {
            $totals = $ext::displayText($platform_id, $area, $totals, $this);
        }
        return $totals;
    }

    function getDefaultTitle() {
        return TEXT_DEFAULT;
    }
    
    function getDefault($visibility_id = 0, $checked = false) {
        return tep_draw_radio_field('visibility_vat[' . $visibility_id . ']', 0, $checked);
    }
    
    function getIncVATTitle() {
        return TEXT_INC_VAT;
    }
    
    function getIncVAT($visibility_id = 0, $checked = false) {
        return tep_draw_radio_field('visibility_vat[' . $visibility_id . ']', 1, $checked);
    }
    
    function getExcVATTitle() {
        return TEXT_EXC_VAT;
    }
    
    function getExcVAT($visibility_id = 0, $checked = false) {
        return tep_draw_radio_field('visibility_vat[' . $visibility_id . ']', -1, $checked);
    }
    
    function getVisibility($platform_id) {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ModulesVisibility', 'allowed')) {
            return $ext::getVisibility($platform_id, $this);
        }
        $response = '<br><br><table width="50%" class="dis_module"><thead><tr><th>' . TEXT_VISIBILITY_ON_PAGES . '</th><th style="text-align: center">' . $this->getIncVATTitle() . '</th><th style="text-align: center">' . $this->getExcVATTitle() . '</th><th style="text-align: center">' . $this->getDefaultTitle() . '</th><th style="text-align: center">' . SHOW_TOP_LINE . '</th></tr></thead><tbody>';
        $visibility_query = tep_db_query("SELECT * FROM " . TABLE_VISIBILITY . " where 1 order by visibility_constant");
        while ($visibility = tep_db_fetch_array($visibility_query)) {
            if (!\common\helpers\Extensions::isVisibility($visibility['visibility_constant'])) continue;
            $response .= '<tr><td><input type="checkbox" disabled>';
            $response .= '&nbsp;' . constant($visibility['visibility_constant']) . '<br>';
            $response .= '</td><td style="text-align: center"><input type="radio" disabled>';
            $response .= '</td><td style="text-align: center"><input type="radio" disabled>';
            $response .= '</td><td style="text-align: center"><input type="radio" disabled>';
            $response .= '</td><td style="text-align: center"><input type="checkbox" disabled>';
            $response .= '</td></tr>';
        }
        $response .= '</tbody></table>';
        return $response;
    }

    function setVisibility() {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ModulesVisibility', 'allowed')) {
            return $ext::setVisibility($this);
        }
        return true;
    }
    
    function getZeroPrice($platform_id) {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ModulesZeroPrice', 'allowed')) {
            return \common\helpers\Modules::getInfoLinkForExtension('ModulesZeroPrice') .$ext::getZeroPrice($platform_id, $this);
        }
    }
    
    function setZeroPrice() {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ModulesZeroPrice', 'allowed')) {
            return $ext::setZeroPrice($this);
        }
        return true;
    }
       
}
