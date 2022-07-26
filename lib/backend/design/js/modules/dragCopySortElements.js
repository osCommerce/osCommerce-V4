export default function(settings){

    let options = $.extend({
        sourceArea: '.suggest-products',
        sourceItems: '.product-item',
        sourceHandle: '',
        destinationArea: '.image-content',
        destinationItems: '> .product-item',
        destinationHandle: '',
        receive: function(event, ui){},
    }, settings);


    let copyHelper;
    let destinationArea = $(options.destinationArea);
    let sourceArea = $(options.sourceArea);

    destinationArea.sortable({
        connectWith: destinationArea,
        items: options.destinationItems,
        cursor: 'move',
        handle: options.destinationHandle,
        update: function( event, ui ) {
            $('.original-placeholder').remove();
        },
        revert: true,
        tolerance: "pointer",
        scroll: false,
        sort: function(){
        },
        start: function (e, ui) {
            let clone = ui.item.clone();
            clone.addClass('original-placeholder')
            ui.item.parent().append(clone);
        },
        stop: function() {
            $('.original-placeholder').remove();
            $('body').trigger('changedEmail');
        },
        receive: function(e,ui) {
            copyHelper= null;

            options.receive(e,ui)
        }
    });

    sourceArea.sortable({
        handle: options.sourceHandle,
        connectWith: destinationArea,
        items: options.sourceItems,
        forcePlaceholderSize: false,
        helper: function(e,li) {
            copyHelper = li.clone().insertAfter(li);
            return li.clone();
        },
        stop: function() {
            copyHelper && copyHelper.remove();
        },
        update: function( event, ui){
            if (ui.item.parent().hasClass('box-group')){
                return false;
            }
        }
    });
}