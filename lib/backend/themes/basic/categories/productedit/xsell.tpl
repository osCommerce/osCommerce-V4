<div class="xl-pr-box" id="box-xl{$xsellTypeId}-pr">
    <div class="after">
        <div class="attr-box attr-box-1">
            <div class="widget widget-attr-box box box-no-shadow" style="margin-bottom: 0;">
                <div class="widget-header">
                    <h4>{$smarty.const.FIND_PRODUCTS}</h4>
                    <div class="box-head-serch after">
                        <input type="search" id="xsell{$xsellTypeId}-search-by-products" data-target="xsell{$xsellTypeId}-search-products" placeholder="{$smarty.const.SEARCH_BY_ATTR}" class="form-control xsell-search-by-products">
                        <button onclick="return false"></button>
                    </div>
                </div>
                <div class="widget-content">
                  <select multiple="multiple" id="xsell{$xsellTypeId}-search-products" size="25" style="width: 100%; height: 100%; border: none;" ondblclick="addSelectedXSell({$xsellTypeId})">
                    </select>
                </div>
            </div>
        </div>
        <div class="attr-box attr-box-2">
            <span class="btn btn-primary" onclick="addSelectedXSell({$xsellTypeId})"></span>
        </div>
        <div class="attr-box attr-box-3">
            <div class="widget-new widget-attr-box box box-no-shadow" style="margin-bottom: 0;">
                <div class="widget-header">
                    <h4>{$smarty.const.FIELDSET_ASSIGNED_PRODUCTS}</h4>
                    <div class="box-head-serch after">
                        <input type="search" id="search-xp{$xsellTypeId}-assigned" placeholder="{$smarty.const.SEARCH_BY_ATTR}" class="form-control">
                        <button onclick="return false"></button>
                    </div>
                </div>
                <div class="widget-content">
                    <table class="table assig-attr-sub-table xsell-products xsell{$xsellTypeId}-products">
                        <thead>
                        <tr role="row">
                            <th></th>
                            <th>{$smarty.const.TEXT_IMG}</th>
                            <th>{$smarty.const.TEXT_LABEL_NAME}</th>
                            <th>{$smarty.const.TEXT_PRICE}</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody id="xp{$xsellTypeId}-assigned" data-target-type-id="{$xsellTypeId}">
                        {foreach $app->controller->view->xsellProducts[$xsellTypeId] as $xKey => $xsell}
                            <tr role="row" prefix="xsell-box-{$xsell['xsell_id']}" class="{$xsell['status_class']}">
                                <td class="sort-pointer"></td>
                                <td class="img-ast img-ast-img">
                                    {$xsell['image']}
                                </td>
                                <td class="name-ast name-ast-xl">
                                    {$xsell['products_name']}                                    
                                </td>
                                <td class="ast-price ast-price-xl">
                                    {$xsell['price']}
                                    <input type="hidden" name="xsell_id[{$xsellTypeId}][]" value="{$xsell['xsell_id']}" />
                                </td>
                                <td class="remove-ast" onclick="deleteSelectedXSell(this)"></td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                    <input type="hidden" value="" name="xsell_sort_order[{$xsellTypeId}]" id="xsell{$xsellTypeId}_sort_order"/>
                </div>
            </div>
        </div>
    </div>
</div>