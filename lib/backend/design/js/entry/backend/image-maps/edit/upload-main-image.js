import popUpContent from "src/popup-content-wraper";

let img = function(data = {
    image: '',
    button: '',
    uploadUrl: 'image-maps/upload',
    svg: ''
}){
    let applySvgSize = function(){
        data.image.css({
            'position': 'absolute',
        });
        let height = data.image.height();
        let width = data.image.width();
        data.image.css({
            'position': '',
        });

        let svg = data.svg.get(0);
        svg.setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:xlink", "http://www.w3.org/1999/xlink");
        svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
    };
    applySvgSize();

    data.image.on('load', applySvgSize);

    let applyImage = function(file){
        data.image.css('display', '');
        data.image.attr('src', '../images/maps/' + file);
        $('.popup-box-wrap:last').remove();
        applySvgSize();
        data.image.on('load', applySvgSize);
    };

    data.button.dropzone({
        url: data.uploadUrl,
        previewTemplate: '<span data-dz-name></span>',
        success: function(e, data){
            data = JSON.parse(data);
            data = data[0];
            if (data.status === 'ok') {
                applyImage(data.file)
            }

            if (data.status === 'error') {
                alertMessage( popUpContent(data.text) );
            }

            if (data.status === 'choice') {
                alertMessage( popUpContent(data.text, [
                    {'name': 'ok', 'class': 'btn-ok'},
                    {'name': 'cancel', 'class': 'btn-cancel'}
                ]) );

                $('.btn-ok').on('click', function(){
                    applyImage(data.file)
                })
            }
        }
    })

};
export default img;