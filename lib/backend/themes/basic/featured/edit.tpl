{tep_draw_form('save_item_form', 'reviews/index', \common\helpers\Output::get_all_get_params( array( 'action' ) ), 'post', 'id="save_item_form" onSubmit="return saveItem();"' )}

{tep_draw_hidden_field( 'item_id', $item_id )}
{tep_draw_hidden_field( 'featured_type_id', $featured_type_id )}


<div class="or_box_head">{$header}</div>


{if $item_id}
    <div class="main_row_el after" style="margin-bottom: 10px">
        <div class="mt_left">{$smarty.const.TEXT_FEATURED_PRODUCT}</div>
        <div class="mt_value">{$product}</div>
    </div>
    <div class="main_row_el after" style="margin-bottom: 10px">
        <div class="mt_left">{$smarty.const.TABLE_HEADING_STATUS}:</div>
        <div class="mt_value">
            <input type="checkbox" value="1" name="status" class="check_on_off" {if $status_checked_active} checked{/if}>
        </div>
    </div>
{else}
    <div class="main_row after" style="margin-bottom: 10px">
        <div class="main_title">{$smarty.const.TEXT_FEATURED_PRODUCT}</div>
        <div class="main_value">
            <input type="text" class="product-name form-control"/>
            <input type="hidden" name="products_id" class="product-id"/>
        </div>
    </div>
{/if}

    <div class="main_row" style="margin-bottom: 10px">
        <div class="main_title">{$smarty.const.TEXT_FEATURED_EXPIRES_DATE}</div>
        <div class="main_value">
            <input type="text" name="expires_date" value="{$expires_date}" class="datepicker form-control">
        </div>
    </div>

<div class="btn-toolbar btn-toolbar-order">
    <button class="btn btn-no-margin">{$smarty.const.IMAGE_SAVE}</button><input class="btn btn-cancel" type="button" onclick="return resetStatement()" value="{$smarty.const.IMAGE_CANCEL}">
</div>
</form>
<script type="text/javascript">
$(function(){
    $( ".datepicker" ).datepicker({
        changeMonth: true,
        changeYear: true,
        showOtherMonths:true,
        autoSize: false,
        minDate: "1",
        dateFormat: "{$smarty.const.DATE_FORMAT_DATEPICKER}",
    });

    {if !$item_id}
    $.fn.searchProduct = function(options){
        let op = $.extend({
            url: 'index/search-suggest',
            suggestWrapClass: 'search-product',
            suggestClass: 'suggest',
            suggestItem: 'a',
            itemTextBox: '.td_name',
            suggestItemClick: function(input, itemObj){},
        },options);

        return this.each(function() {

            let searchInput = $(this);
            let suggestWrap = $(`<div class="${ op.suggestWrapClass}"></div>`);
            let suggest = $(`<div class="${ op.suggestClass}"></div>`);

            searchInput.after(suggestWrap);
            suggestWrap.append(suggest);
            suggest.hide();

            searchInput.on('keyup', function(e){
                $.get(op.url, {
                    keywords: searchInput.val(),
                    no_click: true
                }, function(data){
                    suggest.show().html(data);
                })
            });

            suggest.on('click', op.suggestItem, function(e){
                e.preventDefault();

                searchInput.val($(op.itemTextBox, this).text());
                suggest.hide().html('');

                op.suggestItemClick(searchInput, $(this));
                searchInput.trigger('suggestItemClick', [searchInput, $(this)]);

                return false
            });

            searchInput.on('blur', function(){
                setTimeout(function() {
                    suggest.hide();
                }, 300);
            })
        })
    };
    $('.product-name').searchProduct({
        suggestItemClick: function(input, itemObj){
            $('input[name="products_id"]', input.closest('.row')).val(itemObj.data('id'));
        }
    });
    {/if}
})
</script>