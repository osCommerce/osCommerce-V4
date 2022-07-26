{use class = "yii\helpers\Html"}
<div class="note-box">
    <div class="note-box-form">
        {foreach $productsNotes as $key => $note}
            <div class="noteRow">
                <div class="form-view">
                    <div class="row">
                    <div class="view col-md-10">
                        {$note.note}
                    </div>
                        <div class="control col-md-2">
                            <div class="editNote"></div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="edit col-md-10">
                            {Html::textarea('ProductsNotes['|cat:$key|cat:'][note]', $note.note, ['class'=>'form-control ck-editor', 'id' =>'noteId'|cat:$key ] )}
                        </div>
                        <div class="control col-md-2">
                            <div class="cancelNote"></div>
                            <div class="removeNote"></div>
                            <div class="insertButton">
                                {Html::a($smarty.const.TEXT_DOCUMENTS, $app->urlManager->createUrl(['categories/file-filter-form','editorId' =>'noteId'|cat:$key]), ['class'=>'btn linkPopup'])}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {/foreach}
        <div id="newNotes"></div>
    </div>
    <div class="buttons_hours">
        <button class="btn" id="moreNote">{$smarty.const.TEXT_ADD_MORE}</button>
    </div>
</div>
<script>
    var bodyT = $('body');
    var countNewNotesId = 0;

    bodyT.on('click', '#moreNote', function (event) {
        event.preventDefault();
        $('#newNotes').append('<div class="noteRow"><div class="form-group" style="display: block; margin: 2em 0;"><div class="row"><div class="edit col-md-10"><textarea class="form-control" id="newNoteId'+countNewNotesId+'" name="ProductsNotesForm[][note]"></textarea></div><div class="control col-md-2"><div class="insertButton"><a href="categories/file-filter-form/?editorId=newNoteId'+countNewNotesId+'" class="btn newLinkPopup'+countNewNotesId+'">{$smarty.const.TEXT_DOCUMENTS}</a></div><div class="removeNote"></div></div>');
        $('.newLinkPopup'+countNewNotesId).popUp({
            box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box'><div class='popup-heading cat-head'>{$smarty.const.TEXT_PAGE_LINKS}</div><div class='pop-up-close'></div><div class='pop-up-content'><div class='preloader'></div></div></div></div>",
            one_popup: false
        });
        CKEDITOR.inline('newNoteId'+countNewNotesId);
        countNewNotesId++;
    });
    bodyT.on('click', '.removeNote', function () {
        event.preventDefault();
        $(this).closest('.noteRow').remove()
    });
    bodyT.on('click', '.editNote', function () {
        event.preventDefault();
        var formView = $(this).closest('.form-view');
        formView.hide();
        formView.closest('.noteRow').find('.form-group').show();
    });
    bodyT.on('click', '.cancelNote', function () {
        event.preventDefault();
        var formGroup = $(this).closest('.form-group');

        var noteData = formGroup.find('textarea.ck-editor');
        var data = CKEDITOR.instances[noteData.attr('id')].getData();;
        var formView = formGroup.closest('.noteRow').find('.form-view');
        var view = formView.find('.view');
        view.html(data);
        formGroup.hide();
        formView.show();
    });

    var linkPopup = $('.linkPopup');

    linkPopup.popUp({
        box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box'><div class='popup-heading cat-head'>{$smarty.const.TEXT_PAGE_LINKS}</div><div class='pop-up-close'></div><div class='pop-up-content'><div class='preloader'></div></div></div></div>",
        one_popup: false
    });
    linkPopup.on('click', function(){
        $('.popup-heading').text($(this).text());
    });
</script>
