{use class="yii\helpers\Url"}
{use class="yii\helpers\Html"}
<style>
.white {
 background-color: #fff!important;
}
</style>
<div class="widget box platform-holidays">            
            <div class="widget-content">
             <div class="alert fade in hide ">
                  <i data-dismiss="alert" class="icon-remove close"></i>
                  <span id="message_place"></span>
              </div>
              <form name="holi-day" method="post" action="{Url::to('platforms/holidays')}" >
                {Html::input('hidden', 'platform_id', $platform_id)}
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable table-selectable datatable"
                       checkable_list="0">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->holidaysTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
                <div class="btn-bar">
                    <div class="btn-left"><a class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</a></div>
                    <input type="hidden" name="action" value="predefine">
                    <div class="btn-right"><input type="submit" class="btn btn-primary" value='{$smarty.const.IMAGE_CONFIRM}'></div>
                </div>
                </form>
        </div>       
</div>
<script>
var hTable;

function removeRow(obj){
    var year = $('input[type=search]').val();
    hTable.row($(obj).parents('td')).remove().draw();
    //$('input[type=search]').datepicker('update', new Date(year, 1, 1));
}

 $(document).ready(function(){
    hTable = $('.platform-holidays table').DataTable();
    $(hTable.context[0].aanFeatures.f).append('<button class="btn btn-default holiday-more" style="float:left;">{$smarty.const.BUTTON_ADD_MORE}</button>');    
    $('.input-group-addon', hTable.context[0].aanFeatures.f).remove();
    $(hTable.context[0].aanFeatures.f).css('width', '100%').parent().css('width', '100%');
    
    
    function addRow(value){
        var sVal = $('input[type=search]').val();
        var row = hTable.rows.add([' ']).draw(false);
        var node = row.nodes();
        $('td', node).html('<input name="hdate[]" class="form-control new-datepicker hdate-input" placeholder="{$smarty.const.PULL_DOWN_DEFAULT}" value="'+ value + '" style="width:88%">&nbsp;<a href="javascript:void(0)" onclick="return removeRow(this);" class="btn">-</a>');
        var _new = $('.new-datepicker').datepicker({
            dateFormat: "{$smarty.const.DATE_FORMAT_DATEPICKER}",
            autoclose: true
        });
        //$(_new).datepicker('update', new Date($(_new).val()));
        $(_new).removeClass('new-datepicker');
        $('input[type=search]').val(sVal);
    }
    
    {if $dates|count > 0}
        {foreach $dates as $id => $date}
            addRow('{$date}');
        {/foreach}
    {/if}
    
    $(hTable.context[0].aanFeatures.f).find('input[type=search]').val('{$hyear}').attr('name', 'search').attr('readonly', 'readonly').css({ 'cursor': 'pointer' }).addClass('white');
    
    $('input[type=search]').datepicker({
        dateFormat:'yy',
        autoclose:true,
        minViewMode:2,
    }).on('change', function(e){
        var that = this;
        //var year = (new Date(e.date)).getFullYear();
        // $(this).val(year);
        $(this).datepicker('refresh');
        $.post('platforms/holidays', 
         $('form[name=holi-day]').serialize() + '&action=load'
        , function(data, status){
            $.each($('input.hdate-input'), function(i, e){
                removeRow(e);
            });
            if (Array.isArray(data) && data.length > 0){
                $.each(data, function (i, e){
                    addRow(e);
                });
            }
            
            //$(that).datepicker('update', new Date(year, 1, 1));
        }, 'json');
    });    
    
    $('.holiday-more').on('click', function(e){
        e.preventDefault();
        var year = $('input[type=search]').val();
        addRow('');
        //$('input[type=search]').datepicker('update', new Date(year, 1, 1));
        return false;
    })
    
    $('form[name=holi-day]').submit(function(){
       var form = this;
       $('.alert').addClass('hide');
       $.post('platforms/holidays', 
        $(form).serialize(),
        function (data, status){
            if (status == 'success'){
                $('.alert').addClass('alert-'+data.messageType).removeClass('hide');
                $('.alert #message_place').html(data.message);
                setTimeout(function(){
                    $('.pop-up-close').trigger('click');
                }, 1000);
            }
        }, "json");
       return false; 
    });
 });
</script>