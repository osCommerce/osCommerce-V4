<div class="up-pr-box dis_module" id="box-up-pr">
    <div class="after">
        <div class="attr-box attr-box-1">
            <div class="widget widget-attr-box box box-no-shadow" style="margin-bottom: 0;">
                <div class="widget-header">
                    <h4>{$smarty.const.FIND_PRODUCTS}</h4>
                    <div class="box-head-serch after">
                        <input type="search" placeholder="{$smarty.const.SEARCH_BY_ATTR}" class="form-control" disabled>
                        <button onclick="return false"></button>
                    </div>
                </div>
                <div class="widget-content">
                    <select id="upsell-search-products" size="25" style="width: 100%; height: 100%; border: none;" disabled>
                    </select>
                </div>
            </div>
        </div>
        <div class="attr-box attr-box-2">
            <span class="btn btn-primary"></span>
        </div>
        <div class="attr-box attr-box-3">
            <div class="widget-new widget-attr-box box box-no-shadow" style="margin-bottom: 0;">
                <div class="widget-header">
                    <h4>{$smarty.const.FIELDSET_ASSIGNED_PRODUCTS}</h4>
                    <div class="box-head-serch after">
                        <input type="search" placeholder="{$smarty.const.SEARCH_BY_ATTR}" class="form-control" disabled>
                        <button onclick="return false"></button>
                    </div>
                </div>
                <div class="widget-content dis_module">
                    <table class="table assig-attr-sub-table upsell-products">
                        <thead>
                            <tr role="row">
                                <th></th>
                                <th>{$smarty.const.TEXT_IMG}</th>
                                <th>{$smarty.const.TEXT_LABEL_NAME}</th>
                                <th>{$smarty.const.TEXT_PRICE}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="up-assigned" >
                            {foreach $app->controller->view->upsellProducts as $upKey => $upsell}
                                <tr role="row" prefix="upsell-box-{$upsell['upsell_id']}" class="{$upsell['status_class']}">
                                    <td class="sort-pointer"></td>
                                    <td class="img-ast img-ast-img">
                                        {$upsell['image']}
                                    </td>
                                    <td class="name-ast name-ast-xl">
                                        {$upsell['products_name']}
                                    </td>
                                    <td class="ast-price ast-price-xl">
                                        {$upsell['price']}
                                        <input type="hidden" name="upsell_id[]" value="{$upsell['upsell_id']}" />
                                    </td>
                                    <td class="remove-ast"></td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>