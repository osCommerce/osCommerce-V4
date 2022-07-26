{use class="frontend\design\Info"}
<div id="vc_{$block_id}" class="visitor_country_selector">
    <div>
        <div class="current">
            <span><a href="#country_selector-body{$block_id}" class="visitor-country-link">{*<img src="themes/basic/icons/flags/{$selected_variant.iso2}.svg" />*}{$selected_variant['text']}</a></span>
        </div>

        <div id="country_selector-body{$block_id}" class="country_selector-body{if $auto_open_popup} country_selector-body__opened{/if} artwork artworkPopup" style="display:none">
            <div class="visitor-country-left art-left"><img src="themes/theme-1/img/artwork.webp" alt=""></div>
            <div class="visitor-country-right art-right">
                <div class="country_selector-heading artwork-title">{sprintf($smarty.const.TEXT_POPUP_COUNTRY_SELECTION, $selected_variant['text'])}</div>
                {*
                <div class="select">
                {foreach $countries_variants as $country}
                <a href="javascript:void(0);" data-value="{$country.id}" class="country_{$country.iso2} {if $country.selected} country_select-selected{/if}"><img data-value="{$country.id}" src="themes/basic/icons/flags/{$country.iso2}.svg" />{$country.text}</a>
                {/foreach}
                </div>
                *}
                {if $smarty.const.TEXT_COUNTRY_SELECTOR_NOTE}
                    <div class="country_selector-note artwork-desc">{sprintf($smarty.const.TEXT_COUNTRY_SELECTOR_NOTE, $selected_variant['text'])}</div>
                {/if}
                <div class="select location">
                    <select name="selected_country_id">
                        <option value="-1">{$smarty.const.PULL_DOWN_DEFAULT}</option>
                        {foreach $countries_variants as $country}
                            <option value="{strip}
                                    {if isset($country.link) }
                                        {$country.link}
                                    {else}
                                        {$country.id}
                                    {/if}
                                    {/strip}" class="country_{$country.iso2} {*if $country.selected}selected{/if*}">{$country.text}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
    </div>

</div>
<script type="text/javascript">
    tl([
        '{Info::themeFile('/js/main.js')}'
    ], function () {
        {*  div a
        $('#vc_{$block_id} .select a').on('click', function (event) {
            var $link = $(event.target);
            var country_id = $link.attr('data-value');
            $.post('{$country_selector_store_url}', {selected_country_id: country_id}, function (data) {
                var set_text = $link.html();
                if (set_text.indexOf('(') > 2)
                    set_text = set_text.replace(/\([^(]+\)/, '');
                $('.visitor_country_selector').find('.current span').html(set_text);
                $('.country_selector-body.country_selector-body__opened').removeClass('country_selector-body__opened');
                if (data.relocate && data.relocate.length > 0) {
                    window.location.href = data.relocate;
                } else {
                    document.location.reload();
                }
            });
        });
        *}
        {*select*}

        $('.visitor-country-link').popUp({
            'box_class':'visitor-country-link artworkPopup',
            'close': function(){
                //window.location.href = '{$country_selector_store_url}'+'?'+'selected_country_id={$selected_variant["id"]}';
                /*
                var csrfParam = $('meta[name="csrf-param"]').attr("content");
                var csrfToken = $('meta[name="csrf-token"]').attr("content");
                var params = {
                        'selected_country_id': '{$selected_variant["id"]}'
                    };
                    params[csrfParam] = csrfToken;
                $.post('{$country_selector_store_url}', params, function (data) {
                });*/
            },
            'opened': function(){
              $('.select select[name=selected_country_id]').on('change', function(e){
                var csrfParam = $('meta[name="csrf-param"]').attr("content");
                var csrfToken = $('meta[name="csrf-token"]').attr("content");
                var country_id = $(this).val();
                if (country_id.match(/^\d+$/)) {
                    var params = {
                        'selected_country_id': country_id
                    };
                    params[csrfParam] = csrfToken;
                    $.post('{$country_selector_store_url}', params, function (data) {
                        var sel = $('.pop-up-content .select select[name=selected_country_id] option:selected').text();
                        $('.visitor-country-link').text(sel);
                        $('.country_selector-body.country_selector-body__opened').removeClass('country_selector-body__opened');
                        return ($('.popup-box:last').trigger('popup.close'), $('.popup-box-wrap').remove());

                    });

                } else {
                    window.location.href = country_id;
                }

				return false;
			  })
            },
		});

{if $auto_open_popup}
		setTimeout(function(){
			$('.visitor-country-link:first').click();
		},2000);
{/if}

    })
</script>

