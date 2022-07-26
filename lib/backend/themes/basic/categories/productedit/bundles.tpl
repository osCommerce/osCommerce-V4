
<div class="after bundl-box dis_module">
  <div class="attr-box attr-box-1">
    <div class="widget widget-attr-box box box-no-shadow" style="margin-bottom: 0;">
      <div class="widget-header">
        <h4>{$smarty.const.FIND_PRODUCTS}</h4>
        <div class="box-head-serch after">
            <input type="search" id="bundles-search-by-products" placeholder="{$smarty.const.SEARCH_BY_ATTR}" class="form-control" disabled>
          <button onclick="return false"></button>
        </div>
      </div>
      <div class="widget-content">
        <select size="25" style="width: 100%; height: 100%; border: none;" disabled>
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
          <input type="search" placeholder="{$smarty.const.TEXT_SEARCH_ASSIGNED_ATTR}" class="form-control" disabled>
          <button onclick="return false"></button>
        </div>
      </div>
      <div class="widget-content dis_module">
        <table class="table assig-attr-sub-table bundles-products">
          <thead>
          <tr role="row">
            <th></th>
            <th>{$smarty.const.TEXT_IMG}</th>
            <th>{$smarty.const.TEXT_LABEL_NAME}</th>
            <th>{$smarty.const.TEXT_TITLE_NUMBER}</th>
            <th></th>
          </tr>
          </thead>
          <tbody>
          {foreach $app->controller->view->bundlesProducts as $bKey => $bundles}
            <tr role="row" prefix="bundles-box-{$bundles['bundles_id']}">
              <td class="sort-pointer"></td>
              <td class="img-ast img-ast-img">
                {$bundles['image']}
              </td>
              <td class="name-ast">
                {$bundles['products_name']}
                <input type="hidden" name="bundles_id[]" value="{$bundles['bundles_id']}" />
              </td>
              <td class="bu-num plus_td">
                <span class="pr_plus"></span><input type="text" name="sets_num_product[]" value="{$bundles['num_product']}" class="form-control" /><span class='pr_minus'></span>
              </td>
              <td class="remove-ast"></td>
            </tr>
          {/foreach}
          </tbody>
        </table>
        <input type="hidden" value="" name="bundles_sort_order" id="bundles_sort_order"/>
      </div>
      <div class="bu-box-set">
        <span><label>{$smarty.const.TEXT_SET_PRICE}</label><input type="text" class="form-control" disabled /></span>
        <span><label>{$smarty.const.TEXT_SET_DISCOUNT}</label><input type="text" class="form-control" placeholder="0.00%" disabled /></span>
      </div>
    </div>
  </div>
</div>
