<div class="xl-pr-box" id="box-xl{$xsellTypeId}-pr">
    {\backend\design\SelectProducts::widget([
        'name' => 'box-xsell-'|cat:$xsellTypeId,
        'selectedName' => 'xsell_id['|cat:$xsellTypeId|cat:']',
        'selectedProducts' => $app->controller->view->xsellProducts[$xsellTypeId],
        'selectedPrefix' => 'xsell-box-',
        'selectedSortName' => 'xsell_sort_order['|cat:$xsellTypeId|cat:']',
        'selectedBackLink' => 'xsell_backlink['|cat:$xsellTypeId|cat:']',
        'selectedBackLink_c' => 'xsell_backlink_c['|cat:$xsellTypeId|cat:']'
    ])}
</div>