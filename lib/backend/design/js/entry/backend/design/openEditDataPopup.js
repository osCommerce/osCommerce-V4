import draggablePopup from "src/draggablePopup";
import store from "./store";
import popupSettings from "src/popupSettings";

export default function(framePrams, onLoadFrameScript, popUpSettings = {}){

    let height = $(window).height() - 100;
    let popupSettingsVal = popupSettings('translate');
    if (popupSettingsVal && popupSettingsVal.height) {
        height = popupSettingsVal.height;
    }

    let defaultFramePframs = {
        width: '100%',
        height: height - 50,
        class: 'edit-data-popup',
    };

    let $iframe = $('<iframe>', {...defaultFramePframs, ...framePrams});

    let popup = draggablePopup($iframe, {
        //draggable: false,
        top: 20,
        name: 'translate',
        className: 'translate-popup',
        height,
        resizable: {
            edges: {
                top: false,
                left: false,
                right: true,
                bottom: true,
            },
            listeners: {
                move (event) {
                    Object.assign(event.target.style, {
                        width: `${event.rect.width}px`,
                        height: `${event.rect.height}px`,
                    });
                    $iframe.css({
                        height: $iframe.height() + event.deltaRect.height + 'px'
                    })
                    popupSettings('translate', {width: event.rect.width, height: event.rect.height})
                }
            },
        },
        ...popUpSettings
    });

    store.data.$editButtons.html('').css('top', -10);

    $iframe.on('load', function(){
        let frame = $(this).contents();
        $('body', frame).addClass('design-translate')

        onLoadFrameScript(frame, popup)
    })
}