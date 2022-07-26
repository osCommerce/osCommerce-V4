<!DOCTYPE html>
{use class="backend\design\Data"}
<html>
<head>
    <link href="{$app->view->theme->baseUrl}/css/designer.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{$app->view->theme->baseUrl}/css/fontawesome/font-awesome.min.css">
</head>
<body>
    <div id="root"></div>
    <div id="modal-root"></div>
    <script>
        var entryData = JSON.parse('{Data::getJsonData()}');
    </script>
    <script type="text/javascript" src="{$app->view->theme->baseUrl}/js/designer.js"></script>
</body>
</html>