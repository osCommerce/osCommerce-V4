<?php

function smarty_function_output_label($params)
{
  $colon = ':';
  if (stripos(\Yii::$app->language,'fr')===0 ){
    $colon = '&nbsp;:&nbsp;';
  }
  $text = '';
  foreach ($params as $_key => $_val) {
    switch ($_key) {
      case 'colon':
        $$_key = $_val;
        break;
      case 'const':
        $text = defined($_val)?constant($_val):'';
        break;
      case 'text':
          $$_key = $_val;
        break;

      default:
        break;
    }
  }

  $label = empty($text)?'':($text.$colon);

  return $label;
}
