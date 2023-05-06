{use class="yii\helpers\Html"}
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<!--=== Page Content ===-->

{assign var="connectionSuccess" value=$app->controller->view->connectionSuccess}
<div class="tabbable tabbable-custom tabbable-ep">
    <ul class="nav nav-tabs">
        <li id="tab_connection" class="{if $tab=='tab_connection'}active{/if}"><a><span>{$smarty.const.EXTENSION_OSCLINK_TAB_CONNECTION}</span></a></li>
        <li id="tab_mapping" class="{if $tab=='tab_mapping'}active{/if} depending" {if !$connectionSuccess}style="display: none;"{/if}><a><span>{$smarty.const.EXTENSION_OSCLINK_TAB_MAPPING}</span></a></li>
        <li id="tab_actions" class="{if $tab=='tab_actions'}active{/if} depending" {if !$connectionSuccess}style="display: none;"{/if}><a><span>{$smarty.const.EXTENSION_OSCLINK_TAB_ACTIONS}</span></a></li>
        <li id="tab_cleaning" class="{if $tab=='tab_cleaning'}active{/if} " {if !$app->controller->view->isMappedExist}style="display: none;"{/if}><a><span>{$smarty.const.EXTENSION_OSCLINK_TAB_CLEANING}</span></a></li>
    </ul>
    <div class="tab-content tab-content1">

        <div class="tab-pane topTabPane tabbable-custom active">
            <div id="page_connection" {if $tab!='tab_connection'}style="display: none;"{/if}>
                {include "./tab_settings.tpl" type="connection"}
            </div>
            <div id="page_mapping"  {if $tab!='tab_mapping'}style="display: none;"{/if}>
                {include "./tab_settings.tpl" type="mapping"}
            </div>
            <div id="page_actions"  {if $tab!='tab_actions'}style="display: none;"{/if}>
                {include "./tab_actions.tpl"}
            </div>
            <div id="page_cleaning" {if $tab!='tab_cleaning'}style="display: none;"{/if}>
                {include "./tab_cleaning.tpl"}
            </div>

        </div>

    </div>
</div>

<div>
    <div class="row">
        <div class="col-md-12">
            <div id="ButtonControlHolder" style="display: none;">
                <center><a class="btn btn-primary" onclick="return doProgressCancel();">{$smarty.const.EXTENSION_OSCLINK_TEXT_CANCEL_BUTTON}</a></center>
            </div>
            <div class="widget-content">
                <div id="ProgressText" style="float: left; width: 100%; text-align: center; font-weight: bold;"></div>
                <div id="ProgressBar" style="min-height: 24px; width: 0%; background-color: lightblue; text-align: center;"></div>
                <div id="ProgressMessage" style="float: left; width: 100%;"></div>
                <iframe id="ProgressFrame" name="ProgressFrame" style="width:0; height:0; border:0px solid #fff; display: none;"></iframe>
                <form name="ProgressForm" id="ProgressForm" enctype="multipart/form-data" action="{Yii::$app->urlManager->createUrl('extensions?module=OscLink&action=adminActionExecute')}" method="POST" target="ProgressFrame" style="display: none;"></form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
var connectionSuccessAtStart = {if $app->controller->view->connectionSuccess}true{else}false{/if};
var connectionFailedAtSave = false;
$(document).ready(function() {
    $('ul.nav-tabs').find('li').unbind('click').bind('click', function() { tabClick($(this)) });

    $('input[name="CleanFully"]').unbind('click').bind('click', function() { cleanRadioClick($(this)) });

    $('#ActionsTable,#ActionAllTable').find('a.btn-execute').unbind('click').bind('click', function() {
        if (confirm('{$smarty.const.EXTENSION_OSCLINK_TEXT_EXECUTE_CONFIRM|replace:'\'':'\\\''}')) {
            progressSubmit($(this).parent('td')[0], "{Yii::$app->urlManager->createUrl('extensions?module=OscLink&action=adminActionExecute')}" );
            doProgressUpdate({ 'progress': 0, 'text': 'preparing... (it may take a few minutes if your webserver caches the data sending)' });
            return true;
        }
        return false;
    });
    $('#CleanImportedData').find('a.btn-execute').unbind('click').bind('click', function() {
        confirm_text = $('input[name="CleanFully"][value="fully"]').is(':checked') ?
            '{$smarty.const.EXTENSION_OSCLINK_TEXT_CLEAN_CONFIRM_ALL|replace:'\'':'\\\''}' :
            '{$smarty.const.EXTENSION_OSCLINK_TEXT_CLEAN_CONFIRM|replace:'\'':'\\\''}'
        if (confirm(confirm_text)) {
            progressSubmit('#CleanImportedData', "{Yii::$app->urlManager->createUrl('extensions?module=OscLink&action=adminActionClean')}" );
            return true;
        }
        return false;
    });
});
function saveConfiguration(type) {
    progressClear();
    if (type=='connection') {
        progressSetText('{$smarty.const.EXTENSION_OSCLINK_MSG_CONNECT_START}');
    }
    $.post("{Yii::$app->urlManager->createUrl('extensions?module=OscLink&action=adminActionSave')}",
        $('#'+type+'_ConfigurationTable').find('input, select, textarea').serialize(),
        function (response, status) {
            if (response == 'success') {
                if (type == 'connection') {
                    progressSetText('{$smarty.const.EXTENSION_OSCLINK_MSG_CONNECT_SUCCESS}');
                    tabsVisible(true);
                } else {
                    progressSetText('Mapping settings saved');
                }
            } else {
                connectionFailedAtSave = true;
                progressSetText(response);
                tabsVisible(false);
            }
        }
        , 'html')
        .fail(function(jqXHR){
            connectionFailedAtSave = true;
            progressSetText('Server error:'+jqXHR.status);
            tabsVisible(false);
        });
}
function doProgressUpdate(parameterArray) {
    $('table.table-execute').find('a.btn-execute').attr('disabled', 'disabled');
    if (typeof(parameterArray['text']) != 'undefined') {
        $('#ProgressText').text(parameterArray['text']);
    }
    if (typeof(parameterArray['progress']) != 'undefined') {
        $('#ProgressBar').css('width', (parameterArray['progress'] + '%'));
        if (parameterArray['progress'] >= 100) {
            $('table.table-execute').find('a.btn-execute').removeAttr('disabled');
        }
    }
    if (typeof(parameterArray['message']) != 'undefined') {
        $('#ProgressMessage').html(
            (($('#ProgressMessage').html() == '')
                ? '' : ($('#ProgressMessage').html() + '<br />')
            ) + parameterArray['message']
        );
    }
    if (typeof(parameterArray['reset']) != 'undefined') {
        if (parameterArray['reset'] > 0) {
            $('table.table-execute').find('a.btn-execute').removeAttr('disabled');
            $('#ButtonControlHolder').hide();
            tabsUpdateStates();
        }
    }
    return true;
}
function doProgressCancel() {
    if (confirm('{$smarty.const.EXTENSION_OSCLINK_TEXT_CANCEL_CONFIRM|replace:'\'':'\\\''}')) {
        $('#ButtonControlHolder').hide();
        $.get("{Yii::$app->urlManager->createUrl('extensions?module=OscLink&action=adminActionCancel')}");
        return true;
    }
    return false;
}
function pageFromTab(tab) {
    return $(tab.attr('id').replace("tab_", "#page_"));
}
function tabClick(clicked) {
    if (!clicked.hasClass('active')) {
        tab = clicked.attr('id');
        if (!connectionSuccessAtStart || connectionFailedAtSave) {
            window.location.replace( "{Yii::$app->urlManager->createUrl('extensions?module=OscLink&tab=')}"+tab);
            return false;
        }

        active_tab = $('ul.nav-tabs').find('li.active');
        active_tab.removeClass('active');
        pageFromTab(active_tab).hide();

        clicked.addClass('active');
        pageFromTab(clicked).show();
    }
    return false;
}
function tabsVisible(visible) {
    $('ul.nav-tabs').find('li.depending').toggle(visible);
}
function tabsUpdateStates() {
    $.ajax({
        url: "{\Yii::$app->urlManager->createUrl(['extensions/index', 'module' => 'OscLink', 'action' => 'adminActionTabStates'])}",
        type:'GET',
        success: function(states){
            tab_cleaning = $('ul.nav-tabs').find('#tab_cleaning');
            if (!states.cleaning && tab_cleaning.hasClass('active')) {
                tabClick($('ul.nav-tabs').find('#tab_actions'));
            }
            tab_cleaning.toggle(states.cleaning);
        }
    });
}
function progressSetText(msg) {
    $('#ProgressText').html(msg);
}
function progressClear() {
    progressSetText('');
    $('#ProgressText').text('');
    $('#ProgressBar').css('width', '0%');
    $('#ProgressMessage').html('');
}
function progressSubmit(copyFieldsFrom, url) {
    progressClear();
    $('#ProgressForm').find('input, select, textarea').remove();
    $(copyFieldsFrom).find('input, select, textarea').clone().appendTo('#ProgressForm');
    $('html, body').animate({
        scrollTop: ($('#ProgressBar').offset().top - ($('#ProgressBar').height() * 6))
    }, 250);
    $('#ProgressForm').attr('action', url).submit();
//    $('#ButtonControlHolder').show();
}
function doStatusMapNew() {
    let element = $('#StatusMapEtalon').clone().removeAttr('id');
    element.find('[name="api_status_map_tstatus"]').attr('name', ('api_status_map[tstatus][' + statusMapCount + ']'));
    element.find('[name="api_status_map_mstatus"]').attr('name', ('api_status_map[mstatus][' + statusMapCount + ']'));
    statusMapCount ++;
    element.insertBefore($('#StatusMapEtalon')).show();
    return true;
}
function doStatusMapDelete(element) {
    $(element).closest('tr').remove();
    return true;
}
function doTaxMapNew() {
    let element = $('#TaxMapEtalon').clone().removeAttr('id');
    element.find('[name="api_tax_map_ttax"]').attr('name', ('api_tax_map[ttax][' + taxMapCount + ']'));
    element.find('[name="api_tax_map_mtax"]').attr('name', ('api_tax_map[mtax][' + taxMapCount + ']'));
    taxMapCount ++;
    element.insertBefore($('#TaxMapEtalon')).show();
    return true;
}
function doTaxMapDelete(element) {
    $(element).closest('tr').remove();
    return true;
}
function cleanRadioClick(clicked) {
    isSelectedRadio = clicked.attr("value") == 'selected';
    $('#CleanSelectedItems').toggle(isSelectedRadio);
}
function viewLog(log) {
    $.ajax({
        url: "{\Yii::$app->urlManager->createUrl(['extensions/index', 'module' => 'OscLink', 'action' => 'adminActionShowLog', 'log' => '111'])}".replace('111', log),
        type:'GET',
        success: function(m){
          alert(m);
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            alert("Status: " + textStatus + "\r\nError: " + errorThrown);
        }
    });
    return false;
}
</script>
<!-- /Page Content -->