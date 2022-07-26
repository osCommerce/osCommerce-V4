{use class="yii\helpers\Html"}

{Html::hiddenInput('provider', $owner)}
<label>{$smarty.const.TEXT_SERVICE_ACCOUNT_CREDETIALS}</label>
<div class="analytics-file-upload{$platformId}">
    <span class="btn btn-upload{$platformId}">{$smarty.const.IMAGE_UPLOAD}</span>
    {Html::fileInput($owner|cat:"[jsonFile]", '', ['class' => 'analytics-file-upload'|cat:$platformId, 'style' => "width: 0; height: 0; overflow: hidden"])}
    <span>
        {if $jsonFile }
            {basename($jsonFile)} <small style="color:green">(+)</small>
            {\yii\helpers\Html::hiddenInput($owner|cat:"[jsonFile]", basename($jsonFile), ['class' => 'form-control'])}
        {/if}
    </span>
</div>
<label>{$smarty.const.TEXT_ANALYTICS_VIEW_ID}</label>
{Html::textInput($owner|cat:"[viewId]", $viewId, ['class' => 'form-control'])}
<p>
    <br/>
    {$description}
</p>
<script>
    $(document).ready(function () {
        var form = document.createElement('form');
        form.setAttribute('enctype', 'multipart/form-data');
        form.setAttribute('method', 'post');
        form.setAttribute('id', 'post-from{$platformId}');
        document.body.appendChild(form);
        $('.btn-upload{$platformId}').on('click', function () {
            $.each(form.children, function (i, e) {
                form.removeChild(e);
            });
            $(this).next('.analytics-file-upload{$platformId}').trigger('click');
        });
        if ($('#post-from{$platformId}').is('form')) {
            $('#post-from{$platformId}').fileupload();
            $('.analytics-file-upload{$platformId}').on('change', function () {
                var that = this;
                var filesList = $(this).prop('files');
                $('#post-from{$platformId}').fileupload('send', { files: filesList, url: 'upload/index', paramName: 'file' })
                        .success(function (result, textStatus, jqXHR) {
                            $(that).next().html(filesList[0].name + '<input type="hidden" name="' + $(that).attr('name') + '" value="' + filesList[0].name + '">');
                        });
            });
        }
    })
</script>