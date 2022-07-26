
<div class="setting-row">
    <label for="">{$smarty.const.TEXT_PAGE_FORMAT}</label>
    <select name="added_page_settings[sheet_format]" id="" class="form-control">
        <option value=""></option>
        <option value="size"{if $added_page_settings.sheet_format == 'size'} selected{/if}>{$smarty.const.TEXT_ENTER_CUSTOM_SIZE}</option>
        <option value="A6"{if $added_page_settings.sheet_format == 'A6'} selected{/if}>A6</option>
        <option value="A5"{if $added_page_settings.sheet_format == 'A5'} selected{/if}>A5</option>
        <option value="A4"{if $added_page_settings.sheet_format == 'A4'} selected{/if}>A4</option>
        <option value="A3"{if $added_page_settings.sheet_format == 'A3'} selected{/if}>A3</option>
        <option value="A2"{if $added_page_settings.sheet_format == 'A2'} selected{/if}>A2</option>
        <option value="A1"{if $added_page_settings.sheet_format == 'A1'} selected{/if}>A1</option>
        <option value="A0"{if $added_page_settings.sheet_format == 'A0'} selected{/if}>A0</option>
    </select>
</div>

<div class="setting-row orientation">
    <label for="">{$smarty.const.TEXT_ORIENTATION}</label>
    <select name="added_page_settings[orientation]" id="" class="form-control">
        <option value=""></option>
        <option value="P"{if $added_page_settings.orientation == 'P'} selected{/if}>{$smarty.const.TEXT_PORTRAIT}</option>
        <option value="L"{if $added_page_settings.orientation == 'L'} selected{/if}>{$smarty.const.TEXT_LANDSCAPE}</option>
    </select>
</div>

<div class="setting-row page_width">
    <label for="">{$smarty.const.TEXT_PAGE_WIDTH}</label>
    <input type="text" name="added_page_settings[page_width]" value="{$added_page_settings.page_width}" class="form-control"/> mm
</div>

<div class="setting-row page_height">
    <label for="">{$smarty.const.TEXT_PAGE_HEIGHT}</label>
    <input type="text" name="added_page_settings[page_height]" value="{$added_page_settings.page_height}" class="form-control"/> mm
</div>

<div class="setting-row">
    <label for="">{$smarty.const.TEXT_MARGIN_TOP}</label>
    <input type="text" name="added_page_settings[pdf_margin_top]" value="{$added_page_settings.pdf_margin_top}" class="form-control"/> mm
</div>

<div class="setting-row">
    <label for="">{$smarty.const.TEXT_MARGIN_LEFT}</label>
    <input type="text" name="added_page_settings[pdf_margin_left]" value="{$added_page_settings.pdf_margin_left}" class="form-control"/> mm
</div>

<div class="setting-row">
    <label for="">{$smarty.const.TEXT_MARGIN_RIGHT}</label>
    <input type="text" name="added_page_settings[pdf_margin_right]" value="{$added_page_settings.pdf_margin_right}" class="form-control"/> mm
</div>

<div class="setting-row">
    <label for="">{$smarty.const.TEXT_MARGIN_BOTTOM}</label>
    <input type="text" name="added_page_settings[pdf_margin_bottom]" value="{$added_page_settings.pdf_margin_bottom}" class="form-control"/> mm
</div>

<div class="setting-row">
    <label for="">Font family</label>
    <div style="overflow: hidden">
        <div style="margin-bottom: 5px">
            <select name="added_page_settings[pdf_font_family]" id="" class="form-control font-default">
                <option value=""></option>
                <option value="Varela Round"{if $added_page_settings['pdf_font_family'] == 'Varela Round'} selected{/if}>Varela Round</option>
                <option value="Hind"{if $added_page_settings['pdf_font_family'] == 'Hind'} selected{/if}>Hind</option>
                <option value="Tahoma"{if $added_page_settings['pdf_font_family'] == 'Tahoma'} selected{/if}>Tahoma</option>
                <option value="Helvetica"{if $added_page_settings['pdf_font_family'] == 'Helvetica'} selected{/if}>Helvetica</option>
                <option value="Times"{if $added_page_settings['pdf_font_family'] == 'Times'} selected{/if}>Times</option>
                <option value="Courier"{if $added_page_settings['pdf_font_family'] == 'Courier'} selected{/if}>Courier</option>
                <option value="Verdana"{if $added_page_settings['pdf_font_family'] == 'Verdana'} selected{/if}>Verdana</option>
                {if !in_array($added_page_settings['pdf_font_family'], ['', 'Varela Round', 'Hind', 'Tahoma', 'Helvetica', 'Times', 'Courier', 'Verdana'])}
                    <option value="{$added_page_settings['pdf_font_family']}" selected>
                        {$added_page_settings['pdf_font_family']}
                    </option>
                {/if}
            </select>
        </div>
        <div>
            <input type="text" name="new_pdf_font_family" class="form-control font-advanced"/> <span class="btn">Add</span>
        </div>
    </div>
</div>

<script>
    $(function(){
        var $sheetFormat = $('select[name="added_page_settings[sheet_format]"]')

        $('.font-advanced + .btn').on('click', function(){
            var newFont = $('.font-advanced').val()
            $('.font-default').append('<option value="'+newFont+'">'+newFont+'</option>').val(newFont)

        })

        $sheetFormat.on('change', chooseFormat);
        chooseFormat();

        function chooseFormat(){
            if ($sheetFormat.val() === 'size'){
                $('.page_width').show();
                $('.page_height').show();
                $('.orientation').hide();
            } else if (!$sheetFormat.val()){
                $('.page_width').hide();
                $('.page_height').hide();
                $('.orientation').hide();
            } else {
                $('.page_width').hide();
                $('.page_height').hide();
                $('.orientation').show();
            }
        }
    })
</script>