import draggablePopup from "src/draggablePopup";
import store from "./store";

export default function(framePrams, onLoadFrameScript, popUpSettings = {}){

    let defaultFramePframs = {
        width: '900',
        height: '700',
        class: 'edit-data-popup',
    };

    let $iframe = $('<iframe>', {...defaultFramePframs, ...framePrams});

    let popup = draggablePopup($iframe, {
        //draggable: false,
        top: 20,
        ...popUpSettings
    });

    store.data.$editButtons.html('').css('top', -10);

    $iframe.on('load', function(){
        let frame = $(this).contents();

        onLoadFrameScript(frame, popup)
    })
}