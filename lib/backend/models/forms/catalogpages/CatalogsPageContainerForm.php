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
namespace backend\models\forms\catalogpages;

use backend\services\CatalogPagesService;
use common\models\repositories\LanguagesRepository;
use yii\base\Model;
use Yii;

class CatalogsPageContainerForm extends Model {

    public $catalogPageForm;
    public $catalogPageDescriptionForm = [];
    public $assignInformationForm = [];
    public $errorData = [];
    public $languagesRepository;
    public $catalogPagesService;

    public function __construct(int $id,int $platformId,int $parentId,int $languageId,LanguagesRepository $languagesRepository, CatalogPagesService $catalogPagesService, array $config = [])
    {
        parent::__construct($config);
        $this->languagesRepository = $languagesRepository;

		$languages = $this->languagesRepository->getAll();
		if($id < 1){
			$this->catalogPageForm = new CatalogsPageForm([
				'platform_id' => $platformId,
				'parent_id' => $parentId,
                'created_at' => date('Y-m-d'),
                'created_at_view' => date('d M Y'),
			]);
			foreach ($languages as $language){
				$this->catalogPageDescriptionForm[$language->languages_id] = new CatalogsPageDescriptionForm([
					'languages_id' => $language->languages_id,
				]);
			}
		}else{
			$this->catalogPagesService = $catalogPagesService;
			$catalogPage = $this->catalogPagesService->getFullInfoAll($id,$languageId);
			$this->catalogPageForm = new CatalogsPageForm([
				'platform_id' => $catalogPage->platform_id,
				'parent_id' => $catalogPage->parent_id,
				'image' => $catalogPage->image,
				'status' => $catalogPage->status,
                'created_at' => date('Y-m-d',$catalogPage->created_at),
                'created_at_view' => date('d M Y',$catalogPage->created_at),
			]);

			foreach ($languages as $language){
				if(isset($catalogPage->descriptions[$language->languages_id])){
					$this->catalogPageDescriptionForm[$language->languages_id] = new CatalogsPageDescriptionForm([
						'languages_id' => $catalogPage->descriptions[$language->languages_id]->languages_id,
						'description_short' => $catalogPage->descriptions[$language->languages_id]->description_short,
						'description' => $catalogPage->descriptions[$language->languages_id]->description,
						'name' => $catalogPage->descriptions[$language->languages_id]->name,
						'slug' => $catalogPage->descriptions[$language->languages_id]->slug,
						'meta_title' => $catalogPage->descriptions[$language->languages_id]->meta_title,
						'meta_description' => $catalogPage->descriptions[$language->languages_id]->meta_description,
						'meta_keyword' => $catalogPage->descriptions[$language->languages_id]->meta_keyword,
                        'h1_tag' => $catalogPage->descriptions[$language->languages_id]->h1_tag,
                        'h2_tag' => explode("\n",$catalogPage->descriptions[$language->languages_id]->h2_tag),
                        'h3_tag' => explode("\n",$catalogPage->descriptions[$language->languages_id]->h3_tag),
					]);

				}else{
					$this->catalogPageDescriptionForm[$language->languages_id] = new CatalogsPageDescriptionForm([
						'languages_id' => $language->languages_id,
					]);
				}
				if(!empty($catalogPage->information)){
					$this->assignInformationForm = [];
					foreach ($catalogPage->information as $information){
						$this->assignInformationForm[$information->information_id] = new AssignInformationForm([
							'information_id' => $information->information_id,
							'page_title' => $information->page_title,
							'hide' => $information->hide,
						]);
					}

				}
			}
		}
    }
    public function load($data, $formName = null)
    {
        parent::load($data, $formName);
	    $success = true;
	    $success = $success & $this->catalogPageForm->load($data,'CatalogsPageForm');
	    $this->catalogPageForm->status =  (int)(Yii::$app->request->post('CatalogsPageForm')['status']??null);
	    $success = $success & Model::loadMultiple($this->catalogPageDescriptionForm, $data, 'CatalogsPageDescriptionForm');

	    $this->assignInformationForm = [];
	    if(isset($data['AssignInformationForm'])){
		    foreach($data['AssignInformationForm'] as $form){
			    $this->assignInformationForm[] = new AssignInformationForm(['information_id' => $form['information_id']]);
		    }
	    }
        return $success;
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        $parentNames = $attributeNames !== null ? array_filter((array)$attributeNames, 'is_string') : null;
        $success = parent::validate($parentNames, $clearErrors);
        $this->errorData = [];
        if(!$this->catalogPageForm->validate()){
            $this->errorData[] = $this->catalogPageForm->getErrors();
            $success = false;
        }
        foreach ($this->catalogPageDescriptionForm as $form){
	        if(!$form->validate()){
		        $this->errorData[] = $form->getErrors();
		        $success = false;
	        }
        }
        foreach ($this->assignInformationForm as $form){
            if(!$form->validate()){
                $this->errorData[] = $form->getErrors();
                $success = false;
            }
        }
        return $success;
    }
    /**
     * @return array
     */
    public function getErrorData() {
        return $this->errorData;
    }

    /**
     * @return mixed
     */
    public function getCatalogPageDescriptionForm()
    {
        return $this->catalogPageDescriptionForm;
    }

    /**
     * @return mixed
     */
    public function getCatalogPageForm()
    {
        return $this->catalogPageForm;
    }

	/**
	 * @return array
	 */
	public function getAssignInformationForm(){
		return $this->assignInformationForm;
	}

}

