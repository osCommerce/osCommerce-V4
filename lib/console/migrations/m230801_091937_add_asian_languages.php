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
 * Class m230801_091937_add_asian_languages
 */
class m230801_091937_add_asian_languages extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $langInfo = [
            ['language_code' => 'tw', 'icon' => 'tw.svg', 'language_iso' => 'twn', 'language_name' => '繁中'],
            ['language_code' => 'ja', 'icon' => 'jp.svg', 'language_iso' => 'jpn', 'language_name' => '日本語'],
            ['language_code' => 'zh', 'icon' => 'cn.svg', 'language_iso' => 'zho', 'language_name' => '簡中'],
            ['language_code' => 'vi', 'icon' => 'vn.svg', 'language_iso' => 'vie', 'language_name' => '越南話'],
        ];

        foreach ($langInfo as $lang) {
            if (\common\models\LanguagesData::findOne(['language_code' => $lang['language_code']])) {
                $this->print(sprintf('Language %s (%s) is already exist', $lang['language_code'], $lang['language_iso']));
                continue;
            }

            $rec = new \common\models\LanguagesData();
            $rec->loadDefaultValues();
            $rec->setAttributes($lang, false);
            $rec->save(false);
        }

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230801_091937_add_asian_languages cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230801_091937_add_asian_languages cannot be reverted.\n";

        return false;
    }
    */
}
