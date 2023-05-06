<?php
namespace common\models\forms;

use yii\base\Model;
use yii\web\UploadedFile;
use Yii;

class FilesForm extends Model
{
    /**
     * @var UploadedFile[]
     */
    public $files;
    public $filename;

    public function __construct($filename = 'file', $config = [])
    {
        parent::__construct($config);
        $this->filename = $filename;
    }

    public function rules()
    {
        return [
            ['files', 'each', 'rule' => ['file', 'extensions' => ['png', 'jpg', 'gif', 'jpeg','pdf','doc','xls','docx','xslx']]],
        ];
    }
    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            $this->files = UploadedFile::getInstancesByName($this->filename);
            return true;
        }
        return false;
    }
}