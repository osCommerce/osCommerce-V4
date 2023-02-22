import style from "./draggablePopup.scss";
import interact from "interactjs";
import popupSettings from "./popupSettings";
//import displace from 'displacejs';

export default function(content, op){

    let options = $.extend(true, {
        heading: '',
        className: '',
        top: 200,
        width: false,
        height: false,
        buttons: [],
        name: false,
        draggable: {
            allowFrom: '.popup-heading',
            listeners: {
                move: function (event) {
                    var target = event.target
                    // keep the dragged position in the data-x/data-y attributes
                    var x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx
                    var y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy

                    // translate the element
                    target.style.transform = 'translate(' + x + 'px, ' + y + 'px)'

                    // update the posiion attributes
                    target.setAttribute('data-x', x)
                    target.setAttribute('data-y', y)

                    if (options.name) {
                        popupSettings(options.name, {x, y})
                    }
                },
            }
        },
        resizable: {
            // resize from all edges and corners
            edges: { left: true, right: true, bottom: true, top: true },

            listeners: {
                move (event) {
                    var target = event.target
                    var x = (parseFloat(target.getAttribute('data-x')) || 0)
                    var y = (parseFloat(target.getAttribute('data-y')) || 0)

                    // update the element's style
                    target.style.width = event.rect.width + 'px'
                    target.style.height = event.rect.height + 'px'

                    // translate when resizing from top or left edges
                    x += event.deltaRect.left
                    y += event.deltaRect.top

                    target.style.transform = 'translate(' + x + 'px,' + y + 'px)'

                    target.setAttribute('data-x', x)
                    target.setAttribute('data-y', y)

                    if (options.name) {
                        popupSettings(options.name, {x, y, width: event.rect.width, height: event.rect.height})
                    }
                }
            },
        },
        aroundArea: false,
        position: 'absolute',
        close: 'remove',
        beforeRemove: function(){ return true; }
    },op);

    let body = $('body');

    let popupDraggable = $(`<div class="popup-draggable ${options.className}"></div>`);
    body.append(popupDraggable);

    let aroundArea = $(`<div class="around-pop-up"></div>`);
    if (options.aroundArea) {
        body.append(aroundArea);
    }

    let close = $('<div class="pop-up-close"></div>');
    popupDraggable.append(close);

    if (options.heading) {
        let headingWrap = $('<div class="popup-heading"></div>');
        headingWrap.append(options.heading);
        popupDraggable.append(headingWrap);
    }

    let contentWrap = $('<div class="popup-content pop-mess-cont"></div>');
    contentWrap.append(content);
    popupDraggable.append(contentWrap);


    if (options.buttons && options.buttons.length > 0) {
        let buttonsWrap = $('<div class="popup-buttons"></div>');

        options.buttons.forEach(function(item) {
            buttonsWrap.append(item);
        });

        popupDraggable.append(buttonsWrap)
    }

    if (options.width) {
        popupDraggable.css('width', options.width + 'px');
    }
    if (options.height) {
        popupDraggable.css('height', options.height + 'px');
    }
    popupDraggable.css({
        left: ($(window).width() - popupDraggable.width())/2,
        top: $(window).scrollTop() + options.top,
    });
    if (options.name) {
        const popupSettingsVal = popupSettings(options.name);
        if (popupSettingsVal) {
            let translateX = '0px';
            let translateY = '0px';
            if (popupSettingsVal.x) {
                popupDraggable.attr('data-x', popupSettingsVal.x)
                translateX = popupSettingsVal.x + 'px'
            }
            if (popupSettingsVal.y) {
                if (popupSettingsVal.y + options.top < 0) {
                    popupSettingsVal.y = -options.top;
                }
                popupDraggable.attr('data-y', popupSettingsVal.y)
                translateY = popupSettingsVal.y + 'px'
            }

            popupDraggable.css('transform', `translate(${translateX}, ${translateY})`)
            if (popupSettingsVal.width) {
                popupDraggable.css('width', popupSettingsVal.width + 'px');
            }
            if (popupSettingsVal.height) {
                popupDraggable.css('height', popupSettingsVal.height + 'px');
            }
        }
    }
    if (options.position === 'fixed') {
        popupDraggable.css({
            top: options.top,
            position: 'fixed'
        });
    } else {
        interact(popupDraggable.get(0))
            .draggable(options.draggable)
            .resizable(options.resizable)
    }

    close.on('click', () => popupDraggable.trigger('close'));
    aroundArea.on('click', closePopup);
    popupDraggable.on('close', closePopup);
    popupDraggable.on('open', openPopup);

    function closePopup() {
        options.beforeRemove();
        if (options.close === 'remove') {
            popupDraggable.remove();
            aroundArea.remove()
        } else {
            popupDraggable.hide();
            aroundArea.hide()
        }
    }

    function openPopup() {
        popupDraggable.show();
        aroundArea.show()
    }

    let handle = {};
    if ($('.popup-heading', popupDraggable).length){
        handle = { handle: '.popup-heading' };
    }

    if (typeof options.draggable == "function") {
        popupDraggable.draggable(handle);
        //const d = displace(popupDraggable.get(0), {});
    }

    return popupDraggable;
}