export default function(html){

    $('.widget-handle, .menu-widget', html).remove();

    /*let logoImg = $('.logo img', html);
    let logoUrl = logoImg.attr('src');
    logoUrl = window.location.protocol + '//' + window.location.host + logoUrl;
    logoImg.attr('src', logoUrl);*/

    $('.image-area-holder', html).remove();
    $('.product-area-holder', html).remove();

    $('*', html).removeAttr('class');
    $('*', html).removeAttr('data-name');
    $('*', html).removeAttr('id');

    return `${html.html()}`;
}