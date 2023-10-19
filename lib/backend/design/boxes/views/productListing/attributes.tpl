{use class="Yii"}
{use class="yii\base\Widget"}

<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
    <input type="hidden" name="id" value="{$id}"/>
    <div class="popup-heading">
        {$smarty.const.TABLE_HEADING_PRODUCTS_ATTRIBUTES}
    </div>
    <div class="popup-content">


        <div class="tabbable tabbable-custom">
            <ul class="nav nav-tabs">

                <li class="active" data-bs-toggle="tab" data-bs-target="#type"><a>{$smarty.const.TABLE_HEADING_PRODUCTS_ATTRIBUTES}</a></li>
                <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
                <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

            </ul>
            <div class="tab-content">

                <div class="tab-pane active" id="type">

                    <label for="">{$smarty.const.TEXT_PRICE_OPTION}</label>
					<select name="setting[0][price_option]" id="" class="form-control">
						<option value="0"{if $settings[0].price_option == '0'} selected{/if}>{$smarty.const.TEXT_PRICE_DIFFERENCE}</option>
						<option value="1"{if $settings[0].price_option == '1'} selected{/if}>{$smarty.const.TEXT_FULL_PRICE}</option>
						<option value="2"{if $settings[0].price_option == '2'} selected{/if}>{$smarty.const.TEXT_DONT_SHOW_PRICE}</option>
					</select>

                </div>
                <div class="tab-pane" id="style">
                    {$block_view = 1}
                    {include '../include/style.tpl'}

                </div>
                <div class="tab-pane" id="visibility">
                    {include '../include/visibility.tpl'}
                </div>

            </div>
        </div>


    </div>
    <div class="popup-buttons">
        <button type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</button>
        <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
    </div>
</form>