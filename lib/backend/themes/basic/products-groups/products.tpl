{use class="yii\helpers\Html"}

<form action="{Yii::$app->urlManager->createUrl('products-groups/products-update')}" method="post"
      id="products_groups_products" name="products_groups_products">
    {Html::hiddenInput('products_groups_id', $eInfo->products_groups_id)}

    {\backend\design\SelectProducts::widget([
        'name' => 'box-xl-pr',
        'selectedName' => 'products_group_products_id',
        'selectedProducts' => $app->controller->view->groupProducts,
        'selectedPrefix' => 'group-product-box-',
        'selectedSortName' => 'group_sort_order'
    ])}

    <div class="btn-bar">
        <div class="btn-left"><a
                    href="{Yii::$app->urlManager->createUrl(['products-groups/index', 'eID' => $eInfo->products_groups_id])}"
                    class="btn btn-cancel-foot">{$smarty.const.IMAGE_CANCEL}</a></div>
        <div class="btn-right">
            <button class="btn btn-primary">{$smarty.const.IMAGE_SAVE}</button>
        </div>
    </div>
</form>