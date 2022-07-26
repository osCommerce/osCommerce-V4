<script type="text/javascript">
    tl('{\frontend\design\Info::themeFile('/js/main.js')}', function(){
        var box = $('#box-{$id}');
        var id = {$id};
        var accordion = [{foreach $accordion as $size}'{$size}',{/foreach}];
        var active = localStorage.getItem(id);
        if (!active) {
            active = 'tab-block-'+id+'-1';
        }

        var applyAccordion = function() {
            box.addClass('accordion');
            $('> .accordion-heading', box).show();
            $('> .tab-navigation', box).hide();
        };
        var applyTabs = function() {
            box.removeClass('accordion');
            $('> .accordion-heading', box).hide();
            $('> .tab-navigation', box).show();
            $('.'+active+', .'+active+' a', box).addClass('active');
            $('#'+active).showTab()
        };

        $.each(accordion, function(key, val){
            if ($.inArray(val, tlSize.current ) === -1) {
                applyTabs()
            } else {
                applyAccordion()
            }
            $(window).on(val+'in', applyAccordion);
            $(window).on(val+'out', applyTabs)
        });

        $('> .block', box).hideTab();
        $('.'+active+', .'+active+' a', box).addClass('active');
        $('#'+active).showTab();


        $('> .tab-navigation .tab-li', box).on('click', function(){
            active = $(this).data('tab');

            $('> .tab-navigation .active', box).removeClass('active');
            $('> .accordion-heading.active', box).removeClass('active');
            $('.'+active+', .'+active+' a', box).addClass('active');

            $('> .block', box).hideTab();
            $('#'+active).showTab();

            localStorage.setItem(id, active);
        });
        $('> .tab-navigation .tab-a', box).on('click', function(d){
            d.preventDefault()
        });

        box.on('click', '> .accordion-heading:not(.active)', function(){
            active = $(this).data('tab');

            $('> .tab-navigation .active', box).removeClass('active');
            $('> .accordion-heading.active', box).removeClass('active');
            $('.'+active+', .'+active+' a', box).addClass('active');

            $('> .block:not(#'+active+')', box).slideUp();
            $('#'+active).showTab().hide().slideDown();

            localStorage.setItem(id, active);
        });

        box.on('click', '> .accordion-heading.active', function(){
            active = $(this).data('tab');

            $('> .tab-navigation .active', box).removeClass('active');
            $('> .accordion-heading.active', box).removeClass('active');

            $('#'+active, box).slideUp();
        });

        box.on('tabHide', function(){
            $('> .block', this).hideTab();
            $('#'+active, this).showTab();
        })
    });

</script>