import style from "./edit/style.scss";

import uploadMainImage from "./edit/upload-main-image";
import saveMap from "./edit/save";
import imageMapCreator from "./edit/image-map-creator";
import toolsBar from "src/tools-bar";




let btnUploadImage = $('.upload-image');
let btnSave = $('.btn-save-boxes');
let mapImage = $('.map-image');
let form = $('.map-info');
let svg = $('#svg');

toolsBar();

uploadMainImage({
    image: mapImage,
    button: btnUploadImage,
    uploadUrl: 'image-maps/upload',
    svg: svg
});

let imageMap = imageMapCreator();

saveMap({
    image: mapImage,
    button: btnSave,
    imageMap: imageMap,
    form: form,
    saveUrl: 'image-maps/save'
});

//show svg elements from saved data
(function wait(){
    if (!window.imageMapsData) {
        setTimeout(wait, 500)
    } else {
        imageMap.fromJSON(imageMapsData.items);
    }
})()