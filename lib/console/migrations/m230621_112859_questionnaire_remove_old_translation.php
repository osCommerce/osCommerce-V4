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
 * Class m230621_112859_questionnaire_remove_old_translation
 */
class m230621_112859_questionnaire_remove_old_translation extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (method_exists($this, 'isOldExtension'))
        {
            if (!$this->isOldExtension('Questionnaire'))
            {
                $this->removeTranslation('admin/questionnaire');
                $this->removeTranslation('questionnaire');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addTranslation('admin/questionnaire',[
            'QUESTIONNAIRE' => 'Questionnaire',
            'QUESTIONNAIRE_GROUPS' => 'Questionnaire groups',
            'QUESTIONNAIRE_EDIT' => 'Edit question',
            'QUESTIONNAIRE_GROUPS_EDIT' => 'Edit questionnaire groups',
            'TEXT_ADD_QUESTION_GROUP' => 'Add group',
            'TEXT_ADD_QUESTION' => 'Add question',
            'TEXT_QUESTIONS' => 'Questions',
            'TEXT_ADD_CHOICE' => 'Add choice',
            'TEXT_CHOICES' => 'Choices',
            'TEXT_ANSWER_WEIGHT' => 'Weight',
            'ONLY_FOR_LOGGED_CUSTOMERS' => 'Only for logged customers',
            'CAN_VOTE_ONLY_ONCE' => 'Can vote only once',
            'TEXT_SUCCESS_MESSAGE' => 'Success message',
        ]);

        $this->addTranslation('questionnaire',[
            'QUESTIONNAIRE_PLEASE_LOGIN' => 'Please login',
            'TEXT_CONFIRM' => 'Confirm',
            'YOU_CAN_VOTE_ONLY_ONCE' => 'You can vote only once',
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230621_112859_questionnaire_remove_old_translation cannot be reverted.\n";

        return false;
    }
    */
}
