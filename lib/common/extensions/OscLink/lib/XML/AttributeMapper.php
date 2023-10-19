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

namespace OscLink\XML;


class AttributeMapper
{

    protected $known = [
        '@language' => ['languages', 'languages_id'],
        '@currency' => ['currencies', 'currencies_id'],
        '@customer_address_book' => ['address_book', 'address_book_id'],
        '@order_status' => ['orders_status', 'orders_status_id'],
    ];

    protected $projectId = 1;
    protected $isLocalProject = false;

    protected $cache = [];

    public function setProjectId($projectId)
    {
//        $getProjectInfo_r = tep_db_query("SELECT * FROM io_project WHERE project_id='".intval($projectId)."'");
//        if ( tep_db_num_rows($getProjectInfo_r) ) {
//            $getProjectInfo = tep_db_fetch_array($getProjectInfo_r);
//            $this->projectId = $projectId;
//            $this->isLocalProject = !!$getProjectInfo['is_local'];
//        }else{
//            throw new \Exception('Wrong project id');
//        }
        $this->cache = [];

    }

    public function externalId(Complex $ref)
    {
        $externalId = null;

        if ( !$this->isLocalProject ) {
            $entityId = $this->getEntityId($ref);
            if ($entityId) {
                $getReference_r = tep_db_query(
                    "SELECT external_id " .
                    "FROM connector_osclink_mapping " .
                    "WHERE entity_id='" . (int)$entityId . "' AND internal_id='" . intval($ref->value) . "'"
                );
                if (tep_db_num_rows($getReference_r) > 0) {
                    $getReference = tep_db_fetch_array($getReference_r);
                    $externalId = $getReference['external_id'];
                }
            }
        }

        return $externalId;
    }

    public function internalId(Complex $ref)
    {
        $internalId = null;

        if ( !$this->isLocalProject ) {

            if (in_array($ref->table, IOCore::get()->getTablenamesWithMirrorIds()) && $ref instanceof IOMap) {
                return $ref->externalId;
            }

            $entityId = $this->getEntityId($ref);

            if ($entityId) {
                $mapName = $ref->getMapName();
                if ( isset($this->known[$mapName]) ) {
                    if ( isset($this->cache[(int)$entityId.$mapName]) && !empty($this->cache[(int)$entityId.$mapName][intval($ref->externalId)]) ) {
                        return $this->cache[(int)$entityId.$mapName][intval($ref->externalId)];
                    }
                }

                $getReference_r = tep_db_query(
                    "SELECT internal_id " .
                    "FROM connector_osclink_mapping " .
                    "WHERE entity_id='" . (int)$entityId . "' AND external_id='" . intval($ref->externalId) . "'"
                );
                if (tep_db_num_rows($getReference_r) > 0) {
                    $getReference = tep_db_fetch_array($getReference_r);
                    $internalId = $getReference['internal_id'];
                    if ( isset($this->known[$mapName]) ) {
                        $this->cache[(int)$entityId.$mapName][intval($ref->externalId)] = $internalId;
                    }
                }

            }

        }

        return $internalId;
    }

    public function mapIds(Complex $ref, $internalId, $externalId)
    {
        $entityId = $this->getEntityId($ref);
        tep_db_query(
            "INSERT IGNORE INTO connector_osclink_mapping " .
            " (entity_id, internal_id, external_id) ".
            "VALUES ".
            "('" . (int)$entityId . "', '" . intval($internalId) . "', '" . intval($externalId) . "')"
        );
    }

    protected function getEntityId(Complex $ref)
    {
        $entityId = 0;

        static $cachedIds = [];

        $key = intval($this->projectId).'^'.$ref->getMapName();
        if ( !isset($cachedIds[$key]) ) {
            $get_id_r = tep_db_query(
                "SELECT id " .
                "FROM connector_osclink_entity " .
                "WHERE entity_name='" . tep_db_input($ref->getMapName()) . "' AND project_id='" . intval($this->projectId) . "'"
            );
            if (tep_db_num_rows($get_id_r) > 0) {
                $get_id = tep_db_fetch_array($get_id_r);
                $entityId = $get_id['id'];
            } else {
                tep_db_perform('connector_osclink_entity', array(
                    'entity_name' => $ref->getMapName(),
                    'project_id' => intval($this->projectId),
                ));
                $entityId = tep_db_insert_id();
            }
            $cachedIds[$key] = $entityId;
        }else{
            $entityId = $cachedIds[$key];
        }

        return $entityId;
    }
}