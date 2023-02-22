import draggablePopup from 'src/draggablePopup';

export default function () {
    var this_block = $(this).closest('div[id]');
    var block_id = this_block.attr('id');
    var block_name = this_block.data('name');
    var block_type = '';
    this_block.closest('div[data-type]').each(function(){
        block_type = $(this).data('type');
    });
    let widgetData = entryData.widgetList.find(i => i.name == $(this).closest('div[data-name]').data('name'));
    let widgetTitle = 'Widget settings';
    if (widgetData && widgetData.title && typeof widgetData.title === 'string') {
        widgetTitle = widgetData.title;
    }

    const $popup = draggablePopup('<div class="preloader"></div>', {
        name: 'widgetSettings',
        top: 100,
        className: 'widget-settings',
        zooming:true,
        resizable: {
            edges: {
                top: false,
                left: false,
                right: true,
                bottom: true,
            },
        },
    });

    const $popupContent = $('.popup-content', $popup)
        .removeClass('popup-content')
        .addClass('popup-content-settings');

    $.get('design/box-edit', {id: block_id, name: block_name, block_type: block_type}, function(data){
        $popupContent.html(data);
        const $boxSave = $('#box-save');
        saveSettings($popup);
        $('.popup-buttons .btn-cancel', $popup).on('click', function(){
            $popup.trigger('close');
        });

        $('.widget-settings .popup-heading').text(widgetTitle);

        var showChanges = function(){
            var style = $('#style .style-tabs-content > .active');
            $('.changed', style).removeClass('changed');
            $('input:not([type="radio"]), select', style).each(function(){
                if (
                    ($(this).val() !== '' && $(this).attr('type') != 'checkbox') ||
                    ($(this).attr('type') == 'checkbox' && $(this).prop( 'checked' ))
                ) {
                    $(this).closest('.setting-row').find('label').addClass('changed');
                    $(this).closest('label').addClass('changed');
                    var id = $(this).closest('.tab-pane').attr('id');
                    $('.nav a[href="#'+id+'"]').addClass('changed');
                    id = $(this).closest('.tab-pane').parents('.tab-pane').attr('id');
                    $('.nav a[href="#'+id+'"]').addClass('changed');
                }
            });
        };
        showChanges();
        $boxSave.on('change', showChanges);

        if (entryData.designer_mode != 'expert') {
            $('a[href="#visibility"]').closest('li').hide();
        }
        if (!entryData.designer_mode) {
            $('a[href="#align"]').closest('li').hide();
        }

    });
}

function saveSettings($popup) {

    const $boxSave = $('#box-save');

    window.boxInputChanges = {};

    $boxSave.on('change', 'input, select, textarea', function(){
        if ($(this).attr('type') == 'checkbox' && !$(this).is(':checked')) {
            window.boxInputChanges[$(this).attr('name')] = '';
        } else if ($(this).attr('type') == 'checkbox' && $(this).is(':checked')) {
            window.boxInputChanges[$(this).attr('name')] = 1;
        }else {
            window.boxInputChanges[$(this).attr('name')] = $(this).val();
        }
    });

    $boxSave.on('submit', function(){

        window.boxInputChanges.id = $('input[name="id"]', this).val();
        var params = $('input[name="params"], select[name="params"]', this).val();
        if (params) {
            window.boxInputChanges.params = params;
        }

        var values = [];
        $.each( window.boxInputChanges, function(name, value) {
            values = values.concat({ 'name': name, 'value': value});
        });

        values = values.concat(
            $('.visibility input[disabled]', this).map(function() {
                return { 'name': this.name, 'value': 1};
            }).get()
        );

        $('.check_on_off').each(function(){
            values = values.concat({ 'name': $(this).attr('name'), 'value': $(this).prop( 'checked' )});
        });

        var data = values.reduce(function(obj, item) {
            obj[item.name] = item.value;
            return obj;
        }, {});

        $.post('design/box-save', {'values': JSON.stringify(data)}, function(){ });
        setTimeout(function(){
            $popup.trigger('close');
            $(window).trigger('reload-frame');
        }, 300);
        return false;
    });

}