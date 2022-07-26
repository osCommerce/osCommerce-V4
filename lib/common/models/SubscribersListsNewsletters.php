<?php

namespace common\models;

use Yii;

/**
 * internal "subscribers lists" to external newsletter lists (mailchimp tags).
 * This is the model class for table "subscribers_lists_newsletters".
 *
 * @property int $subscribers_lists_id
 * @property string $external_id
 * @property string $external_name (tag name)
 * @property string $type - list, tag , empty
 * @property string $provider
 */
class SubscribersListsNewsletters extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'subscribers_lists_newsletters';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['subscribers_lists_id', 'external_id', 'provider'], 'required'],
            [['subscribers_lists_id'], 'integer'],
            [['external_id', 'provider', 'external_name'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 50],
            [['subscribers_lists_id', 'external_id', 'provider'], 'unique', 'targetAttribute' => ['subscribers_lists_id', 'external_id', 'provider']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'subscribers_lists_id' => 'Subscribers Lists ID',
            'external_id' => 'External ID',
            'external_name' => 'External Name',
            'type' => 'External Type (entity)',
            'provider' => 'Provider',
        ];
    }

    /**
     *
     * @return type
     */
    public function getSubscribersLists() {
      return $this->hasMany(SubscribersLists::class, ['subscribers_lists_id' => 'subscribers_lists_id']);
    }
}
