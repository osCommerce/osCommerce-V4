<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">    
<style>

        .calculator ul {
            list-style-type: none;
            margin: 0; padding: 0;
            margin-bottom: 10px;
        }
        .calculator li {
            margin: 5px;
            padding: 9px 5px 5px;
            width: 220px;
            cursor: move;
            line-height: 22px;
        }
        .calculator .sortable {
            min-height: 150px;
            overflow: hidden;
        }
        .calculator li {
            display: inline-block;
            vertical-align: middle;
            position: relative;
            min-width: 150px;
            text-align: center;
            height: auto !important;
            width: auto !important;
        }
        .calculator .remove {
            position: absolute;
            right: 0;
            top: 0;
            width: 28px;
            height: 28px;
            line-height: 26px;
            text-align: center;
            font-size: 20px;
            cursor: pointer;
            display: none !important;
            background: #eee;
			color:#424242;
        }
        .calculator .sortable li:hover .remove {
            display: block !important;
        }
        .calculator .sortable-formula {
            min-width: 50px;
            min-height: 18px;
            margin: 0;
            /*display: inline-block;*/
            vertical-align: middle;
        }
        .calculator .sortable .sortable-formula {
            min-width: 100px;
            min-height: 30px;
        }
        .calculator .parenthesis-left {
            display: inline-block;
            vertical-align: middle;
        }
        .calculator .parenthesis-right {
            vertical-align: middle;
        }
        .calculator input[type="text"] {
            width: 50px;
            text-align: center;
            display: none;
        }
        .calculator .sortable li .parenthesis-left input[type="text"] {
            display: block;margin:0 0 10px;
        }
		.calculator li.ui-state-highlight{
		background:#f5f5f5;border:1px solid #bdbdbd;
		}
		.parenthesis-left-title{
		font-family:'Open Sans';font-size:14px;color:#424242;text-transform:uppercase;font-weight:700;padding:0 0 5px;display:inline-block;
		}
		#formula > li > .parenthesis-left > input, #formula > li > .parenthesis-left > span, #formula > li > ul > li > .parenthesis-left > input, #formula > li > ul > li > .parenthesis-left > span{
		display:inline-block;vertical-align:middle;padding:0;margin:0;
		}
		#formula > li > .parenthesis-left input{
		width:290px;
		}
		#formula > li > ul > li > .parenthesis-left input{
		width:100px;
		}
		#formula > li > .parenthesis-left, #formula > li > ul > li > .parenthesis-left{
		text-align:left;padding:0 0 9px 8px;
		}
		#formula > li, #formula > li > ul > li{
			text-align:left;
		}
		#formula > li li li{
			text-align:center;
		}
		#formula > li > .parenthesis-left > span, #formula > li > ul > li > .parenthesis-left > span{
		margin-right:10px;
		}
		#formula > li{
		border:4px solid #bdbdbd;
		}
		#formula > li > ul > li{
		border:2px solid #bdbdbd;
		}
    </style>
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<div class="calculator">
    <ul class="source js-tokens">
        {foreach $blocks as $block}
            <li class="draggable ui-state-highlight sortable-formula-box js-brackets">
                <input type="hidden" name="id" value="">
                <input type="hidden" name="block_id" value="{$block.block_id}">
                <span class="parenthesis-left"><span class="parenthesis-left-title">{$block.block_name}</span><input type="text" name="name" value="" class="form-control"></span>
                <ul class="sortable-formula connectedSortable ui-widget-header"></ul>
                <span class="parenthesis-right"></span>
                <span class="remove" onclick="return deleteMe(this);">&times;</span>
            </li>
        {/foreach}
    </ul>
    
{function name=renderLocationTree}
        {foreach $items as $item}
            <li class="ui-state-highlight sortable-formula-box">
                <input type="hidden" name="id" value="{$item.location_id}">
                <input type="hidden" name="block_id" value="{$item.block_id}">
                <span class="parenthesis-left"><span class="parenthesis-left-title">{$item.block_name}</span><input type="text" name="name" value="{$item.location_name}" class="form-control"></span>
                <ul class="sortable-formula connectedSortable ui-widget-header">
                    {if $item.is_final == 0}
                    {call name=renderLocationTree items=$item.children}
                    {/if}
                </ul>
                <span class="parenthesis-right"></span>
                <span class="remove" onclick="return deleteMe(this);">&times;</span>
            </li>
        {/foreach}
{/function}

    <ul class="sortable connectedSortable ui-widget-header" id="formula">
        {call renderLocationTree items=$locationsTree }
    </ul>
    
    <textarea cols="64" rows="2" id="massiv" style="display: none"></textarea>
    
    <div class="btn-bar">
        <div class="btn-left"><a href="javascript:void(0)" onclick="return backStatement();" class="btn btn-cancel-foot">{$smarty.const.IMAGE_CANCEL}</a></div>
        <div class="btn-right"><button class="btn btn-confirm js-formula-confirm">{$smarty.const.IMAGE_SAVE}</button></div>
    </div>
        
</div>    
<script>
function backStatement() {
    window.history.back();
    return false;
}
function deleteMe(obj) {
    $(obj).parent('li').remove();
    $('#formula').trigger('formulaChanged');
}

function subcalcMassiv(obj) {
    var text = [];

    $(obj).children('li').each(function( i, section ) {
        var type = [];
        type.push( $(this).children('input[name="id"]').val() );
        type.push( $(this).children('input[name="block_id"]').val() );
        type.push( $(this).children('span.parenthesis-left').children('input[name="name"]').val() );
        type.push( subcalcMassiv($(this).children('ul')) );
        text.push(type);
    });
    return text;
}
function calcMassiv() {
    var massiv = [];
    massiv.push(subcalcMassiv($('#formula')));
    $('#massiv').text(JSON.stringify(massiv));
}
$( function() {

    $( ".sortable, .sortable .sortable-formula" ).sortable({
        connectWith: ".connectedSortable",
        revert: true,
        update:function(event, ui){
            $('#formula').trigger('formulaChanged');
        }
    });

    $( ".draggable" ).draggable({
        connectToSortable: ".sortable, .sortable .sortable-formula",
        helper: "clone",
        revert: "invalid",
        stop: function( event, ui ) {

            $('#formula').find('span').show();

            $( ".sortable .sortable-formula" ).sortable({
                connectWith: ".connectedSortable",
                revert: true,
                update:function(event, ui){
                    $('#formula').find('input[type="text"]').not('.js-watch').each(function(){
                        $(this).addClass('js-watch');
                        $(this).on('keyup',function(){
                            $('#formula').trigger('formulaChanged');
                        });
                    });
                    $('#formula').trigger('formulaChanged');
                }
            });

            $('#formula').find('input[type="text"]').not('.js-watch').each(function(){
                $(this).addClass('js-watch');
                $(this).on('keyup',function(){
                    $('#formula').trigger('formulaChanged');
                });
            });

            //$( ".sortable, ..sortable sortable-formula" ).sortable("refresh");
        }
    });
    $( "ul, li" ).disableSelection();

    $( ".droppable" ).droppable({
        drop: function( event, ui ) {
            /*
          $( this )
            .addClass( "ui-state-highlight" )
            .find( "p" )
              .html( "Dropped!" );
      */

        }
    });

    $('#formula').on('formulaChanged',function() {
        calcMassiv();
    });

    $('.js-formula-confirm').on('click', function () {
        calcMassiv();
        $.post("{Yii::$app->urlManager->createUrl(['warehouses/save-location', 'id' => $id])}", { "post_data" : $('#massiv').text() } , function(data){
            backStatement();
        },'json');
    });
    
    
    
} );
</script>