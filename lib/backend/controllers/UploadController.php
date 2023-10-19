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

namespace backend\controllers;

use Yii;
use common\classes\Images;
use yii\helpers\FileHelper;
/**
 *
 */
class UploadController extends Sceleton
{
  /**
   *
   */
    public function actionIndex()
    {
        if (false === \common\helpers\Acl::rule(['FILE_UPLOAD'])) {
            header('HTTP/1.0 403 Forbidden');
            die('File upload not allowed');
        }
        if (isset($_FILES['file'])) {
            $folder = Yii::$app->request->get('folder');
          $path = \Yii::getAlias('@webroot');
          $path .= DIRECTORY_SEPARATOR . ($folder == 'images' ? '../images' : 'uploads') . DIRECTORY_SEPARATOR;
          $uploadfile = $path . $this->basename($_FILES['file']['name']);

          if ( !is_writeable(dirname($uploadfile)) ) {
              $response = ['status' => 'error', 'text'=> 'Directory "'.$this->basename(\Yii::getAlias('@webroot')).DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR.'" not writeable'];
          }elseif(!is_uploaded_file($_FILES['file']['tmp_name']) || filesize($_FILES['file']['tmp_name'])==0){
              $response = ['status' => 'error', 'text'=> 'File upload error'];
          }else
          if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
            $text = '';
            $response = ['status' => 'ok', 'text' => $text];
          } else {
            $response = ['status' => 'error'];
          }
        }
        echo json_encode($response);
    }

    public function actionScreenshot()
    {
        $post = tep_db_prepare_input(Yii::$app->request->post());
        if (isset($post['image'])) {
            $path = \Yii::getAlias('@webroot');
            $path .= DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
            $path .= 'themes' . DIRECTORY_SEPARATOR . $post['theme_name'];

            if (!file_exists($path)) {
                mkdir($path);
            }

            if ($post['file_name'] ?? null){
                $file_name = $post['file_name'];
                $folders = explode('/', $file_name);
                $path2 = '';
                for ($i = 0; $i < count($folders) - 1; $i++){
                    $path2 .= $folders[$i] . DIRECTORY_SEPARATOR;
                    if (!file_exists($path . DIRECTORY_SEPARATOR . $path2)){
                        mkdir($path . DIRECTORY_SEPARATOR . $path2);
                    }
                }
            } else {
                $file_name = 'screenshot';
            }
            file_put_contents($path . DIRECTORY_SEPARATOR . $file_name . '.png', base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $post['image'])));
        }
        echo $post['image'];
    }

    protected function basename($param, $suffix=null,$charset = 'utf-8'){
        if ( $suffix ) {
            $tmpstr = ltrim(mb_substr($param, mb_strrpos($param, DIRECTORY_SEPARATOR, 0, $charset), null, $charset), DIRECTORY_SEPARATOR);
            if ( (mb_strpos($param, $suffix, null, $charset)+mb_strlen($suffix, $charset) )  ==  mb_strlen($param, $charset) ) {
                return str_ireplace( $suffix, '', $tmpstr);
            } else {
                return ltrim(mb_substr($param, mb_strrpos($param, DIRECTORY_SEPARATOR, 0, $charset), null, $charset), DIRECTORY_SEPARATOR);
            }
        } else {
            return ltrim(mb_substr($param, mb_strrpos($param, DIRECTORY_SEPARATOR, 0, $charset), null, $charset), DIRECTORY_SEPARATOR);
        }
    }

    public function actionCropImage()
    {
        $settings = Yii::$app->request->post();
        $settings['src'] = str_replace(tep_catalog_href_link(), DIR_FS_CATALOG, $settings['src']);
        $settings['src'] = preg_replace("/^" . str_replace('/', '\/', DIR_WS_CATALOG) . "/", DIR_FS_CATALOG, $settings['src']);
        $srcArr = explode('/', str_replace('\\', '/', $settings['src']));
        $fileName = end($srcArr);
        $destination = DIR_FS_ADMIN . 'uploads' . DIRECTORY_SEPARATOR . $fileName;

        $pos = strripos($destination, '.');
        $ext = strtolower(substr($destination, $pos+1));
        $name = substr($destination, 0, $pos) . '-crop';
        $destination = $name . '.' . $ext;
        $from = 1;
        $pos2 = strripos($destination, '-');
        $end = strtolower(substr($destination, $pos2+1));
        if ($pos2 > 1 && preg_match("/[0-9]+/", $end)) {
            $name = substr($destination, 0, $pos2);
            $from = (int)$end;
        }
        for ($i = $from; $i < 20 && file_exists($destination); $i++) {
            $destination = $name . '-' . $i . '.' . $ext;
        }

        $settings['destination'] = $destination;
        $response = \common\classes\Images::cropImage($settings);
        if ($response) {
            $response = str_replace(DIR_FS_CATALOG, tep_catalog_href_link(), $response);
            return json_encode(['src' => $response]);
        } else {
            return json_encode(['error' => 'error']);
        }
    }

    public function actionRemove()
    {
        $file = Yii::$app->request->get('file');

        if (dirname($file) == '.') {
            unlink(Images::getFSCatalogImagesPath() . $file);
            unlink(Images::getFSCatalogImagesPath() . 'thumbnails/' . $file);

            return json_encode(['text' => 'Removed']);
        }

        return json_encode(['error' => 'Wrong file location']);
    }

    public function actionSaveSvg()
    {
        $svg = Yii::$app->request->post('svg');
        $fileName = Yii::$app->request->post('name');
        $fileName = pathinfo($fileName, PATHINFO_FILENAME);

        file_put_contents(DIR_FS_ADMIN . 'uploads/' . $fileName . '.svg', $svg);

        return json_encode([
            'text' => 'Saved',
            'file' => DIR_WS_ADMIN . 'uploads/' . $fileName . '.svg',
            'fileName' => $fileName . '.svg',
        ]);
    }
}
