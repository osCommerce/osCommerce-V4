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

namespace common\models\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * Class SimpleNested
 * Andrew's Categories Forked
 * @package common\models\behaviors
 */
class SimpleNested extends Behavior {

	/**
	 * @var string
	 */
	public $leftAttribute = 'lft';
	/**
	 * @var string
	 */
	public $rightAttribute = 'rgt';
	/**
	 * @var string
	 */
	public $levelAttribute = 'lvl';
	/**
	 * @var string
	 */
	public $ownerPrimaryKey = '';
	/**
	 * @var string
	 */
	public $descriptionTable = '';
	/**
	 * @var integer
	 */
	public $platformAttribute = 'platform_id';
	/**
	 * @var integer
	 */
	public $sortAttribute = 'sort_order';
	/**
	 * @var integer
	 */
	public $parentAttribute = 'parent_id';

	/**
	 * @var integer
	 */
	private $counter = 0;
	/**
	 * @var integer
	 */
	private $level = 0;
	/**
	 * @var boolean
	 */
	private $updateFlag = false;
	private $updatePlatformFlag = false;
	private $platformId = null;
	private $saveOldPlatform = null;
	public function events() {
		return [
			ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
			ActiveRecord::EVENT_AFTER_DELETE => 'afterInsert',
			ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
			ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate'
		];
	}


	public function afterInsert() {
		$this->clearCounters();
		$this->buildTree();
	}

	public function beforeUpdate() {
		$this->updateFlag = false;
		$this->updatePlatformFlag = false;
		if (
			( $this->owner->{$this->sortAttribute} != $this->owner->getOldAttribute( $this->sortAttribute ) ) ||
			( $this->owner->{$this->parentAttribute} != $this->owner->getOldAttribute( $this->parentAttribute ) ) ) {
			$this->updateFlag = true;
		}
		if( $this->owner->{$this->platformAttribute} != $this->owner->getOldAttribute( $this->platformAttribute )){
			$this->updatePlatformFlag = true;
			$this->saveOldPlatform = $this->owner->getOldAttribute( $this->platformAttribute );
		}
	}

	public function afterUpdate() {
		if ( $this->updateFlag ) {
			$this->clearCounters();
			$this->buildTree(0, $this->platformId);
			$this->updateFlag = false;
		}
		if ( $this->updatePlatformFlag ) {
			$this->clearCounters();
			$this->buildTree(0, $this->saveOldPlatform);
			$this->updatePlatformFlag = false;
		}
	}

	private function clearCounters() {
		$this->counter = 0;
		$this->level   = 0;
		$primaryKey = $this->owner->primaryKey();
		if ( ! isset( $primaryKey[0] ) ) {
			throw new \Exception( '"' . $this->owner->className() . '" must have a primary key.' );
		}
		$this->ownerPrimaryKey = $primaryKey[0];
		$this->platformId = $this->owner->{$this->platformAttribute};
	}

	private function buildTree( $parent_id = 0,$platformId = 1 ) {
		$catalogs = $this->owner->find()
		                        ->alias( 't' )
		                        ->select( $this->ownerPrimaryKey )
		                        ->where( [
			                        'AND',
			                        [ $this->parentAttribute => $parent_id ],
			                        [ $this->platformAttribute => $platformId  ],
		                        ] )
		                        ->asArray( true )
		                        ->orderBy( $this->sortAttribute )
		                        ->all();
		foreach ( $catalogs as $catalog ) {
			$this->counter ++;
			$this->owner->updateAll( [
				$this->levelAttribute => $this->level,
				$this->leftAttribute  => $this->counter
			], [ $this->ownerPrimaryKey => $catalog[ $this->ownerPrimaryKey ] ] );
			$subCatalogs = $this->owner->find()
			                           ->alias( 't' )
			                           ->select( $this->ownerPrimaryKey )
			                           ->where( [
				                           'AND',
				                           [ $this->parentAttribute => $catalog[ $this->ownerPrimaryKey ] ],
				                           [ $this->platformAttribute => $platformId  ],
			                           ] )
			                           ->asArray( true )
			                           ->orderBy( $this->sortAttribute )
			                           ->all();
			if ( ! empty( $subCatalogs ) ) {
				$this->level ++;
				$this->buildTree( $catalog[ $this->ownerPrimaryKey ],1 );
				$this->level --;
			}
			$this->counter ++;
			$this->owner->updateAll( [ $this->rightAttribute => $this->counter ], [ $this->ownerPrimaryKey => $catalog[ $this->ownerPrimaryKey ] ] );
		}
	}
}