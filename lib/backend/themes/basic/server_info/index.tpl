<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->


<!--===banners list===-->
<div class="order-wrap server-info">
<div class="row">
    <div class="col-md-12">
        <div class="widget-content">
		<table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable">
              <tr>
                <td class="smallText"><b>{TITLE_SERVER_HOST}</b></td>
                <td class="smallText">{$system['host']}({$system['ip']})</td>
                <td class="smallText">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>{TITLE_DATABASE_HOST}</b></td>
                <td class="smallText">{$system['db_server']}({$system['db_ip']})</td>
              </tr>
              <tr>
                <td class="smallText"><b>{TITLE_SERVER_OS}</b></td>
                <td class="smallText">{$system['system']} {$system['kernel']}</td>
                <td class="smallText">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>{TITLE_DATABASE}</b></td>
                <td class="smallText">{$system['db_version']}</td>
              </tr>
              <tr>
                <td class="smallText"><b>{TITLE_SERVER_DATE}</b></td>
                <td class="smallText">{$system['date']}</td>
                <td class="smallText">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>{TITLE_DATABASE_DATE}</b></td>
                <td class="smallText">{$system['db_date']}</td>
              </tr>
              <tr>
                <td class="smallText"><b>{TITLE_SERVER_UP_TIME}</b></td>
                <td colspan="3" class="smallText">{$system['uptime']}</td>
              </tr>
              <tr>
                <td colspan="4"></td>
              </tr>
              <tr>
                <td class="smallText"><b>{TITLE_HTTP_SERVER}</b></td>
                <td colspan="3" class="smallText">{$system['http_server']}</td>
              </tr>
              <tr>
                <td class="smallText"><b>{TITLE_PHP_VERSION}</b></td>
                <td colspan="3" class="smallText">{$system['php']} ({TITLE_ZEND_VERSION} {$system['zend']})</td>
              </tr>
            </table>
        </div>
    </div>
</div>
{$reg}
</div>
