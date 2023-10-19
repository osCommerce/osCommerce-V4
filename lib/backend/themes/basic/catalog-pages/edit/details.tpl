<div class="row">
    <div class="col-md-12">
        <div class="widget box">
            <div class="widget-header">
                <h4>{$smarty.const.TEXT_IMAGE_}</h4>
            </div>
            <div class="widget-content">
                <div class="about-image">
                </div>
                {assign var="img" value=""}
                {if $catalogPageForm->catalogPageForm->image != ''}
                    {$img = $dirImages|cat:$catalogPageForm->catalogPageForm->image}
                {/if}
                {\backend\design\Image::widget([
                'name' => 'CatalogsPageForm[imageGallery]',
                'value' => {$img},
                'upload' => 'CatalogsPageForm[image]',
                'delete' => 'CatalogsPageForm[image_delete]'
                ])}
            </div>
            <div class="divider"></div>
        </div>
    </div>
</div>
<div class="">
    <div class="md_row after">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td>
                    {$form->field($catalogPageForm->catalogPageForm,  'created_at')->hiddenInput(['id' => 'created_at'])->label(false)}
                    {$form->field($catalogPageForm->catalogPageForm,  'created_at_view')->textInput(['class' => 'datepicker'])}
                </td>
            </tr>
            <tr>
                <td >
                    <label for="status">{$smarty.const.TABLE_HEADING_STATUS}</label>
                    <div class="md_value"><input type="checkbox" value="1" name="CatalogsPageForm[status]" class="check_on_off"{if $catalogPageForm->catalogPageForm->status== 1} checked="checked"{/if}></div>
                </td>
            </tr>
        </table>
    </div>
</div>
<script>
    $( ".datepicker" ).datepicker({
        changeMonth: true,
        changeYear: true,
        showOtherMonths:true,
        autoSize: false,
        dateFormat: 'd MM yy',
        altFormat: "yy-mm-dd",
        altField: "#created_at"
    });
</script>