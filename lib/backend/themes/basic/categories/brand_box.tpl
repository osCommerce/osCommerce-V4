

<ul>
    <li class="li_block"><span class="brand_li"><span id="0" onclick="changeBrand(this)">{$smarty.const.TEXT_ALL}</span></span></li>
     {foreach $app->controller->view->brandsList as $brandItem}
         <li id="brands-{$brandItem.id}" class="li_block{if $brandItem.id == $app->controller->view->brand_id|default:null} selected{/if}">

             <span class="handle"><i class="icon-hand-paper-o"></i></span>
             <span class="brand_li">
                 <span class="brand_text" id="{$brandItem.id}" onClick="changeBrand(this)">{$brandItem.text}</span>


                <span class="function-buttons">
                 {if \common\helpers\Acl::rule(['TEXT_LABEL_BRAND', 'IMAGE_EDIT'])}<a href="{Yii::$app->urlManager->createUrl(['categories/brandedit', 'manufacturers_id' => $brandItem.id])}" class="edit_brand"><i class="icon-pencil"></i></a>{/if}

                 {if \common\helpers\Acl::rule(['TEXT_LABEL_BRAND', 'IMAGE_DELETE'])}<a class="delete_brand" href="{Yii::$app->urlManager->createUrl(['categories/confirm-manufacturer-delete', 'manufacturers_id' => $brandItem.id])}"><i class="icon-trash"></i></a>{/if}
                </span>
             </span>
         </li>
    {/foreach}
</ul>
