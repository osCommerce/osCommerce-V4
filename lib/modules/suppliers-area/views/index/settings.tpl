{\suppliersarea\assets\AppAsset::register($this)|void}
<div >
    {$messages}
    {$content}
</div>
<script>
    $(document).ready(function(){
        $('body').on('click','.js-qdt-remove-row',function(event){
            $(this).parents('tr').remove();
        });
    })
</script>