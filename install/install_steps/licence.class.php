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

class licence extends install_generic {

    public $next_button = 'accept';

    public function get_output() {
        $license_text = file_get_contents($this->root_path . 'LICENSE.TXT');
        $strOut = $this->lang['welcome'];
        $strOut .= '<br /><br /><h1>' . $this->lang['licence'] . '</h1>';
        $strOut .= '<div class="licence"><pre>' . $license_text . '</pre></div>';

        return $strOut;
    }

    public function get_filled_output() {
        $license_text = file_get_contents($this->root_path . 'LICENSE.TXT');
        $strOut = $this->lang['welcome'];
        $strOut .= '<br /><br /><h1>' . $this->lang['licence'] . '</h1>';
        $strOut .= '<div class="licence"><pre>' . $license_text . '</pre></div>';

        return $strOut;
    }

    public function parse_input() {
        return true;
    }

}
