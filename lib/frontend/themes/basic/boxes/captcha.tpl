{if $version eq 'v2'}
<script src="https://www.google.com/recaptcha/api.js?hl={$code}" async defer></script>
<div class="g-recaptcha" data-sitekey="{$public_key}"></div>
<br/>
{elseif $version eq 'v3'}
<script src="https://www.google.com/recaptcha/api.js?render={$public_key}"></script>
<script class="v3score-{$uniqueId}">
grecaptcha.ready(function() {
    grecaptcha.execute("{$public_key}", { action: 'ecommerce' }).then(function(token) {
        {if \common\helpers\System::isBackend()}${else}tl{/if}(function(){
            var form = $('.v3score-{$uniqueId}').closest('form');
            if (form){
                var input = document.createElement('input');
                input.setAttribute('type', 'hidden');
                input.setAttribute('name', 'g-recaptcha-response');
                input.setAttribute('value', token);
                $(form).append(input);
            }
        })
    });
});
</script>
{/if}