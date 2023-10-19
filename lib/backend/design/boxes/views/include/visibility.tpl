<div class="visibility">

    <div class="row">
        <div class="col visibility-pages">
            <h4>{$smarty.const.TEXT_PAGES}</h4>
            {$pages = [
                'visibility_home' => $smarty.const.TEXT_HOME,
                'visibility_product' => $smarty.const.TEXT_PRODUCT,
                'visibility_catalog' => $smarty.const.TEXT_LISTING,
                'visibility_info' => $smarty.const.TEXT_INFORMATION,
                'visibility_cart' => $smarty.const.TEXT_SHOPPING_CART,
                'visibility_checkout' => $smarty.const.TEXT_CHECKOUT,
                'visibility_success' => $smarty.const.TEXT_CHECKOUT_SUCCESS,
                'visibility_account' => $smarty.const.TEXT_ACCOUNT,
                'visibility_login' => $smarty.const.TEXT_LOGIN_CREATE_ACCOUNT,
                'visibility_other' => $smarty.const.TEXT_OTHER
            ]}
            <label class="form-check">
                <input type="checkbox"
                       name="visibility-page-all"
                       class="form-check-input visibility-page-all"/>
                {$smarty.const.TEXT_ALL}
            </label>
            {foreach $pages as $page => $title}
                <label class="form-check">
                    <input type="checkbox"
                           name="setting[0][{$page}]"
                           class="form-check-input visibility-page"
                           {if !$settings[0][$page]} checked{/if}/>
                    {$title}
                </label>
            {/foreach}
        </div>
        <div class="col">
            <h4>&nbsp;</h4>
            {$statuses = [
                'visibility_first_view' => 'First visit',
                'visibility_more_view' => 'More then one visit',
                'visibility_logged' => 'Logged in',
                'visibility_not_logged' => 'No logged in'
            ]}
            {foreach $statuses as $status => $title}
                <label class="form-check">
                    <input type="checkbox"
                           name="setting[0][{$status}]"
                           class="form-check-input"
                            {if !$settings[0][$status]} checked{/if}/>
                    {$title}
                </label>
            {/foreach}
        </div>

        {foreach \common\helpers\Hooks::getList('design/box-edit', 'hide-widget') as $filename}
            {include file=$filename}
        {/foreach}

    </div>
</div>


<script>
    $(function () {
        const $checkBoxes = $('.visibility-page');
        const $checkBoxAll = $('.visibility-page-all');
        $checkBoxAll.on('change', function () {
            if ($checkBoxAll.prop('checked')) {
                $checkBoxes.prop('checked', true).trigger('change')
            } else {
                $checkBoxes.prop('checked', false).trigger('change')
            }
        });
        checkAll();
        $checkBoxes.on('change', checkAll);
        function checkAll() {
            let allChecked = true;
            $checkBoxes.each(function () {
                if (!$(this).prop('checked')) {
                    allChecked = false
                }
            });
            if (allChecked) {
                $checkBoxAll.prop('checked', true);
            } else {
                $checkBoxAll.prop('checked', false);
            }
        };
    })
</script>