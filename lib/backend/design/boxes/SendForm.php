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

namespace backend\design\boxes;

use Yii;
use yii\base\Widget;

class SendForm extends Widget
{

    public $id;
    public $params;
    public $settings;
    public $visibility;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        global $languages_id;

        $languages = \common\helpers\Language::get_languages();
        $lang = array();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
            $languages[$i]['logo'] = $languages[$i]['image'];
            $lang[] = $languages[$i];
        }


        if ( $this->settings[0]['text']) {
            $text = $this->settings[0]['text'];
        } elseif ($this->settings[$languages_id]['text']) {
            $text = $this->settings[$languages_id]['text'];
        } else {
            $text = $this->settings[0]['text'];
        }

        if ( $this->settings[0]['success']) {
            $success = $this->settings[0]['success'];
        } elseif ($this->settings[$languages_id]['success']) {
            $success = $this->settings[$languages_id]['success'];
        } else {
            $success = $this->settings[0]['success'];
        }


        $templatesList = array();

        $groups_query = tep_db_query("select email_templates_id, email_templates_key from " . TABLE_EMAIL_TEMPLATES . " where 1 group by email_templates_key order by email_templates_key, email_template_type");

        while( $email_templates = tep_db_fetch_array( $groups_query ) ) {

            $templatesList[] = $email_templates['email_templates_key'];
        }


        return $this->render('send-form.tpl', [
            'id' => $this->id,
            'params' => $this->params,
            'settings' => $this->settings,
            'languages' => $lang,
            'languages_id' => $languages_id,
            'visibility' => $this->visibility,
            'templatesList' => $templatesList,
            'text' => htmlspecialchars($text),
            'success' => $success,
        ]);
    }
}