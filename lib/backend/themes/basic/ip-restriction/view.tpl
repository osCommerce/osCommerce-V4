    {if $forbidden}
    <div class="or_box_head">{$forbidden->forbidden_address}</div>
	<div class="btn-toolbar btn-toolbar-order">
        <a href="javascvript:void(0)" onclick="ipEdit({$forbidden->forbidden_id})" class="btn btn-no-margin btn-primary btn-edit">{$smarty.const.IMAGE_EDIT}</a>
        <button onclick="return confirmDelete({$forbidden->forbidden_id})" class="btn btn-delete btn-no-margin">{$smarty.const.IMAGE_DELETE}</button>
    </div>
    {/if}
<script>
$(document).ready(function(){
 
    $('.btn-edit').click(function(e){
        var href = $(this).attr('href');
        $.get(href, '',
            function(data, status){
                if (status="success"){
                    
                }
            }, "html");
         return false;
    });
});
</script>    