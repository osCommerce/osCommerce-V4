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
 * Class m230205_125456_banners
 */
class m230205_125456_banners extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/banner_manager',[
            'BANNER_COPIED' => 'Banner copied',
            'BANNER_NOT_COPIED' => 'Banner not copied',
            'BANNERS_WITHOUT_PLATFORM' => 'Banners without sales channel',
            'BANNERS_WITHOUT_GROUP' => 'Banners without group',
            'ARE_YOU_SURE_DELETE_GROUP' => 'Are you sure you want to delete this group? All banners in this group from all sales channels will be deleted',
            'NUMBER_OF_BANNERS' => 'Number of banners',
            'GROUP_NAME' => 'Group name',
            'BATCH_CHECKBOXES' => 'Batch checkboxes',
            'SORTING_HANDLE' => 'Sorting handle',
            'FILE_NAME' => 'File name',
            'SELECT_STATUS' => 'Select status',
            'ARE_YOU_SURE_DELETE_BANNER' => 'Are you sure you want to delete this banner(s)',
            'NEW_BANNER_GROUP' => 'New group',
            'TEXT_ON' => 'On',
            'TEXT_OFF' => 'Off',
            'SHOW_EMPTY_GROUPS' => 'Show empty groups',
        ]);


        if (!$this->isTableExists('banners_groups_sizes')) {
            $this->createTable('banners_groups_sizes', [
                'id' => $this->primaryKey(),
                'group_id' => $this->integer(11)->notNull()->defaultValue(0),
                'banners_group' => $this->string(255)->notNull()->defaultValue(''),
                'width_from' => $this->integer(11)->notNull()->defaultValue(0),
                'width_to' => $this->integer(11)->notNull()->defaultValue(0),
                'image_width' => $this->integer(11)->notNull()->defaultValue(0),
                'image_height' => $this->integer(11)->notNull()->defaultValue(0),
            ]);

            $this->getDb()->createCommand("
                INSERT INTO banners_groups_sizes (id, banners_group, width_from, width_to, image_width, image_height)  
                SELECT id, banners_group, width_from, width_to, image_width, image_height 
                FROM banners_groups")->execute();

            $groups = [];
            $bannersGroups = \common\models\BannersGroups::find()->select('banners_group')->distinct()->asArray()->all();
            if (is_array($bannersGroups)) {
                foreach ($bannersGroups as $bannersGroup) {
                    $groups[$bannersGroup['banners_group']] = $bannersGroup['banners_group'];
                }
            }
            $bannersGroups = $this->getDb()->createCommand("SELECT * FROM banners_new")->queryAll();
            if (is_array($bannersGroups)) {
                foreach ($bannersGroups as $bannersGroup) {
                    $groups[$bannersGroup['banners_group']] = $bannersGroup['banners_group'];
                }
            }


            $this->getDb()->createCommand("TRUNCATE TABLE banners_groups")->execute();

            foreach ($groups as $group) {
                if ($group) {
                    $this->insert('banners_groups', [
                        'banners_group' => $group,
                    ]);
                }
            }


            $this->getDb()->createCommand("TRUNCATE TABLE banners")->execute();
            $this->dropColumn('banners', 'banners_title');
            $this->dropColumn('banners', 'banners_url');
            $this->dropColumn('banners', 'banners_image');
            $this->dropColumn('banners', 'banners_html_text');
            $this->dropColumn('banners', 'language_id');
            $this->addColumn('banners', 'nofollow', $this->integer(1)->notNull()->defaultValue(0));
            $this->addColumn('banners', 'group_id', $this->integer(1)->notNull()->after('banners_group')->defaultValue(0));
            $this->alterColumn('banners', 'banners_group', $this->string(255)->notNull()->defaultValue(''));
            $this->getDb()->createCommand("
                INSERT INTO banners (banners_id, banners_group, expires_impressions, expires_date, date_scheduled, date_added, date_status_change, status, affiliate_id, sort_order, banner_type, nofollow) 
                SELECT banners_id, banners_group, expires_impressions, expires_date, date_scheduled, date_added, date_status_change, status, affiliate_id, sort_order, banner_type, nofollow 
                FROM banners_new")->execute();

            $bannersGroups = \common\models\BannersGroups::find()->asArray()->all();
            if (is_array($bannersGroups))
                foreach ($bannersGroups as $bannersGroup) {
                    $this->update('banners_groups_sizes',
                        ['group_id' => $bannersGroup['id']],
                        ['banners_group' => $bannersGroup['banners_group']]
                    );
                    $this->update('banners',
                        ['group_id' => $bannersGroup['id']],
                        ['banners_group' => $bannersGroup['banners_group']]
                    );
                }

            $this->dropColumn('banners_groups', 'width_from');
            $this->dropColumn('banners_groups', 'width_to');
            $this->dropColumn('banners_groups', 'image_width');
            $this->dropColumn('banners_groups', 'image_height');
            $this->dropColumn('banners_groups_sizes', 'banners_group');
            $this->dropColumn('banners', 'banners_group');
            $this->dropTable('banners_new');
        }

        $this->removeAdminMenu('BOX_BANNER_GROUPS');
        $this->removeAcl(['TEXT_SETTINGS', 'BOX_BANNER_GROUPS']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
