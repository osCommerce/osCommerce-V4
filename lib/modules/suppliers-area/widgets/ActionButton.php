<?php

namespace suppliersarea\widgets;

use Yii;
use yii\helpers\Html;

class ActionButton extends \yii\base\Widget {
    
    public $template;
    public $options = [];
    public $url;    

    public function init() {
        parent::init();
        $this->options = array_merge([
            //'data-pjax' => '0',
        ], $this->options);        
    }

    public function run() {
        switch($this->template){
            case '{update}': 
                return $this->renderUpdateTemplate();
                break;
            case '{propose}':
                return $this->renderProposeTemplate();
                break;
            case '{save}':
                return $this->renderSaveTemplate();
                break;
        }
        
    }
    
    public function renderUpdateTemplate(){
        $this->options['title'] = 'Update';
        $this->options['aria-label'] = 'Update';

        $icon = Html::tag('span', '', ['class' => "glyphicon glyphicon-pencil"]);
        return Html::a($icon, $this->url, $this->options);
    }
    
    public function renderProposeTemplate(){
        $this->options['title'] = 'Propose';
        $this->options['aria-label'] = 'Propose';

        $icon = Html::tag('span', '', ['class' => "glyphicon glyphicon-plus"]);
        return Html::a($icon, $this->url, $this->options);
    } 
    
    public function renderSaveTemplate(){
        $this->options['title'] = 'Save';
        $this->options['aria-label'] = 'Save';

        $icon = Html::tag('span', '', ['class' => "glyphicon glyphicon-saved"]);
        return Html::a($icon, $this->url, $this->options);
    } 
    
}
