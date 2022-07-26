<iframe src="{$svgEditorUrl}" frameborder="0" class="svg-editor-iframe" id="svg_editor_frame" width="100%"></iframe>

<link href="{$app->view->theme->baseUrl}/css/banner-editor.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{$app->view->theme->baseUrl}/js/banner-editor.js"></script>
<script>
    bannerEditor.init({
        banners_id: '{$banners_id}',
        language_id: '{$language_id}',
        banner_group: '{$banner_group}',
        tr: JSON.parse('{$tr}'),
    })
</script>