{use class="common\helpers\Html"}
{use class="yii\helpers\ArrayHelper"}
<div class="scroll-table-workaround" id="ep_datasource_config">

    <div class="widget box">
        <div class="widget-header">
            <h4>Access details</h4>
            <div class="toolbar no-padding">
                <div class="btn-group">
                    <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
                </div>
            </div>
        </div>
        <div class="widget-content">
            <div class="w-line-row w-line-row-1">
                <div class="wl-td">
                    <label>Base URL:</label> {Html::textInput('datasource['|cat:$code|cat:'][base_url]', $base_url, ['class' => 'form-control'])}
                </div>
            </div>
            <h4>Authorization <small>(see readme file in oscb directory for details)</small>:</h4>
            <div class="w-line-row w-line-row-1">
                <div class="wl-td">
                    <label>Secure key:</label> {Html::textInput('datasource['|cat:$code|cat:'][secure_key]', $secure_key, ['class' => 'form-control'])}
                </div>
            </div>
            <div class="w-line-row w-line-row-1">
                <div class="wl-td">
                    <label>Secure method:</label> {Html::dropDownList('datasource['|cat:$code|cat:'][secure_method]',$secure_method,['bearer'=>'Bearer Token (recommended)','post'=>'POST','get'=>'GET (non secure)'],['class' => 'form-control'])}
                </div>
            </div>
        </div>
    </div>

</div>