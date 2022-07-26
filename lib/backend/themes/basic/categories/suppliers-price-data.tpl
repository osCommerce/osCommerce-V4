{use class="\common\helpers\Html"}
    <div id="suppliers-placeholder">
    {foreach $supplier_data as $data}
        {include file="../categories/category-supplier-block.tpl" sInfo=$data mayEditCost = $mayEditCost}{*currenciesVariants=$supplierCurrenciesVariants*}
    {/foreach}
    </div>
{if not $singleSupplier}
    <div class="ed-sup-btn-box">
        <a href="{Yii::$app->urlManager->createUrl(['categories/supplier-select','mode'=>'category','except'=>''])}" class="btn js-append_supplier">{$smarty.const.TEXT_SELECT_ADD_SUPPLIER}</a>
    </div>
{/if}
    {Html::hiddenInput('supplier_price_rule_present','1')}
{*<script type="text/template" id="supplier-price-rule-tpl">
    {include file="../categories/supplier-price-rule.tpl" supplier_idx='__supplier_id__' supplier_rule_idx='__supplier_rule_id__' currenciesVariants=[]}
</script>*}
<script type="text/template" id="supplier-discount-row-tpl">
    {include file="../categories/supplier-quantity-discount-row.tpl" supplier_idx='__supplier_id__' row_idx='__row_idx__'}
</script>
<script type="text/javascript">
    $(document).ready(function() {


        // --- supplier operation --
        {if $mayEditCost}
        $('.js-append_supplier').popUp({
            one_popup: false,
            box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='pop-up-close'></div><div class='popup-heading cat-head'>{$smarty.const.TEXT_SELECT_ADD_SUPPLIER}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
        });
        {/if}
        $('#suppliers-placeholder').on('click', '.js-supplier-rule-set-remove', function (event) {
            $(this).parents('.js-supplier-rule-set').remove();
        });
        $('#suppliers-placeholder').on('click', '.js-ro-edit',function (event) {
            var $root = $(this).parents('.js-supplier-rule-set');
            var suppliers_id = $root.data('supplier-id');
            $.post(
                '{Yii::$app->urlManager->createUrl(['categories/supplier-add','mode'=>'category'])}',
                { suppliers_id:suppliers_id, mode:'category' },
                function (data) {
                    $root.html($(data).html());
                    $('#suppliers-placeholder').trigger('supplier_added',[{ 'suppliers_id' : suppliers_id, 'uprid' : '' }]);
                }
            );
        });
        // --- supplier operation --

        $('#suppliers-placeholder').on('click', '.js-add-supplier-rule', function () {
            var $rulesRoot = $(this).parents('.js-supplier-rule-set');
            var _row_html = $('#supplier-price-rule-tpl-'+$rulesRoot.data('supplier-id')).text();
            var $rulesList = $rulesRoot.find('.js-supplier-rule-table');
            var idx = $rulesList.data('index');
            $rulesList.data('index',parseInt(idx,10)+1);
            _row_html = _row_html.replace(/__supplier_id__/g, $rulesRoot.data('supplier-id')).replace(/__supplier_rule_id__/g, idx);
            var $row = $(_row_html);
            var def = $rulesRoot.data('rule-default');
            $('input[name$="[supplier_discount]"]',$row).attr('placeholder',def['supplier_discount']);
            $('input[name$="[surcharge_amount]"]',$row).attr('placeholder',def['surcharge_amount']);
            $('input[name$="[margin_percentage]"]',$row).attr('placeholder',def['margin_percentage']);
            $('input[name$="[price_formula_text]"]',$row).val(def['price_formula_text']);
            $('input[name$="[price_formula]"]',$row).val(def['price_formula']);
            $('select[name$="[currencies_id]"]',$row).val(def['currencies_id']);

            $rulesList.append($row);
            $rulesRoot.find('.js-row-condition').trigger('change');
            $('.js-change-supplier-rule-condition', $rulesRoot).show();
        });
        $('#suppliers-placeholder').on('click', '.js-remove-supplier-rule', function () {
            var $rulesList = $(this).parents('.js-supplier-rule-table');
            $(this).parents('.js-supplier-rule-row').remove();
            if ( $rulesList.find('tr').length==0 ) {
                var $supplierRoot = $rulesList.parents('.js-supplier-rule-set');
                $('.js-change-supplier-rule-condition',$supplierRoot).hide();
                $('.js-add-supplier-rule',$supplierRoot).show();
            }
        });
        $('.js-supplier-rule-set').each(function(){
            var _this = $(this);
            if ( $('.js-table-rules tbody tr', _this).length==0 ) {
                $('.js-change-supplier-rule-condition', _this).hide();
            }
        });

        // --- rules table mutate --
        $('#suppliers-placeholder').on('change', '.js-row-condition', function (event) {
            var selectedCondition = $(this).val().split(',');
            if (selectedCondition.length>0 && selectedCondition[0]==='') selectedCondition = [];
            var $root = $(this).parents('.js-supplier-rule-set');
            var $rulesTable = $root.find('.js-table-rules');
            $rulesTable.find('.js-cond').each(function(){
                var $cell = $(this);
                var needShow = false;
                for(var i=0; i<selectedCondition.length;i++) {
                    needShow = $cell.hasClass('js-cond-'+selectedCondition[i]);
                    if ( needShow ) break;
                }
                if (needShow){
                    $cell.show();
                }else{
                    $cell.hide();
                }
            });
        });
        $('#suppliers-placeholder .js-row-condition').trigger('change'); // on load

        $('#suppliers-placeholder').on('supplier_added', function (event, info) {
            $('#suppliers-'+info.suppliers_id+' .js-row-condition').trigger('change');
        });

        // --- rules table mutate --

        // --- condition popup ---
        function popUpCondition(selected){
            return "<p style=\"margin:10px 0\"><label><input type=\"checkbox\" value=\"fromTo\" "+((','+selected||''+',').indexOf('fromTo')===-1?'':' checked="checked"')+" style=\"vertical-align:middle\">{$smarty.const.TEXT_SUPPLIER_RULE_CONDITION_FROM_TO}</label></p>"+
                "<p style=\"margin:10px 0\"><label><input type=\"checkbox\" value=\"notBelow\" "+((','+selected||''+',').indexOf('notBelow')===-1?'':' checked="checked"')+" style=\"vertical-align:middle\">{$smarty.const.TEXT_SUPPLIER_RULE_CONDITION_NOT_BELOW}</label></p>";
        }
        $('#suppliers-placeholder').on('click', '.js-change-supplier-rule-condition', function () {
            var supplierContainerId = $(this).parents('.js-supplier-rule-set').attr('id');
            $('<a href="javascript:void(0)"></a>').popUp({
                one_popup: false,
                box: "<div class='popup-box-wrap popup-wrap-price-rule-condition'><div class='around-pop-up'></div><div class='popup-box popup-box-price-rule-condition' rel='"+supplierContainerId+"' style='width:320px'><div class='pop-up-close'></div><div class='popup-heading cat-head'>{$smarty.const.TEXT_ADD_CONDITION}</div><div class='pop-up-content' style=\"margin:10px\">"+popUpCondition($('#'+supplierContainerId+' .js-row-condition').val())+"<div class=\"btn-toolbar\"><button class=\"pull-left btn btn-cancel\">{$smarty.const.IMAGE_CANCEL}</button><button class=\"pull-right btn btn-confirm js-popup-condition-confirm\">{$smarty.const.IMAGE_ADD}</button></div></div></div></div>",
                position: function(popup_box){
                    var d = ($(window).height() - $('.popup-box-price-rule-condition').height()) / 2;
                    if (d < 0) d = 30;
                    $('.popup-wrap-price-rule-condition').css('top', $(window).scrollTop() + d);
                }
            }).trigger('click');
        });
        $(document).on('click','.popup-box .js-popup-condition-confirm', function () {
            var $popupContainer = $(this).parents('.popup-box');
            var selected = [];
            $popupContainer.find('input:checked').each(function () {
                selected.push(this.value);
            });
            var $condInput = $('#'+$popupContainer.attr('rel')+' .js-row-condition');
            $condInput.val(selected.join(','));
            $condInput.trigger('change');
            $popupContainer.find('.pop-up-close').trigger('click');
        });

        // --- condition popup ---

        {if !(isset($disableFormulaEditor) && $disableFormulaEditor)}
        // -- price formula --
        $('#suppliers-placeholder').on('click', '.js-price-formula', function(event){
            var field = $(this).data('formula-rel');
            if ( !field ) {
                field = $(event.target).parents('.js-price-formula-group').find('.js-price-formula-data').attr('name');
                field = 'input[name="'+field+'"]';
            }
            var allowed_params = $(this).data('formula-allow-params')||'';

            bootbox.dialog({ message: '<iframe src="{$app->urlManager->createUrl(['popups/price-formula-editor','s'=>(float)microtime()])}&formula_input='+encodeURIComponent(field)+'&allowed_params='+encodeURIComponent(allowed_params)+'" width="900px" height="420px" style="border:0"/>' });
            bootbox.setDefaults( { size:'large', onEscape:true, backdrop:true });
        });

        window.priceFormulaRetrieve = function (inputSelector){
            var jsonString = $(inputSelector).val();
            if ( jsonString ) {
                return JSON.parse(jsonString);
            }
            return { };
        };

        window.priceFormulaUpdate = function (inputSelector, formulaObject ) {
            var $targetDataInput = $(inputSelector);
            $targetDataInput.val( JSON.stringify(formulaObject) );
            $targetDataInput.parents('.js-price-formula-group').find('.js-price-formula-text').val($.trim(formulaObject.text));
            bootbox.hideAll();
        };
        // -- price formula --
        {/if}

/* qty disc*/
        var initDtSwitcher = function(root){
            if (!$('.js-supplier-qdt-switcher', root).is(':checked')){
                $('.js-qdt',root).hide();
            }
            $('.js-supplier-qdt-switcher', root).bootstrapSwitch({
                onSwitchChange: function (element, state) {
                    if ( state ) {
                        $('.js-qdt',root).show();
                    }else{
                        $('.js-qdt',root).hide();
                    }
                    return true;
                },
                onText: "{$smarty.const.SW_ON}",
                offText: "{$smarty.const.SW_OFF}",
                handleWidth: '38px',
                labelWidth: '24px'
            });
        };
        $('.js-supplier-rule-set').each(function(){
            initDtSwitcher($(this));
        }); // init on load

        $('#suppliers-placeholder').on('supplier_added', function (event, info) {
            initDtSwitcher($('#suppliers-'+info.suppliers_id));
        }); // added supplier

        $('#suppliers-placeholder').on('click','.js-qdt-add-row', function(event){
            var $root = $(event.target).parents('.js-qdt');
            var $table = $root.find('.js-qdt-table');
            var $rulesRoot = $(this).parents('.js-supplier-rule-set');

            var _row_html = $('#supplier-discount-row-tpl').text();

            var idx = $table.data('index');
            $table.data('index',parseInt(idx,10)+1);
            _row_html = _row_html.replace(/__supplier_id__/g, $rulesRoot.data('supplier-id')).replace(/__row_idx__/g, idx);

            $('tbody',$table).append(_row_html);
        });

        $('#suppliers-placeholder').on('click','.js-qdt-remove-row',function(event){
            $(this).parents('tr').remove();
        });

    });
</script>