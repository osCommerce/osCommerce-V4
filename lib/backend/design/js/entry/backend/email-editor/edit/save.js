import popUpContent from "src/popup-content-wraper";
import clearHtml from "./clearHtml";

let save = function(data = {
    button: '',
    form: '',
    saveUrl: 'email-editor/save'
}){

    let savePage = function(){
        let pageData = data.form.serializeArray();
        let eData = emailEditor.data;

        let html = clearHtml($('.block.email').clone());
        pageData.push({
            name: 'html',
            value:  html
        });

        let saveData = $('.w-email-content').clone();
        $('.ui-resizable-handle', saveData).remove();
        pageData.push({
            name: 'data',
            value:  btoa(saveData.html())
        });

        pageData.push({
            name: 'theme_name',
            value:  eData.theme_name
        });
        pageData.push({
            name: 'template',
            value:  eData.template
        });

        $.post(data.saveUrl, pageData, function(response){

            let inputEmailId = $('input[name="email_id"]', data.form);
            let EmailIdTmp = inputEmailId.val();

            if (response.email_id) {
                inputEmailId.val(response.email_id);

                if (EmailIdTmp !== response.email_id) {
                    window.history.pushState('', '', 'email-editor/edit?email_id=' + response.email_id);
                }
            }

            if (response.status === 'ok') {
                alertMessage(popUpContent(response.text));
                setTimeout(function(){ $('.popup-box-wrap:last').remove() }, 1000)
            }

        }, 'json')
    };

    data.button.on('click', savePage);
    data.form.on('submit', savePage)

};
export default save;