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

namespace common\widgets;


class WarningWidget extends \yii\bootstrap\Widget
{
    public function init()
    {
        parent::init();

    }

    public function run(){
        $messageStack = \Yii::$container->get('message_stack');

    /*if (WARN_INSTALL_EXISTENCE == 'true') {
      if (file_exists(dirname($_SERVER['SCRIPT_FILENAME']) . '/install')) {
        $messageStack->add(sprintf(WARNING_INSTALL_DIRECTORY_EXISTS, dirname($_SERVER['SCRIPT_FILENAME'])), 'header', 'warning');
      }
    }*/

  /*/ check if the configure.php file is writeable
    if (WARN_CONFIG_WRITEABLE == 'true') {
      if ( (file_exists(dirname($_SERVER['SCRIPT_FILENAME']) . '/includes/configure.php')) && (is_writeable(dirname($_SERVER['SCRIPT_FILENAME']) . '/includes/configure.php')) ) {
        $messageStack->add(sprintf(WARNING_CONFIG_FILE_WRITEABLE, dirname($_SERVER['SCRIPT_FILENAME'])), 'header', 'warning');
      }
    }*/

  // check if the session folder is writeable
    if (WARN_SESSION_DIRECTORY_NOT_WRITEABLE == 'true') {
      if (STORE_SESSIONS == '') {
        if (!is_dir(tep_session_save_path())) {
          $messageStack->add(sprintf(WARNING_SESSION_DIRECTORY_NON_EXISTENT, basename(tep_session_save_path())), 'header', 'warning');
        } elseif (!is_writeable(tep_session_save_path())) {
          $messageStack->add(sprintf(WARNING_SESSION_DIRECTORY_NOT_WRITEABLE, basename(tep_session_save_path())), 'header', 'warning');
        }
      }
    }


    if ($ext = \common\helpers\Extensions::isAllowed('Maintenance'))
    {
        $ext::getWarnMessage();
    }

  // check session.auto_start is disabled
    /*if ( (function_exists('ini_get')) && (WARN_SESSION_AUTO_START == 'true') ) {
      if (ini_get('session.auto_start') == '1') {
        $messageStack->add(WARNING_SESSION_AUTO_START, 'header', 'warning');
      }
    }*/

    /*if ( (WARN_DOWNLOAD_DIRECTORY_NOT_READABLE == 'true') && (DOWNLOAD_ENABLED == 'true') ) {
      if (!is_dir(DIR_FS_DOWNLOAD)) {
        $messageStack->add(WARNING_DOWNLOAD_DIRECTORY_NON_EXISTENT, 'header', 'warning');
      }
    }*/

    if ($messageStack->size('header') > 0) {
      echo $messageStack->output('header');
    }

    if (isset($_GET['error_message']) && tep_not_null($_GET['error_message'])) {
  ?>
        <div class="" id="header-error-container" style="display:none;"><div class="header-error"><?php echo htmlspecialchars(urldecode($_GET['error_message'])); ?></div></div>
        <script>
            tl('<?php echo \frontend\design\Info::themeFile('/js/main.js'); ?>', function(){
                $('<a href="#header-error-container"></a>').popUp().trigger('click')
            });
        </script>
  <?php
    }

    if (isset($_GET['info_message']) && tep_not_null($_GET['info_message'])) {
  ?>
        <div class="" id="header-info-container" style="display:none;"><div class="header-info"><?php echo htmlspecialchars(urldecode($_GET['info_message'])); ?></div></div>
        <script>
            tl('<?php echo \frontend\design\Info::themeFile('/js/main.js'); ?>', function(){
                $('<a href="#header-info-container"></a>').popUp().trigger('click')
            });
        </script>
  <?php
    }

	}
}
