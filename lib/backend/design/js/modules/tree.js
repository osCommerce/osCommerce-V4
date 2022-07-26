
import style from "./tree.scss";

export default function (obj) {
    $('.close-sub-items', obj).each(function(){
        let $btn = $(this);
        let $li = $btn.closest('li');
        let $ul = $('> ul', $li);
        let $closeHolder = $('> .close-holder, > * > .close-holder, > * > * > .close-holder', $li);

        if ($btn.hasClass('closed')) {
            $li.addClass('closed');
            $ul.hide()
        } else {
            $li.removeClass('closed');
            $ul.show()
        }

        $closeHolder.off('click').on('click', openClose)
        $btn.off('click').on('click', openClose)

        function openClose(){
            if ($btn.hasClass('closed')) {
                $btn.removeClass('closed');
                $li.removeClass('closed');
                $ul.slideDown(100)
            } else {
                $btn.addClass('closed');
                $li.addClass('closed');
                $ul.slideUp(100)
            }
        }
    })
}