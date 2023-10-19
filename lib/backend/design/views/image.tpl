<div class="upload-box upload-box-wrap upload-box-{$id}"></div>
<script>
    $(function(){
        $('.upload-box-{$id}').fileManager(JSON.parse('{$data}'))
    });
</script>