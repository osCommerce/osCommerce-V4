
import fileHolder from './file-holder';

export default async function editor($box, options, src, value = '') {

    const $popUp = alertMessage(`<div class="svg-editor-container" style=""></div>
                   <link href="${entryData.mainUrl}/plugins/svg-editor/svgedit.css" rel="stylesheet">`, 'svg-editor-popup');

    if (window.svgEditor) {
        loadEditor();
    } else {
        $.get(`${entryData.mainUrl}/plugins/svg-editor/Editor.js`, loadEditor);
    }

    async function loadEditor() {
        $(window).scrollTop(0);
        const svgEditor = new Editor($('.svg-editor-container', $popUp)[0]);
        svgEditor.init();
        svgEditor.setConfig({
            imgPath: 'plugins/svg-editor/images',
            extPath: './plugins/svg-editor/extensions',
            //allowInitialUserOverride: true,
            extensions: [],
            noStorageOnLoad: true,
            userExtensions: [/* { pathName: './react-extensions/react-test/dist/react-test.js' } */],
            dimensions: [options.width || 640, options.height || 480],
        }, 'script');

        let imageSource = '';
        if (value) {
            if (['images', 'themes'].includes(value.slice(6).toLowerCase())) {
                imageSource = entryData.frontendUrl + value;
            } else {
                imageSource = entryData.mainUrl + '/uploads/' + value;
            }
        } else if (src) {
            imageSource = src;
        }
        imageSource = imageSource.replace(/\\/g, '/');

        let svg = '';
        let image = '';
        let width = options.width;
        let height = options.height;
        if (imageSource.slice(-4).toLowerCase() == '.svg') {
            try {
                const response = await fetch(imageSource);
                if (response.ok) {
                    svg = await response.text();
                }
            } catch (error) {
                console.error(error);
            }
        } else if (['.jpg', '.gif', '.png', 'jpeg', 'webp'].includes(imageSource.slice(-4).toLowerCase())) {

            const [imgWidth, imgHeight] = await new Promise(function(resolve, reject) {
                const img = new Image();
                img.onload = function() {
                    resolve([ this.width, this.height ]);
                };
                img.src = imageSource;
                img.onerror = function() {
                    reject([0, 0]);
                };
            });

            if (imgWidth && imgHeight) {
                if (!width) {
                    width = imgWidth;
                }
                if (!height) {
                    height = imgHeight;
                }

                const containerRatio = width / height;
                const imageRatio = imgWidth / imgHeight;

                let newImageWidth, newImageHeight;

                if (containerRatio > imageRatio) {
                    newImageWidth = imgWidth * (height / imgHeight);
                    newImageHeight = height;
                } else {
                    newImageWidth = width;
                    newImageHeight = imgHeight * (width / imgWidth);
                }

                const offsetX = Math.round((width - newImageWidth) / 2);
                const offsetY = Math.round((height - newImageHeight) / 2);

                const _imageSource = await fetch(imageSource)
                    .then(response => response.blob())
                    .then(blob => new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.onloadend = () => resolve(reader.result);
                        reader.onerror = reject;
                        reader.readAsDataURL(blob);
                    }));

                image = `<image width="${Math.round(newImageWidth)}" height="${Math.round(newImageHeight)}" x="${offsetX}" y="${offsetY}" xlink:href="${_imageSource}"/>`;

            }

        }
        if (!svg) {
            svg = `<svg width="${width}" height="${height}"  xmlns="http://www.w3.org/2000/svg"
                        xmlns:svg="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                       <g class="layer"><title>Layer 1</title>${image}</g></svg>`;
        }

        if (svgEditor.svgCanvas) {
            loadedCanvas(svg);
        } else {
            $(document).bind('svgEditorReady', () => loadedCanvas(svg));
        }
    }

    async function loadedCanvas(svg) {
        const svgEditor = window.svgEditor;
        const svgCanvas = window.svgEditor.svgCanvas;

        await svgEditor.loadSvgString(svg);
        svgEditor.updateCanvas();

        const saveButtonTemplate = '<se-menu-item id="tool_save" label="Save" shortcut="S" src="saveImg.svg"></se-menu-item>';
        svgCanvas.insertChildAtIndex(svgCanvas.$id('tools_top'), saveButtonTemplate, 1);


        svgCanvas.$click(svgCanvas.$id('tool_save'), function () {
            $.post('upload/save-svg', {
                svg: svgCanvas.getSvgString(),
                name: value || src || options.name
            }, function (response) {
                fileHolder($box, options, entryData.mainUrl + '/uploads/' + response.fileName, response.fileName, 'image');
            }, 'json');
        });
    }
}