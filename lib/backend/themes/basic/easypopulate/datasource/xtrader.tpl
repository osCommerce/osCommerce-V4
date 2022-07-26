{use class="yii\helpers\Html"}
{use class="yii\helpers\ArrayHelper"}
<div class="scroll-table-workaround">
    <div class="w-line-row w-line-row-1">
        <div class="wl-td">
            <label>Supplier Name:</label> {Html::textInput('datasource['|cat:$code|cat:'][supplierName]', $supplierName, ['class' => 'form-control'])}
        </div>
    </div>
    <div class="w-line-row w-line-row-1">
        <div class="wl-td">
            {assign var=location_server value=$location}
            {if $location_server eq ''}
                {$location_server="http://www.xtrader.co.uk/catalog/bedroompleasures.xml"}
            {/if}
            <label>Catalog Path:</label> {Html::textInput('datasource['|cat:$code|cat:'][location]', $location_server, ['class' => 'form-control'])}
        </div>
    </div>
    <div class="w-line-row w-line-row-1">
        <div class="wl-td">
            {assign var=media_server value=$media_location}
            {if $media_server eq ''}
                {$media_server="http://www.xtrader.co.uk/nvimages/"}
            {/if}
            <label>Media path:</label> {Html::textInput('datasource['|cat:$code|cat:'][media_location]', $media_server, ['class' => 'form-control'])}
        </div>
    </div>
    <div class="w-line-row w-line-row-1">
        <div class="wl-td">
            {assign var=stock_server value=$location_stock}
            {if $stock_server eq ''}
                {$stock_server="http://www.xtrader.co.uk/catalog/xml-feed/stock.xml"}
            {/if}
            <label>Stock Path:</label> {Html::textInput('datasource['|cat:$code|cat:'][location_stock]', $stock_server, ['class' => 'form-control'])}
        </div>
    </div>
    <div class="w-line-row w-line-row-1">
        <div class="wl-td">
            {Html::hiddenInput('datasource['|cat:$code|cat:'][categories_id]', $categories_id, ['id' => 'cid'])}
            <label>Import to category:</label>
              <span class="select_filter_categories">
                  <input type="text" id="categories_acx" name="none" value="{$category_name}">
              </span>
        </div>        
    </div>
    <div class="w-line-row w-line-row-1">
        <div class="wl-td">
            <label>Tax:</label>
            {assign var=taxes value=\common\helpers\Tax::getTaxClassesVariants()}
            {$taxes=ArrayHelper::map($taxes, 'id', 'text')}
            {Html::dropDownList('datasource['|cat:$code|cat:'][tax]', $tax, $taxes, ['class' => 'form-control'])}            
        </div>        
    </div>
    <div class="w-line-row w-line-row-1">
        <div class="wl-td">
            <label>Truncate Products:</label> {Html::checkbox('datasource['|cat:$code|cat:'][truncate_products]', $truncate_products)}
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
    $('#categories_acx').autocomplete({
        //source: source_array,
        appendTo: '.select_filter_categories',
        source: "{$select_filter_categories_auto_complete_url}",
        minLength: 0,
        autoFocus: true,
        delay: 400,
        search: function( event, ui ) {
            $('#cid').val(0);
        },
        select: function( event, ui ) {
            event.preventDefault();
            $('#cid').val(ui.item.id);
            $('#categories_acx').val(ui.item.value);
            $('#categories_acx').trigger('blur');
        }
    }).focus(function () {
        $('#categories_acx').val('');
        $(this).autocomplete("search");
    });
    $('#categories_acx').autocomplete().data( "ui-autocomplete" )._renderItem = function( ul, item ) {
        if ( this.term && this.term!='>' ) {
            item.text = item.text.replace(new RegExp('(' + $.ui.autocomplete.escapeRegex(this.term) + ')', 'gi'), '<span class="hilite_match">$1</span>');
        }
        return $( "<li>" )
            .data("item.autocomplete", item)
            .append( "<a>" + item.text + "</a>" )
            .appendTo( ul );
    };

    $('#categories_acx').autocomplete().data('ui-autocomplete')._renderMenu = function( ul, items ) {
         var that = this;
         $.each( items, function( index, item ) {
           that._renderItemData( ul, item );
         });
         $( ul ).removeClass('ui-autocomplete').addClass( "ui-autocomplete_f_important" );
    };
    })
</script>