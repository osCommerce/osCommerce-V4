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

if(isset($mysql_error_dump) && is_array($mysql_error_dump) && count($mysql_error_dump)>0) {
    if ( defined('YII_DEBUG') && YII_DEBUG ) {
        ?>
        <div class="popup-box-wrap popup-box-wrap-mysql">
            <div class="around-pop-up"></div>
            <div class="popup-box">
                <div class="pop-up-close"></div>
                <div class="pop-up-content">
                    <div class="popup-heading">Error</div>
                    <div class="popup-content">
                        <div id="id_mysql_error">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td class="heading">
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td class="heading">You have an error in your SQL syntax</td>
                                                <td class="close"></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="error">
                                        <?php
                                        for ($i = 0; $i < count($mysql_error_dump); $i++) {
                                            if ($i != 0) echo '<hr>';
                                            echo $mysql_error_dump[$i];
                                        }
                                        tep_session_unregister('mysql_error_dump');
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function () {
                $('.pop-up-close').click(function () {
                    $('.popup-box:last').trigger('popup.close');
                    $('.popup-box-wrap:last').remove();
                    return false
                });
            });

        </script>

        <?php
    }else{
        tep_session_unregister('mysql_error_dump');
    }
}

// close session (store variables)
if (session_status() === PHP_SESSION_ACTIVE) {tep_session_close();}

  if (STORE_PAGE_PARSE_TIME == 'true') {
    if (!is_object($logger)) $logger = new logger;
    echo $logger->timer_stop(DISPLAY_PAGE_PARSE_TIME);
  }
?>
