{$this->beginPage()}<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>{$this->title}</title>
    {$this->head()}

    {use class="frontend\design\Info"}
        <!--link href="{$app->request->baseUrl}/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="{$app->view->theme->baseUrl}/css/main.css" rel="stylesheet" type="text/css" /-->
    {Info::getCss()}

</head>
<body class="{if Info::isAdmin()} is-admin{/if}">
{$this->beginBody()}
        {$content}
        <link rel="stylesheet" href="{Info::themeFile('/css/style.css')}"/>
{$this->endBody()}
</body>
</html>
{$this->endPage()}