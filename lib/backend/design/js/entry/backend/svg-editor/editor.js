import Editor from 'svgedit/src/editor/Editor';
/* for available options see the file `docs/tutorials/ConfigOptions.md` */
const svgEditor = new Editor(document.getElementById('container'));
/* initialize the Editor */
svgEditor.init();
/* set the configuration */
svgEditor.setConfig({
    allowInitialUserOverride: true,
    extensions: [],
    noDefaultExtensions: false,
    userExtensions: []
});