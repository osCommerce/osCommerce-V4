<?php

function smarty_function_field_label($params)
{
  $colon = ':';
  if (stripos(\Yii::$app->language,'fr')===0 ){
    $colon = '&nbsp;:&nbsp;';
  }
  $text = '';
  $required_text = '';
  foreach ($params as $_key => $_val) {
    switch ($_key) {
      case 'colon':
      case 'required_text':
        $$_key = $_val;
        break;
      case 'const':
        $text = defined($_val)?constant($_val):'';
        break;
      case 'text':
          $$_key = $_val;
        break;
      case 'configuration':
          if (defined($_val)) {
              if (constant($_val) == 'required' || constant($_val) == 'required_register') {
                  $required_text = REQUIRED_TEXT;
              }
          }
        break;

      default:
        break;
    }
  }

  $label = empty($text)?'':($text.$colon);
  if ( $required_text ) {
    $label .= '<span class="required">'.$required_text.'</span>';
  }

  return $label;
}
