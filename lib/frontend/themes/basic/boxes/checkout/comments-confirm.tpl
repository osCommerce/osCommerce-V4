{use class="frontend\design\Info"}

<div class="comments">
    {$comments}
</div>

{if $empty && !Info::isAdmin()}
    <script>
        tl(function(){
            {if $settings[0].hide_parents == 1}
            $('#box-{$id}').hide()
            {elseif $settings[0].hide_parents == 2}
            $('#box-{$id}').closest('.box-block').hide()
            {elseif $settings[0].hide_parents == 3}
            $('#box-{$id}').closest('.box-block').closest('.box-block').hide()
            {elseif $settings[0].hide_parents == 4}
            $('#box-{$id}').closest('.box-block').closest('.box-block').closest('.box-block').hide()
            {/if}
        })
    </script>
{/if}