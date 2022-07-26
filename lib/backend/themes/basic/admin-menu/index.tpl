<style>
    .sortable { min-height: 27px; }
</style>
{function name=renderMenuTree level=0}
    <ul class="dd-list sortable">
        {foreach $items as $item}
            <li class="dd-item draggable" id="{$item.box_id}">
                <div class="item-handle dd-handle "><div class="item-handle-move"></div>{if $item.box_type == 1}<div class="item-close" style="display: block;"></div>{/if}<a class="edit-menu" href="{$app->urlManager->createUrl('admin-menu/edit?id=')|cat:$item.box_id}"><i class="icon-pencil"></i></a><a class="edit-menu" href="{$app->urlManager->createUrl('admin-menu/delete?id=')|cat:$item.box_id}"><i class="icon-trash"></i></a><div class="searchable">{$item.title}</div></div>
                        {if $item.box_type == 1}
                    <div class="dd-placeholder">
                        {call name=renderMenuTree items=$item.child level=$level+1}
                    </div>
                {/if}
            </li>
        {/foreach}
    </ul>
{/function}
<div class="menu" id="list">
    {call renderMenuTree items=$currentMenu level=0}
</div>
<div class="item-buttons"><span class="btn btn-primary btn-apply" onclick="return save();">{$smarty.const.TEXT_APPLY}</span><span class="btn btn-cancel" onclick="return backStatement();">{$smarty.const.IMAGE_CANCEL}</span></div>
<script>
function backStatement() {
    window.history.back();
    return false;
}
function calc(obj) {
    var text = [];
    $(obj).children('li').each(function( i, section ) {
        //text += $(this).attr('id') + ' : ';
        //text += calc($(this).children('.dd-placeholder').children('ul')) + ', ';
        
        var elem = { 'id' : $(this).attr('id'), 'children' : calc($(this).children('.dd-placeholder').children('ul')) };
        
        text.push(elem) ;
    });
    return text;
}
function save() {
    var post_data = JSON.stringify( calc($(".menu").children("ul")) );
    $.post("{Yii::$app->urlManager->createUrl('admin-menu/save')}", { "post_data" : post_data } , function(data){
        window.location.reload();
    },'json');
    return false;
}
    $(document).ready(function () {
        $(".sortable").sortable({
            connectWith: ".sortable",
            revert: true
        });

        $('.item-close').on('click', function(){
              if ($(this).hasClass('closed')){
                $(this).removeClass('closed');
                $(this).parent('div').parent('li').children('.dd-placeholder').show()
              } else {
                $(this).addClass('closed');
                $(this).parent('div').parent('li').children('.dd-placeholder').hide()
              }
            
        })
//        $(document).ready(function() {
            $('.edit-menu').popUp({
                'box_class':'editMenu'
            });
//        })
        $('.create_item_popup').popUp();
        
        $('.btn-import').each(function() {
            $(this).dropzone({
              url: '{Yii::$app->urlManager->createUrl('admin-menu/import-menu')}',
              success: function(){
                location.reload();
              }
            })
        });
        
    });
</script>