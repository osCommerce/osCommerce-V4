{use class="\common\helpers\Html"}

<form name="products" action="" method="post" id="products_copy" onSubmit="return copyProduct();">
    <div class="popup-heading">
        {$smarty.const.IMAGE_COPY} "{$pInfo->products_name}"
        {*$smarty.const.TEXT_INFO_HEADING_COPY_TO*}
    </div>
    <div class="popup-content">

        <div class="row mb-3">
            <div class="col-4">
                <b>{$smarty.const.TEXT_INFO_CURRENT_CATEGORIES}</b>
            </div>
            <div class="col-8" style="font-style: italic">
                {\common\helpers\Categories::output_generated_category_path($pInfo->products_id, 'product')}
            </div>
        </div>


        <div class="mb-1"><b>{$smarty.const.TEXT_INFO_COPY_TO_INTRO}</b></div>
        <div class="mb-3" style="height: 360px">
            <select name="categories_id[]" class="form-control categories-select" name="status[]" multiple="multiple" size="4" data-role="multiselect" >
                {foreach \common\helpers\Categories::get_category_tree(0, '', '', '', false, true) as $item}
                    <option value="{$item.id}"{if in_array($item.id, $cIDs)} selected disabled{/if}>{$item.text}</option>
                {/foreach}
            </select>
        </div>


        <div class="mb-1"><b>{$smarty.const.TEXT_HOW_TO_COPY}</b></div>
        <div class="">
            <label class="form-check">
                {Html::radio('copy_as', true, ['value' => 'link', 'class' => 'copy-as form-check-input'])}
                <span class="form-check-label">{$smarty.const.TEXT_COPY_AS_LINK}</span>
            </label>
        </div>
        <div>
            <label class="form-check">
                {Html::radio('copy_as', false, ['value' => 'duplicate', 'class' => 'copy-as form-check-input'])}
                <span class="form-check-label">{$smarty.const.TEXT_COPY_AS_DUPLICATE}</span>
            </label>
        </div>

        <div class=" copy-dup ms-4" id='copy_dup_selected' style="display: none;">
            <label class="form-check">
                {Html::checkbox('copy_categories', false, ['value' => 1])}
                {$smarty.const.TEXT_COPY_CATEGORIES}
            </label>

            {if \common\helpers\Attributes::has_product_attributes($pInfo->products_id, true)}
                <label class="form-check">
                    {Html::checkbox('copy_attributes', true, ['value' => 1])}
                    {$smarty.const.TEXT_COPY_ATTRIBUTES}
                </label>
            {/if}
        </div>


    </div>
    <div class="popup-buttons">
        <button class="btn btn-cancel" onClick="return resetStatement()">{$smarty.const.IMAGE_CANCEL}</button>
        <button class="btn btn-copy btn-confirm">{$smarty.const.IMAGE_COPY}</button>
        <input type="hidden" name="products_id" value="{$pInfo->products_id}">
        <input type="hidden" name="from_categories_id" value="{$pInfo->categories_id}">
    </div>


</form>
<script type="text/javascript">
    (function($){
        $('.categories-select').multipleSelect({
            filter: true,
            place:'{$smarty.const.TEXT_SEARCH_ITEMS}',
            isOpen: true,
            keepOpen: true,
            maxHeight: 300,
            selectAll: false,
            data: [
                {foreach \common\helpers\Categories::get_category_tree(0, '', '', '', false, true) as $item}
                {
                    text: wrapCategory('{$item.text}'),
                    value: '{$item.id}',
                    {if in_array($item.id, $cIDs)}
                    selected: true,
                    disabled: true
                    {/if}
                },
                {/foreach}
            ],
            onFilter: function (t) {
                if (t) {
                    $('.categories-select').addClass('searching')
                } else {
                    $('.categories-select').removeClass('searching')
                }
            }
        });
        function wrapCategory(str) {
            let lastIndex = str.lastIndexOf("&nbsp;&nbsp;&gt;&nbsp;&nbsp;");

            if (lastIndex !== -1) {
                lastIndex = lastIndex + 28;
                const startCategory = str.substring(0, lastIndex);
                const endCategory = str.substring(lastIndex);

                return `<span class="in-category">${ startCategory}</span>${ endCategory}`.replaceAll('&nbsp;', '<span class="nbsp">&nbsp;</span>');
            }

            return str;
        }

        $('.copy-as').on('click', function() {
            if ($(this).val() == 'duplicate') {
                $('#copy_dup_selected').show();
            } else {
                $('#copy_dup_selected').hide();
            }
        });
    })(jQuery);
</script>