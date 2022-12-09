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

namespace common\classes;

class Migration extends \yii\db\Migration {

    /**
     * drop index with same name if exists and create it again
     * @inheritdoc
     */
    public function createIndex($name, $table, $columns, $unique = false) {
        $checkExists = $this->db->createCommand(
                        "show indexes from " . $table .
                        " WHERE Key_name like :indexName ",
                        ['indexName' => $name]
                )->queryOne();
        if (is_array($checkExists)) {
            // drop old one
            $this->dropIndex($name, $table);
        }

        parent::createIndex($name, $table, $columns, $unique);
    }

    /**
     * @inheritdoc
     */
    public function dropIndex($name, $table)
    {
        $checkExists = $this->db->createCommand(
            "show indexes from " . $table ." WHERE Key_name like :indexName ",
            ['indexName' => $name]
        )->queryOne();
        if (is_array($checkExists)) {
            parent::dropIndex($name, $table);
        }
    }

    /**
     * @inheritdoc
     */
    public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null) {
        $ts = $this->getDb()->getTableSchema($table, true);
        if (isset($ts->foreignKeys[$name])) {
            $this->dropForeignKey($name, $table);
        }
        parent::addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete, $update);
    }

    /**
     * @inheritdoc
     */
    public function dropForeignKey($name, $table) {
        $ts = $this->getDb()->getTableSchema($table);
        if (isset($ts->foreignKeys[$name])) {
            parent::dropForeignKey($name, $table);
        }
    }

    /**
     * Creates table and indexes if table not exists yet
     * @param string       $table_name
     * @param array        $struct      table structure array like ['column' => $migrate->integer(10), etc.]     
     * @param string|array $primary     info for primary key:
     *                                  string -> comma separated string of columns that the primary key will consist of. The index name will be $table_name + '_pk'
     *                                  array  -> the key is a name of primary key, the value is comma separated string of columns
     *                                  Samples:
     *                                      'products_id,platforms_id'
     *                                      ['products_id', 'platforms_id']
     *                                      [ 'primary_key' => 'products_id, platforms_id']
     *                                      [ 'primary_key' => ['products_id', 'platforms_id']]
     * @param string|array $indexes     info for foreign key
     *                                  string -> comma separated string of columns that the foreign key will consist of. The index name will be generated automatically
     *                                  array  -> each item
     *                                            string -> comma separated string of columns that the foreign key will consist of. The index name will be generated automatically
     *                                            array  -> the key is a name of primary key (prefix 'unique:' allowed), the value is comma separated string of columns
     *                                  Samples:
     *                                      'products_id,platforms_id'
     *                                      ['products_id,platforms_id', 'products_id,customers_id']
     *                                      [ 'unique:products_platforms' => 'products_id,platforms_id', 'products_customers' => 'products_id,customers_id']
     *                                      [ 'unique:products_platforms' => ['products_id', 'platforms_id'], 'products_customers' => 'products_id,customers_id']
     *
     * @return boolean True if table did not exist and has just been created.
     */
    public function createTableIfNotExists(string $table_name, array $struct, /* array|string */ $primary = null, /* array|string */ $indexes = null) {
        if (!$this->isTableExists($table_name)) {
            $this->createTable($table_name, $struct);

            // create primary key 
            if (!is_null($primary)) {

                $index_name = null;
                if (is_string($primary)) {
                    $index_name = $table_name . '_pk';
                    $columns = $primary;
                }

                if (is_array($primary)) {
                    switch (count($primary)) {
                        case 0: break;
                        case 1: // key as index_name
                            foreach ($primary as $index_name => $columns)
                                break;
                        default: // array of columns
                            $index_name = $table_name . '_pk';
                            $columns = $primary;
                            break;
                    }
                }

                if (!is_null($index_name))
                    $this->addPrimaryKey($index_name, $table_name, $columns);
            }

            // create indexes
            if (!is_null($indexes)) {

                if (!is_array($indexes)) {
                    $indexes = array($indexes);
                }
                foreach ($indexes as $index_name => $columns) {
                    $unique = false;
                    if (is_string($index_name) && strpos($index_name, 'unique:') !== false) {
                        $unique = true;
                        $index_name = str_replace('unique:', '', $index_name);
                    }
                    if (is_int($index_name) or empty($index_name))
                        $index_name = str_replace(',', '_', $columns);
                    $this->createIndex($index_name, $table_name, $columns, $unique);
                }
            }

            return true;
        }
    }

    /**
     * Drops multiple tables.
     * @param array|string $tables array of table names to be dropped.
     */
    public function dropTables($tables) {
        if (is_string($tables)) {
            $tables = explode(',', $tables);
        }
        if (is_array($tables)) {
            foreach ($tables as $table)
                if ($this->isTableExists($table))
                    $this->dropTable($table);
        }
    }

    /**
     * @inheritdoc
     */
    public function isTableExists($tableName) {
        return $this->db->getTableSchema($tableName, true) !== null;
    }

    /**
     * @inheritdoc
     */
    public function isFieldExists($field, $table) {
        $fields = $this->db->createCommand("show FIELDS from {$table}")->queryColumn();
        return in_array($field, $fields);
    }

    /**
     * Check column in table
     *
     * @param $tableName
     * @param $columnName
     * @return bool
     */
    public function isMissingColumn($tableName, $columnName) {
        return $this->db->getTableSchema($tableName, true)->getColumn($columnName) === null;
    }

    /**
     * Wrap around addColumn with check for simple migration scenario
     *
     * Builds and executes a SQL statement for adding a new DB column.
     * @param string $table the table that the new column will be added to. The table name will be properly quoted by the method.
     * @param string $column the name of the new column. The name will be properly quoted by the method.
     * @param string $type the column type. The [[QueryBuilder::getColumnType()]] method will be invoked to convert abstract column type (if any)
     * into the physical one. Anything that is not recognized as abstract type will be kept in the generated SQL.
     * For example, 'string' will be turned into 'varchar(255)', while 'string not null' will become 'varchar(255) not null'.
     */
    public function addColumnIfMissing($table, $column, $type) {
        if ($this->isMissingColumn($table, $column)) {
            $this->addColumn($table, $column, $type);
        }
    }

    /**
     * @inheritdoc
     */
    public function createTable($table, $columns, $options = null) {
        if ($options === null && $this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            //$options = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
            $options = 'ENGINE=InnoDB CHARSET=utf8';
        }
        parent::createTable($table, $columns, $options);
    }

    public function batchInsertSafe($table, $columns, $rows) {
        try {
            parent::batchInsert($table, $columns, $rows);
            return true;
        } catch (\Exception $e) {
            \Yii::warning('Error in batchInsert (will be added row-by-row): ' . $e->getMessage());
        }
        $errorCnt = 0;
        $errorMsg = '';
        foreach($rows as $row) {
            $insert = [];
            foreach($columns as $index => $col) {
                $insert[$col] = $row[$index];
            }
            try {
                \Yii::$app->db->createCommand()->upsert($table, $insert, false)->execute();
            } catch (\Exception $e) {
                $errorCnt++;
                $errorMsg .= sprintf("Error adding row %s: %s\n", var_export($row, true), $e->getMessage());
            }
        }
        if ($errorCnt) {
            \Yii::warning( "Error in row-by-row inserting ($errorCnt): " . $errorMsg);
        }
        return $errorCnt;
    }
    /**
     *
     * @staticvar boolean $language_map
     * @param string  $entity
     * @param array $keys [$key=>$value], $value = string | array per language ['en' => 'english', 'fr' => 'French']
     */
    public function addTranslation($entity, $keys) {
        static $language_map = false;
        if ($language_map === false) {
            $language_map = \yii\helpers\ArrayHelper::map(
                            \common\models\Languages::find()->select('languages_id, code')->asArray()->all(),
                            'code', 'languages_id'
            );
        }
        foreach ($keys as $key => $value) {
            $hash = md5($key . '-' . $entity);
            if (is_array($value)) {
                // per language $value -- 'en' => 'english', 'fr' => 'French'
                foreach ($language_map as $languageCode => $languageId) {
                    $checked = $translated = false;
                    if (isset($value[$languageCode])) {
                        $languageValue = $value[$languageCode];
                        $checked = $translated = true;
                    } elseif (isset($value[\common\helpers\Language::systemLanguageCode()])) {
                        $languageValue = $value[\common\helpers\Language::systemLanguageCode()];
                    } else {
                        $languageValue = reset($value);
                    }
                    $this->db->createCommand(
                            "INSERT IGNORE INTO `translation` " .
                            "  (language_id, translation_key, translation_entity, translation_value, checked, translated, hash) " .
                            "  VALUES (:languages_id, :text_key, :entity, :text_value, :checked, :translated, :hash)",
                            [
                                'languages_id' => (int) $languageId,
                                'entity' => $entity,
                                'text_key' => $key,
                                'text_value' => $languageValue,
                                'checked' => $checked,
                                'translated' => $translated,
                                'hash' => $hash,
                            ]
                    )->execute();
                }
            } else {
                $this->db->createCommand(
                        "INSERT IGNORE INTO `translation` " .
                        "  (language_id, translation_key, translation_entity, translation_value, checked, translated, hash) " .
                        "  VALUES (1, :text_key, :entity, :text_value, 1, 1, :hash)",
                        [
                            'entity' => $entity,
                            'text_key' => $key,
                            'text_value' => $value,
                            'hash' => $hash,
                        ]
                )->execute();

                $this->db->createCommand(
                        "INSERT IGNORE INTO `translation` " .
                        "  (language_id, translation_key, translation_entity, translation_value, hash) " .
                        "  SELECT languages_id, :text_key, :entity, :text_value, :hash FROM languages",
                        [
                            'entity' => $entity,
                            'text_key' => $key,
                            'text_value' => $value,
                            'hash' => $hash,
                        ]
                )->execute();
            }
        }

        \yii\caching\TagDependency::invalidate(\Yii::$app->getCache(), 'translation');
    }

    /**
     *
     * @param string $entity
     * @param array $keys
     */
    public function removeTranslation($entity, $keys = null) {
        if (!empty($keys)) {
            if (!is_array($keys))
                $keys = array($keys);
            foreach ($keys as $key) {
                $this->db->createCommand(
                                "DELETE FROM translation " .
                                "WHERE translation_entity=:entity AND translation_key=:translate_key ",
                                ['entity' => $entity, 'translate_key' => $key])
                        ->execute();
            }
        } else {
            $this->db->createCommand("DELETE FROM translation WHERE translation_entity=:entity", ['entity' => $entity])->execute();
        }

        \yii\caching\TagDependency::invalidate(\Yii::$app->getCache(), 'translation');
    }

    /**
     * @param $key_name
     * @param $type_to_data
     * @example addEmailTemplate('Test email', [ 'html'=>['subject'=>'email subject', 'body'=>'email body'], [ 'text'=>['subject'=>'email subject', 'body'=>'email body'] ])
     */
    public function addEmailTemplate($key_name, $type_to_data) {
        $data = [
            'html' => [
                'email_templates_subject' => (
                isset($type_to_data['html']['subject']) ?
                $type_to_data['html']['subject'] :
                (isset($type_to_data['text']['subject']) ? $type_to_data['text']['subject'] : '')
                ),
                'email_templates_body' => (
                isset($type_to_data['html']['body']) ?
                $type_to_data['html']['body'] :
                (isset($type_to_data['text']['body']) ? $type_to_data['text']['body'] : '')
                ),
            ],
            'plaintext' => [
                'email_templates_subject' => (
                isset($type_to_data['text']['subject']) ?
                $type_to_data['text']['subject'] :
                (isset($type_to_data['html']['subject']) ? $type_to_data['html']['subject'] : '')
                ),
                'email_templates_body' => (
                isset($type_to_data['text']['body']) ?
                $type_to_data['text']['body'] :
                (isset($type_to_data['html']['body']) ? $type_to_data['html']['body'] : '')
                ),
            ]
        ];

        foreach ($data as $email_template_type => $email_template_data) {
            $existing_email_templates_id = $this->db->createCommand(
                            "SELECT email_templates_id " .
                            "FROM email_templates " .
                            "WHERE email_templates_key = :templates_key and email_template_type=:email_type ",
                            [
                                'templates_key' => $key_name,
                                'email_type' => $email_template_type,
                    ])->queryScalar();

            if (!$existing_email_templates_id) {
                $this->insert('email_templates', [
                    'email_templates_key' => $key_name,
                    'email_template_type' => $email_template_type,
                ]);
                $email_template_id = $this->db->getLastInsertID();
                $insert_data_query = new \yii\db\Query();
                foreach ($insert_data_query->select([
                            'email_templates_id' => new \yii\db\Expression($email_template_id),
                            'platform_id' => 'p.platform_id',
                            'language_id' => 'l.languages_id',
                            'affiliate_id' => new \yii\db\Expression(0),
                        ])->from(['p' => 'platforms', 'l' => 'languages'])
                        ->where('p.is_virtual=0')->all() as $row) {
                    $row['email_templates_subject'] = $email_template_data['email_templates_subject'];
                    $row['email_templates_body'] = $email_template_data['email_templates_body'];
                    $this->insert('email_templates_texts', $row);
                }
            }
        }
    }

    public function removeEmailTemplate($key_name) {
        $this->db->createCommand(
                "DELETE ett FROM email_templates_texts ett " .
                " INNER JOIN email_templates et ON ett.email_templates_id=et.email_templates_id " .
                "WHERE email_templates_key = :templates_key ",
                [
                    'templates_key' => $key_name,
        ])->execute();
        $this->db->createCommand(
                "DELETE et FROM email_templates et " .
                "WHERE email_templates_key = :templates_key ",
                [
                    'templates_key' => $key_name,
        ])->execute();
    }

    public function appendAcl($aclChain, $assign_to_access_levels = 1) { /// public to use in extension install()
        $PARENT_ID = 0;
        $ACL_INSERTED_ID = false;

        foreach ($aclChain as $assignBox) {

            $checkOnLevel = $this->db->createCommand(
                            "SELECT access_control_list_id AS id " .
                            "FROM access_control_list " .
                            "WHERE parent_id=:parent_id AND access_control_list_key=:box_name ",
                            ['parent_id' => (int) $PARENT_ID, 'box_name' => $assignBox]
                    )->queryOne();
            if (is_array($checkOnLevel)) {
                $PARENT_ID = $checkOnLevel['id'];
            } else {
                $getSO = $this->db->createCommand(
                                "SELECT MAX(sort_order) AS max_so " .
                                "FROM access_control_list " .
                                "WHERE parent_id='" . (int) $PARENT_ID . "'"
                        )->queryOne();
                $SORT_ORDER = (int) $getSO['max_so'] + 1;

                $this->insert('access_control_list', [
                    'parent_id' => $PARENT_ID,
                    'access_control_list_key' => $assignBox,
                    'sort_order' => $SORT_ORDER,
                ]);
                $ACL_INSERTED_ID = $this->db->getLastInsertID();
                $PARENT_ID = $ACL_INSERTED_ID;

                if (!is_array($assign_to_access_levels))
                    $assign_to_access_levels = array($assign_to_access_levels);

                $this->db->createCommand(
                        "UPDATE access_levels " .
                        "SET access_levels_persmissions = CONCAT(access_levels_persmissions,',','" . (int) $ACL_INSERTED_ID . "') " .
                        "WHERE access_levels_id IN ('" . implode("','", array_map('intval', $assign_to_access_levels)) . "')"
                )->execute();
            }
        }
        if ($ACL_INSERTED_ID === false) {
            $ACL_INSERTED_ID = $PARENT_ID;
        }

        return $ACL_INSERTED_ID;
    }

    public function removeAcl($aclChain) {
        $this->dropAcl($aclChain);
    }

    private function checkParent($id, $aclReversed)
    {
        array_shift($aclReversed);
        foreach ($aclReversed as $acl) {
            if (empty($row = \common\models\AccessControlList::findOne(['access_control_list_key' => $acl, 'access_control_list_id' => $id]))) {
                return false;
            }
            $id = $row->parent_id;
        }
        return true;
    }

    public function dropAcl($aclChain) {
        $aclChain = array_reverse($aclChain);
        $ids = [];
        foreach ($aclChain as $assignBox) {
            foreach (\common\models\AccessControlList::find()->where(['access_control_list_key' => $assignBox])->all() as $acl) {
                if (!$this->checkParent($acl->parent_id, $aclChain)) continue;
                $count = \common\models\AccessControlList::find()->where(['parent_id' => $acl->access_control_list_id])->count();
                if($count == 0) {
                    $ids[] = $acl->access_control_list_id;
                    $acl->delete();
                }
            }
        }
        
        if (count($ids) > 0) {
            foreach (\common\models\AccessLevels::find()->all() as $acl) {
                $persmissions = explode(",", $acl->access_levels_persmissions);
                foreach ($persmissions as $key => $value) {
                    if (in_array($value, $ids)) {
                        unset($persmissions[$key]);
                    }
                }
                $acl->access_levels_persmissions = implode(",", $persmissions);
                $acl->save(false);
            }
        }
    }

    public function addAdminMenuAfter($menuData, $afterBoxTitle) { /// public to use in extension install()
        if (is_array($menuData) && !empty($menuData['title'])) {
            $checkBox = $this->db->createCommand(
                            "SELECT box_id " .
                            "FROM admin_boxes " .
                            "WHERE title=:box_title",
                            ['box_title' => $menuData['title']]
                    )->queryOne();
            if (is_array($checkBox)) {
                return (int) $checkBox['box_id'];
            }
        } else {
            return false;
        }

        $getBox = $this->db->createCommand(
                        "SELECT parent_id, box_id, sort_order " .
                        "FROM admin_boxes " .
                        "WHERE title=:box_title",
                        ['box_title' => $afterBoxTitle]
                )->queryOne();
        if (is_array($getBox)) {
            //$getBox['box_id'];
            $new_sort_order = $getBox['sort_order'] + 1;
            $this->db->createCommand(
                    "UPDATE admin_boxes SET sort_order=sort_order+1 " .
                    "WHERE parent_id=:parent_id AND sort_order>=:shift_sort_order",
                    ['parent_id' => (int) $getBox['parent_id'], 'shift_sort_order' => (int) $new_sort_order]
            )->execute();

            $defaultData = [
                'parent_id' => $getBox['parent_id'],
                'sort_order' => $new_sort_order,
                'acl_check' => '',
                'config_check' => '',
                'box_type' => 0,
                'path' => '',
                'title' => '',
                'filename' => '',
            ];
            $data = array_merge($defaultData, $menuData);
            $this->insert('admin_boxes', $data);
            return $this->db->getLastInsertID();
            //$this->updateMenuXmlAfter(\Yii::getAlias('@site_root/admin/includes/default_menu.xml'), $data, $afterBoxTitle); die;
        }
        return false;
    }

    public function addAdminMenu(array $menuArray)
    {
        \common\helpers\MenuHelper::createAdminMenuItem($menuArray);
    }

    public function removeAdminMenu($array_or_title)
    {
        $this->dropAdminMenu($array_or_title);
    }

    public function dropAdminMenu($array_or_title)
    {
        \common\helpers\MenuHelper::removeAdminMenuItem($array_or_title);
    }

    protected function updateMenuXmlAfter($filename, $nodeData, $afterBoxTitle) {
        $afterBoxTitle = 'BOX_REPORTS_COMPARE';
        $simpleMenu = \simplexml_load_file($filename);

        $checkNewExist = $simpleMenu->xpath('//title[text()=\'' . $nodeData['title'] . '\']');
        if (count($checkNewExist) > 0) {
            return false;
        }
        $xpath = $simpleMenu->xpath('//title[text()=\'' . $afterBoxTitle . '\']/..');
        if (count($xpath) == 0) {
            return false;
        }
        $insertAfter = $xpath[0];
        $new_node_sort_order = intval($insertAfter->sort_order) + 1;

        unset($nodeData['parent_id']);
        $nodeData['sort_order'] = $new_node_sort_order;
        $xmlFormatter = new \common\api\Xml\XmlFormatter();
        $xmlFormatter->rootTag = 'item';
        $insertNode = \simplexml_load_string($xmlFormatter->format($nodeData));

        /**
         * @var $modifyParent \SimpleXMLElement
         */
        $modifyParent = reset($insertAfter->xpath('..'));

        foreach ($modifyParent->children() as $childNode) {
            if (strval($childNode->title) == $afterBoxTitle) {
                $target_dom = \dom_import_simplexml($childNode);
                $insert_dom = $target_dom->ownerDocument->importNode(\dom_import_simplexml($insertNode), true);
                if ($target_dom->nextSibling) {
                    $target_dom->parentNode->insertBefore($insert_dom, $target_dom->nextSibling);
                } else {
                    $target_dom->parentNode->appendChild($insert_dom);
                }
            }
            if (intval($childNode->sort_order) >= $new_node_sort_order) {
                $childNode->sort_order = intval($childNode->sort_order) + 1;
            }
        }

        //$xslt = new \XSLTProcessor();
        //$xslt->importStyleSheet($xsl);

        $xsl = \simplexml_load_string('<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

    <xsl:output encoding="utf-8" method="text" indent="no" media-type="text/xml"/>

    <xsl:template match="/">
        <xsl:text>&lt;?xml version="1.0" encoding="UTF-8"?&gt;
</xsl:text>
        <xsl:apply-templates select="node()">
            <xsl:with-param name="indent" select="\'\'"/>
        </xsl:apply-templates>
    </xsl:template>

    <xsl:template match="node()">
        <xsl:param name="indent"/>

        <xsl:value-of select="$indent"/>

        <xsl:text>&lt;</xsl:text><xsl:value-of select="name(.)"/><xsl:apply-templates select="@*"/>

            <!--xsl:if test="not(node())"><xsl:text> /</xsl:text></xsl:if-->
            <xsl:if test="not(node())"><xsl:text>&gt;&lt;/</xsl:text><xsl:value-of select="name(.)"/></xsl:if>
        <xsl:text>&gt;</xsl:text>

        <xsl:if test="node()">

            <xsl:if test="node()[node()]">
<xsl:text>
</xsl:text>
            </xsl:if>

            <xsl:apply-templates>
                <xsl:with-param name="indent" select="concat($indent, \'    \')"/>
            </xsl:apply-templates>


            <xsl:if test="node()[node()]">
                <xsl:value-of select="$indent"/>
            </xsl:if>

            <xsl:text>&lt;/</xsl:text><xsl:value-of select="name(.)"/><xsl:text>&gt;</xsl:text>
        </xsl:if>

<xsl:text>
</xsl:text>
    </xsl:template>

    <xsl:template match="@*">
        <xsl:text> </xsl:text>
        <xsl:value-of select="name(.)"/>
        <xsl:text>=</xsl:text>
        <xsl:value-of select="concat(\'&quot;\', ., \'&quot;\')"/>
    </xsl:template>

    <xsl:template match="text()">
        <xsl:value-of select="normalize-space(.)"/>
    </xsl:template>

    <xsl:template match="comment()">
        <xsl:text>&lt;--</xsl:text><xsl:value-of select="."/><xsl:text>--&gt;</xsl:text>
    </xsl:template>

    <xsl:template match="processing-instruction()">
        <xsl:text>&lt;?</xsl:text><xsl:value-of select="name(.)"/><xsl:text> </xsl:text><xsl:value-of select="."/><xsl:text>?&gt;</xsl:text>
<xsl:text>
</xsl:text>
    </xsl:template>
</xsl:stylesheet>
');

        $xslt = new \XSLTProcessor();
        $xslt->importStyleSheet($xsl);

        file_put_contents($filename . '.new.xml', $xslt->transformToXML($simpleMenu));
        die;

        $xmlFormatter = new \common\api\Xml\XmlFormatter();
        $unformatedXml = $xmlFormatter->format($simpleMenu);
        $domxml = new \DOMDocument('1.0');
        //$domxml->preserveWhiteSpace = false;
        $domxml->formatOutput = true;
        /* @var $xml SimpleXMLElement */
        $domxml->loadXML($unformatedXml);
        $domxml->save($filename . '.new.xml', LIBXML_NOEMPTYTAG /* leave <tag></tag> instead <tag/> */);

        //file_put_contents($filename.'.new.xml',);
        //echo '<pre>'; var_dump($ob); echo '</pre>';
    }


    public function removeConfigurationKeys($key_or_array)
    {
        $this->delete(TABLE_CONFIGURATION, ['configuration_key' => $key_or_array]);
    }

    public function removeConfigurationKeysInGroup($group_or_array)
    {
        $this->delete(TABLE_CONFIGURATION, ['configuration_group_id' => $group_or_array]);
    }

    public function removePlatformConfigurationKeys($key_or_array, $platform_id = null)
    {
        $conditions['configuration_key'] = $key_or_array;
        if (!is_null($platform_id)) {
            $conditions['platform_id'] = $platform_id;
        }
        $this->delete(TABLE_PLATFORMS_CONFIGURATION, $conditions);
    }

    public function removePlatformConfigurationKeysInGroup($group_or_array, $platform_id = null)
    {
        $conditions['configuration_key'] = $group_or_array;
        if (!is_null($platform_id)) {
            $conditions['platform_id'] = $platform_id;
        }
        $this->delete(TABLE_PLATFORMS_CONFIGURATION, $conditions);
    }

    public function addWidget($placeholder, $widget, $ifNoWidget = '', $themeName = false)
    {
        if (is_file(DIR_FS_CATALOG . $widget)) {
            $widgetName = '';
            $widgetLocation = DIR_FS_CATALOG . $widget;
        } else {
            $mainWidgetsPath = DIR_FS_CATALOG
                . implode(DIRECTORY_SEPARATOR, ['lib', 'frontend', 'design', 'boxes'])
                . DIRECTORY_SEPARATOR
                . str_replace('\\', DIRECTORY_SEPARATOR, $widget);
            $widgetArr = explode('\\', $widget);
            $extensionsPath = DIR_FS_CATALOG
                . implode(DIRECTORY_SEPARATOR, ['lib', 'common', 'extensions'])
                . DIRECTORY_SEPARATOR
                . str_replace('\\', DIRECTORY_SEPARATOR, $widget)
                . DIRECTORY_SEPARATOR . end($widgetArr);
            if (is_file($mainWidgetsPath . '.php') || is_file($extensionsPath . '.php')) {
                $widgetName = $widget;
            } else {
                echo "\nError: $extensionsPath don't have php file \n\n";
                return "\"$extensionsPath\" don't have php file";
            }
            if (is_file($mainWidgetsPath . '.zip')) {
                $widgetLocation = $mainWidgetsPath . '.zip';
            } elseif (is_file($extensionsPath . '.zip')) {
                $widgetLocation = $extensionsPath . '.zip';
            }
        }

        $themeError = '';
        if ($themeName && \common\models\Themes::findOne(['theme_name' => $themeName])) {
            $themes = [['theme_name' => $themeName]];
        } else {
            $themes = \common\models\Themes::find()->asArray()->all();
        }
        foreach ($themes as $theme) {
            $box = \common\models\DesignBoxesTmp::find()->where([
                'widget_params' => $placeholder, 'theme_name' => $theme['theme_name']
            ])->asArray()->one();
            if ($box && is_array($box)) {
                $params['block_name'] = 'block-' . $box['id'];
            } else {
                $params['block_name'] = $placeholder;
            }
            $params['theme_name'] = $theme['theme_name'];

            if ($ifNoWidget) {
                $widgets = \backend\design\Theme::getWidgetsInPlaceholder($params['block_name'], $theme['theme_name']);
                foreach ($widgets as $_widget) {
                    if ($_widget['widget_name'] == $ifNoWidget) {
                        continue 2;
                    }
                }
            }

            $max = \common\models\DesignBoxesTmp::find()->where([
                'block_name' => $params['block_name'], 'theme_name' => $theme['theme_name']
                ])->max('sort_order');

            $params['sort_order'] = $max + 1;


            if ($widgetLocation) {
                $importBlock = \backend\design\Theme::importBlock($widgetLocation, $params);
                if (!is_array($importBlock)) {
                    echo "\n" . $importBlock . "\n";
                    $themeError .= $theme['theme_name'] . ': error in ' . $importBlock . "\n";
                }
            } elseif ($widgetName) {
                $designBoxes = new \common\models\DesignBoxesTmp();
                $designBoxes->microtime = microtime(true);
                $designBoxes->theme_name = $theme['theme_name'];
                $designBoxes->block_name = $params['block_name'];
                $designBoxes->widget_name = $widgetName;
                $designBoxes->sort_order = $params['sort_order'];
                $designBoxes->save();
                if ($designBoxes->errors) {
                    $themeError .= $theme['theme_name'] . ': sql error ' . "\n";
                }
            }

            \backend\design\Theme::elementsSave($params['theme_name']);
            \common\models\DesignBoxesCache::deleteAll(['theme_name' => $params['theme_name']]);
        }
        return $themeError;
    }

    public function removeWidget($widgetName)
    {
        $boxes = \common\models\DesignBoxesTmp::find()
            ->where(['widget_name' => $widgetName])
            ->orWhere(['block_name' => $widgetName])
            ->asArray()->all();
        foreach ($boxes as $box) {
            \backend\design\Theme::deleteBlock($box['id'], true);
            \common\models\DesignBoxesSettings::deleteAll(['box_id' => $box['id']]);
            \common\models\DesignBoxesSettingsTmp::deleteAll(['box_id' => $box['id']]);
            \common\models\DesignBoxes::deleteAll(['id' => $box['id']]);
            \common\models\DesignBoxesTmp::deleteAll(['id' => $box['id']]);
        }
    }

    public function updateTheme($themeName, $migrationPath)
    {
        echo "\nMigration for " . $themeName . " theme \n";
        if (!\common\models\DesignBoxes::find(['theme_name' => $themeName])) {
            echo $themeName . " theme not found \n";
            return '';
        }

        $filePath = DIR_FS_CATALOG . DIRECTORY_SEPARATOR . trim($migrationPath, DIRECTORY_SEPARATOR);

        if (!is_file($filePath)) {
            echo "Migration file not found: " . $filePath . " \n";
        }

        $migration = json_decode(file_get_contents($filePath), true);
        if ( $result = \backend\design\Steps::applyMigration($themeName, $migration) ) {
            \backend\design\Theme::elementsSave($themeName);
            \common\models\DesignBoxesCache::deleteAll(['theme_name' => $themeName]);
            \backend\design\Theme::saveThemeVersion($themeName);
            echo $result . "\n";
            return '';
        }

        echo "Migration not applied \n";
    }

    /**
     * @param $code string class name of extension
     */
    public function installExt(string $code)
    {
        $res = \common\helpers\Extensions::installSafe($code);
        if (!is_null($res)) {
            echo "Error while installing $code: $res\n";
        } else {
            echo "Extension $code was installed successfully\n";
        }
    }

    public function uninstallExt(string $code)
    {
        $res = \common\helpers\Extensions::uninstallSafe($code);
        if (!is_null($res)) {
            echo "Error while uninstalling $code: $res\n";
        } else {
            echo "Extension $code was uninstalled successfully\n";
        }
    }

    public function reinstallExtTranslation(string $code)
    {
        if ($ext = \common\helpers\Extensions::isAllowed($code)) {
            $ext::reinstallTranslation($this);
            echo "Translations were reinstalled for extension $code\n";
        } else {
            echo "Translations were not reinstalled for extension $code: it is not enabled";
        }
    }

}
