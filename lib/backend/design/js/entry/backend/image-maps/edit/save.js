import popUpContent from "src/popup-content-wraper";

let save = function(data = {
    image: '',
    button: '',
    imageMap: '',
    form: '',
    saveUrl: 'image-maps/save'
}){

    let savePage = function(){
        let pageData = data.form.serializeArray();

        pageData.push({
            name: 'image',
            value:  data.image.attr('src').split('/').slice(-1)[0]
        });
        pageData.push({
            name: 'map',
            value:  data.imageMap.toJSON()
        });

        $.post(data.saveUrl, pageData, function(response){

            let inputMapsId = $('input[name="maps_id"]', data.form);
            let mapsIdTmp = inputMapsId.val();

            inputMapsId.val(response.maps_id);

            if (mapsIdTmp !== response.maps_id) {
                window.history.pushState('', '', 'image-maps/edit?maps_id=' + response.maps_id);
            }

            console.log(response);

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