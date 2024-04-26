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
 * Class m240313_143541_remove_catalog_pages
 */
class m240313_143541_remove_catalog_pages extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->removeConfigurationKeys(['CATALOG_PAGES_PREFIX_URL']);
        $this->dropAcl(['BOX_HEADING_DESIGN_CONTROLS', 'BOX_HEADING_CATALOG_PAGES']);
        $this->dropAdminMenu('BOX_HEADING_CATALOG_PAGES');

        $this->dropTables([
            'catalog_pages_to_information',
            'catalog_pages',
            'catalog_pages_description'
        ]);

        $this->removeTranslation('admin/catalog-pages');

        $this->removeTranslation('admin/main', [
            'BOX_HEADING_CATALOG_PAGES',
            'TEXT_CREATE_NEW_CATALOG_PAGE',
            'TEXT_COUNT_ON_PAGE',
            'TEXT_SELECT_CATEGORY_PAGE'
        ]);

        $this->removeTranslation('main', [
            'TEXT_WIDGET_CATEGORY_PAGE',
            'TEXT_WIDGET_CATEGORY_PAGE_LAST_LIST',
            'TEXT_WIDGET_CATEGORY_PAGE_LAST_LIST_BY_CATALOG',
            'TEXT_WIDGET_CATEGORY_PAGE_LAST_LIST_BLOCK',
            'TEXT_WIDGET_CATEGORY_PAGE_LAST_LIST_BY_CATALOG_BLOCK',
            'LAST_EVENTS'
        ]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addConfigurationKey([
            'configuration_key' => 'CATALOG_PAGES_PREFIX_URL',
            'configuration_title' => 'Catalog Pages prefix url',
            'configuration_description' => 'Catalog Pages prefix url',
            'configuration_group_id' => '1',
            'configuration_value' => 'pages',
            'sort_order' => '100',
        ]);

        $this->appendAcl(['BOX_HEADING_DESIGN_CONTROLS', 'BOX_HEADING_CATALOG_PAGES']);

        $this->addAdminMenu([
            'parent' => 'BOX_HEADING_DESIGN_CONTROLS',
            'sort_order' => '6',
            'box_type' => '0',
            'acl_check' => '',
            'config_check' => '',
            'path' => 'catalog-pages',
            'title' => 'BOX_HEADING_CATALOG_PAGES',
            'filename' => '',
        ]);

        if (!$this->isTableExists('catalog_pages')) {
            $this->db->createCommand("CREATE TABLE `catalog_pages` (
                `catalog_pages_id` INT(11) NOT NULL AUTO_INCREMENT,
                `platform_id` INT(11) NOT NULL DEFAULT '0',
                `image` VARCHAR(255) NOT NULL DEFAULT '',
                `parent_id` INT(11) NOT NULL DEFAULT '0',
                `status` TINYINT(4) NOT NULL DEFAULT '1',
                `lft` INT(11) NOT NULL DEFAULT '0',
                `rgt` INT(11) NOT NULL DEFAULT '0',
                `lvl` INT(11) NOT NULL DEFAULT '0',
                `sort_order` INT(11) NOT NULL DEFAULT '0',
                `updated_at` INT(11) NOT NULL DEFAULT '0',
                `created_at` INT(11) NOT NULL DEFAULT '0',
                PRIMARY KEY (`catalog_pages_id`),
                INDEX `parent_id` (`parent_id`),
                INDEX `platform_id` (`platform_id`),
                INDEX `status` (`status`),
                CONSTRAINT `FK_catalog_pages_platforms` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`platform_id`) ON UPDATE NO ACTION ON DELETE NO ACTION
            )")->execute();
        }
        if (!$this->isTableExists('catalog_pages_description')) {
            $this->db->createCommand("CREATE TABLE `catalog_pages_description` (
                `catalog_pages_id` INT(11) NOT NULL,
                `languages_id` INT(11) NOT NULL,
                `name` VARCHAR(255) NOT NULL DEFAULT '',
                `description_short` TEXT NOT NULL,
                `description` TEXT NOT NULL,
                `slug` VARCHAR(255) NOT NULL DEFAULT '',
                `meta_title` VARCHAR(255) NOT NULL DEFAULT '',
                `meta_description` VARCHAR(255) NOT NULL DEFAULT '',
                `meta_keyword` VARCHAR(255) NOT NULL DEFAULT '',
                PRIMARY KEY (`catalog_pages_id`, `languages_id`),
                INDEX `FK_catalog_pages_description_languages` (`languages_id`),
                INDEX `slug` (`slug`),
                CONSTRAINT `FK_catalog_pages_description_languages` FOREIGN KEY (`languages_id`) REFERENCES `languages` (`languages_id`) ON UPDATE NO ACTION ON DELETE NO ACTION
            )")->execute();
        }
        if (!$this->isTableExists('catalog_pages_to_information')) {
            $this->db->createCommand("CREATE TABLE `catalog_pages_to_information` (
                `catalog_pages_id` INT(11) NOT NULL,
                `information_id` INT(11) NOT NULL,
                PRIMARY KEY (`catalog_pages_id`, `information_id`),
                INDEX `catalog_pages_id` (`catalog_pages_id`),
                INDEX `information_id` (`information_id`),
                CONSTRAINT `FK_catalog_pages_to_information_catalog_pages` FOREIGN KEY (`catalog_pages_id`) REFERENCES `catalog_pages` (`catalog_pages_id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
                CONSTRAINT `FK_catalog_pages_to_information_information` FOREIGN KEY (`information_id`) REFERENCES `information` (`information_id`) ON UPDATE NO ACTION ON DELETE NO ACTION
            )")->execute();
        }

        $this->addTranslation('admin/catalog-pages', [
            'TITLE_CREATE_EDIT_CATALOG_PAGE' => 'Edit Catalog Page',
            'TITLE_CREATE_NEW_CATALOG_PAGE' => 'Create New Catalog Page'
        ]);

        $this->addTranslation('admin/main', [
            'BOX_HEADING_CATALOG_PAGES' => 'Catalog Pages',
            'TEXT_CREATE_NEW_CATALOG_PAGE' => 'Create Catalog Page',
            'TEXT_COUNT_ON_PAGE' => 'Count On Page',
            'TEXT_SELECT_CATEGORY_PAGE' => 'Select Category Pages'
        ]);

        $this->addTranslation('main', [
            'TEXT_WIDGET_CATEGORY_PAGE' => 'Category Pages',
            'TEXT_WIDGET_CATEGORY_PAGE_LAST_LIST' => 'Catalog Pages Last List',
            'TEXT_WIDGET_CATEGORY_PAGE_LAST_LIST_BY_CATALOG' => 'Catalog Pages Last List By Catalog',
            'TEXT_WIDGET_CATEGORY_PAGE_LAST_LIST_BLOCK' => 'Catalog Pages Block Last List',
            'TEXT_WIDGET_CATEGORY_PAGE_LAST_LIST_BY_CATALOG_BLOCK' => 'Catalog Pages Block Last List By Catalog',
            'LAST_EVENTS' => 'Last Events'
        ]);

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240313_143541_remove_catalog_pages cannot be reverted.\n";

        return false;
    }
    */
}
