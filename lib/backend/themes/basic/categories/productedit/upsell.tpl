<div class="up-pr-box dis_module" id="box-up-pr">
    {\backend\design\SelectProducts::widget([
        'name' => 'box-upsell',
        'selectedName' => 'upsell_id',
        'selectedProducts' => $app->controller->view->upsellProducts,
        'selectedPrefix' => 'upsell-box-',
        'selectedSortName' => 'upsell_sort_order'
    ])}
</div>