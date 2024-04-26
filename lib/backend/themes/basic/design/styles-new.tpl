{include 'menu.tpl'}
{use class="backend\assets\DesignAsset"}
{use class="backend\assets\DesignStylesAsset"}
{DesignAsset::register($this)|void}
{DesignStylesAsset::register($this)|void}
<div id="app">
    <div class="style-edit-page">

        <div class="tabbable tabbable-custom">
            <ul class="nav nav-tabs nav-tabs-big " role="tablist">
                <li v-for="(tab, index) in stylesGroupTabs" :key="tab.tab" :class="tab.tab == '' || tab.tab == 'Main' ? 'active' : ''" data-bs-toggle="tab" :data-bs-target="'#tab_' + index" aria-selected="true" role="tab">
                    <a><span>{{ tab.tab || 'Main' }}</span></a>
                </li>
                <li @click="addTab()">
                    <a><span>+</span></a>
                </li>
            </ul>
            <div class="tab-content">
                <div v-for="(tab, index) in stylesGroupTabs" :key="tab.tab"
                        :class="'tab-pane tabbable-custom' + (tab.tab == '' || tab.tab == 'Main' ? ' active' : '')"
                        :id="'tab_' + index"
                        role="tabpanel">


        <div class="row" v-sortable="{ options: { animation: 250, handle: '.sort-handle-1' }}" data-group="group" @end="onOrderChangeGroup($event)">

            <template v-for="group in stylesGroups" :key="group.group_id">
            <div class="col-12 col-xxl-6" v-if="group.tab == tab.tab || (group.tab == 'Main' && group.tab == '') || (group.tab == '' && group.tab == 'Main')">
                <div class="widget box box-no-shadow" :id="'styles-main-'+group.group_id">
                    <div class="widget-header">
                        <div class="sort-handle sort-handle-1"></div>
                        <h4 v-if="!group.edit">{{ group.group_name }} <span class="btn-edit" @click="group.edit = true"></span> <span class="btn-remove" @click="removeGroup(group.group_id)"></span></h4>
                        <div class="edit-group-name" v-else>
                            <input type="text" v-model="group.group_name" class="form-control"/>
                            <span class="btn-save" @click="group.edit = false"></span>
                        </div>
                        <div class="toolbar no-padding">
                            <div class="btn-group">
                                <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="widget-content">

                        <table class="table table-bordered">
                            <thead>
                            <tr class="">
                                <th class=""></th>
                                <th class="">{$smarty.const.STYLE_NAME}</th>
                                <th class="">{$smarty.const.STYLE_VALUE}</th>
                                <th class="">{$smarty.const.HEADING_TYPE}</th>
                                <th class=""></th>
                                <th class=""></th>
                            </tr>
                            </thead>

                            <tbody class="main-styles" v-sortable="{ options: { animation: 250, handle: '.sort-handle-2', group: 'sort-styles'}}" @end="onOrderChange($event)" data-group="style" :data-group-id="group.group_id">
                                <template v-for="style in mainStyles" :key="style.oldName">
                                    <tr v-if="style.group_id == group.group_id || (style.group_id == '' && group.group_id == 1)" class="sort-styles sort-item-2" :key="style.oldName" :data-name="style.oldName">
                                        <td class="sort-handle sort-handle-2"></td>
                                        <td class="">
                                            <input type="text" :value="style.name" @change="style.name = $event.target.value" class="form-control" v-if="style.newStyle">
                                            <span v-if="!style.newStyle">{{ style.name }}</span>
                                        </td>
                                        <td class="style-value">
                                            <select v-model="style.value" class="form-control" v-if="style.type == 'font'">
                                                <option value=""></option>
                                                {foreach $fontAdded as $item}
                                                    <option value="{$item}">{$item}</option>
                                                {/foreach}
                                                <option value="Arial">Arial</option>
                                                <option value="Verdana">Verdana</option>
                                                <option value="Tahoma">Tahomaa</option>
                                                <option value="Times">Times</option>
                                                <option value="Times New Roman">Times New Roman</option>
                                                <option value="Georgia">Georgia</option>
                                                <option value="Trebuchet MS">Trebuchet MS</option>
                                                <option value="Sans">Sans</option>
                                                <option value="Comic Sans MS">Comic Sans MS</option>
                                                <option value="Courier New">Courier New</option>
                                                <option value="Garamond">Garamond</option>
                                                <option value="Helvetica">Helvetica</option>
                                            </select>
                                            <colorpicker v-model="style.value"
                                                         v-if="style.type == 'color'"></colorpicker>
                                            <colorpicker v-model="style.value"
                                                         v-if="style.type == 'color-opacity'"
                                                         format="hsl"></colorpicker>

                                            <colorvars v-model="style.value"
                                                       :main-styles="mainStyles"
                                                       :styles-groups="stylesGroups"
                                                       v-if="style.type == 'color-var'" ></colorvars>

                                            <fontvars v-model="style.value"
                                                       :main-styles="mainStyles"
                                                       v-if="style.type == 'font-var'" ></fontvars>

                                        </td>
                                        <td class="style-type">
                                            <select v-model="style.type" class="form-control" v-if="style.newStyle">
                                                <option value="color">{$smarty.const.TEXT_COLOR_}</option>
                                                <option value="color-var">{$smarty.const.COLOR_VAR}</option>
                                                <option value="color-opacity">{$smarty.const.COLOR_HSL_OPACITY}</option>
                                                <option value="font">{$smarty.const.TEXT_FONT}</option>
                                                <option value="font-var">{$smarty.const.FONT_VAR}</option>
                                            </select>
                                            <span v-if="!style.newStyle">{{ style.type }}</span>
                                        </td>
                                        <td class="remove-style" @click="removeStyle(style.name)"></td>
                                        <td class="count">{{ style.count }}</td>
                                    </tr>
                                </template>
                            </tbody>

                        </table>

                        <div class="row">
                            <div class="col-12 align-right p-t-2">
                                <span class="btn btn-primary btn-add-style" @click="addStyle(group.group_id)">{$smarty.const.TEXT_ADD_STYLE}</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            </template>

        </div>

                    <div class="btn-bar">
                        <div class="btn-right">
                        </div>
                        <div class="btn-right">
                            <span class="btn btn-primary" @click="addGroup(tab.tab)">{$smarty.const.TEXT_ADD_GROUP}</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="btn-bar">
        <div class="btn-left">
            <span class="btn btn-export-style" data-type="font" @click="exportStyles('font')">{$smarty.const.EXPORT_FONTS}</span>
            <span class="btn btn-export-style" data-type="color" @click="exportStyles('color')">{$smarty.const.EXPORT_COLORS}</span>
            <span class="btn btn-export-style" data-type="color" @click="exportStyles('buttons')">Export buttons</span>
            <span class="btn btn-export-style" data-type="color" @click="exportStyles('headings')">Export headings</span>
            <span class="btn btn-export-style" data-type="color" @click="exportStyles('price')">Export price</span>
            <span class="btn btn-export-style" data-type="color" @click="exportStyles('form')">Export forms</span>
            {*<span class="btn btn-primary" @click="addGroup()">{$smarty.const.TEXT_ADD_GROUP}</span>*}
        </div>
        <div class="btn-right">
            <span class="btn btn-confirm btn-save-styles" @click="savePage()">{$smarty.const.IMAGE_SAVE}</span>
        </div>
    </div>
</div>

<script type="text/x-template" id="bootstrap-colorpicker">
    <div  class="input-group colorpicker-component">
        <input type="text" class="form-control" />
        <span class="input-group-append"><span class="input-group-text colorpicker-input-addon"><i></i></span></span>
    </div>
</script>

<script type="text/x-template" id="color-vars">
    <div :class="'select-style applied' + (opened ? ' opened' : '')" data-type="color" @click="open()">
        <div class="select-style-around" @click="close()"></div>
        <div class="select-style-content">
            <div class="search-style">
                <input v-model="keys" type="text" class="form-control" placeholder="Search">
            </div>
            <div class="main-styles-list">
                <div data-value="">
                    <span class="name">&nbsp;</span>
                </div>

                <div class="group" v-for="group in stylesGroups" :key="group.group_id">
                    <template v-if="group.group_id == 1 || keys">
                    <h4 class="group-name">
                        {{ group.group_name}}
                    </h4>
                    <div class="group-styles">
                        <template v-for="color in colors" :key="color.name">
                            <div v-if="color.group_id == group.group_id" @click="selectColor(color.name, color.value)">
                                <span class="style-color" :style="{ background: color.value }"></span>
                                <span class="name">{{ color.name}}</span>
                            </div>
                        </template>
                    </div>
                    </template>
                </div>

            </div>
        </div>
        <div class="select-style-selected">
            <span class="style-color" :style="{ background: colorVal}"></span>
            <span class="name">{{ modelValue}}</span>
        </div>
    </div>
</script>

<script type="text/x-template" id="font-vars">
    <div :class="'select-style applied' + (opened ? ' opened' : '')" data-type="font" @click="open()">
        <div class="select-style-around" @click="close()"></div>
        <div class="select-style-content">
            <div class="search-style">
                <input v-model="keys" type="text" class="form-control" placeholder="Search">
            </div>
            <div class="main-styles-list">
                <div data-value="">
                    <span class="name">&nbsp;</span>
                </div>

                <div v-for="font in fonts" :key="font.name" @click="selectFont(font.name, font.value)">
                    <span class="name">{{ font.name}}</span>
                </div>

            </div>
        </div>
        <div class="select-style-selected">
            <span class="name">{{ modelValue}}</span>
        </div>
    </div>
</script>

<script>
    const { createApp, ref } = Vue;

    const app = createApp({
        setup() {
            const mainStyles = ref(JSON.parse('{json_encode($mainStyles)}'));
            const stylesGroups = ref(JSON.parse('{json_encode($stylesGroups)}'));
            const stylesGroupTabs = ref(JSON.parse('{json_encode($stylesGroupTabs)}'));
            console.log(mainStyles);

            function removeStyle(name){
                const index = this.mainStyles.findIndex(i => i.name === name);
                if (index > -1) {
                    this.mainStyles.splice(index, 1);
                }
            }
            function onOrderChange(event) {
                if (event.to.dataset.group === 'style') {
                    const mainStyles = this.mainStyles;
                    const groupTo = event.to.dataset.groupId;
                    const itemName = event.item.dataset.name;
                    $('> tr', event.from).each(function (i) {
                        const $tr = $(this);
                        mainStyles.forEach(function (item, j) {
                            if (item.oldName == $tr.data('name')) {
                                let item = mainStyles.splice(j, 1)[0];
                                item.sort_order = i;
                                mainStyles.push(item)
                            }
                        })
                    })
                    if (event.from != event.to) {
                        $('> tr', event.to).each(function (i) {
                            const $tr = $(this);
                            mainStyles.forEach(function (item, j) {
                                if (item.oldName == $tr.data('name')) {
                                    let item = mainStyles.splice(j, 1)[0];
                                    item.sort_order = i;
                                    if (itemName == $tr.data('name')) {
                                        item.group_id = groupTo;
                                    }
                                    mainStyles.push(item)
                                }
                            })
                        })
                    }
                }
            }
            function onOrderChangeGroup(event) {
                if (event.to.dataset.group === 'group') {
                    let item = this.stylesGroups.splice(event.oldIndex, 1)[0];
                    this.stylesGroups.splice(event.newIndex, 0, item);
                }
            }
            function addStyle(group_id) {
                this.mainStyles.push({ name: '', value: '', type: 'color-var', group_id: group_id, count: 0, oldName: Math.random().toString(), newStyle: true });
            }
            function savePage() {
                $.post('design/style-main-save', {
                    styles: this.mainStyles,
                    groups: this.stylesGroups,
                    theme_name: '{$theme_name}'
                }, function (response) {
                    const $popup = alertMessage(response.text, 'alert-message')
                    setTimeout(() => $popup.remove(), 2000)
                }, 'json')
            }
            function addGroup(tab) {
                let group_id = this.stylesGroups
                    .reduce((max, group) => (max < group.group_id ? group.group_id : max), 0) + 1;
                this.stylesGroups.push({
                    group_id,
                    group_name: 'New group',
                    tab
                })
            }
            function addTab() {
                const stylesGroupTabs = this.stylesGroupTabs;
                const popup = alertMessage(`
                        <div class="popup-heading">Enter tab name</div>
                        <div class="popup-content"><input class="form-control" type="text"></div>
                        <div class="popup-buttons">
                            <div><span class="btn btn-cancel">Cancel</span></div>
                            <div><span class="btn btn-submit">Add</span></div>
                        </div>`);

                $('.btn-submit', popup).on('click', function () {
                    stylesGroupTabs.push({
                        tab: $('input', popup).val()
                    });
                    popup.remove()
                });
            }
            function removeGroup(group_id) {
                const stylesGroups = this.stylesGroups;
                for (let i = 0; i < this.mainStyles.length; i++) {
                    let index = this.mainStyles.findIndex(item => item.group_id == group_id);
                    if (index !== -1) {
                        this.mainStyles.splice(index, 1)
                    } else {
                        break;
                    }
                }
                stylesGroups.forEach(function (item, j) {
                    if (item.group_id == group_id) {
                        stylesGroups.splice(j, 1);
                    }
                });
            }
            function exportStyles(type) {
                const $popUp = alertMessage(`
                    <form>
                        <div class="popup-heading">{$smarty.const.EXPORT_STYLES}</div>
                        <div class="popup-content pop-mess-cont">
                            <div class="row align-items-center m-b-2 block-name">
                                <div class="col-5 align-right">
                                    <label>{$smarty.const.TEXT_NAME}<span class="colon">:</span></label>
                                </div>
                                <div class="col-7">
                                    <input name="name" type="text" class="form-control" autofocus="">
                                </div>
                            </div>
                            <div class="row align-items-center m-b-2 save-to-groups">
                                <div class="col-5 align-right">
                                    <label>{$smarty.const.SAVE_TO_THEME_WIZARD}<span class="colon">:</span></label>
                                </div>
                                <div class="col-7">
                                    <input name="save-to-groups" type="checkbox" class="form-control" checked>
                                </div>
                            </div>
                            <div class="row align-items-center m-b-2 download">
                                <div class="col-5 align-right">
                                    <label>{$smarty.const.DOWNLOAD_ON_MY_COMPUTER}<span class="colon">:</span></label>
                                </div>
                                <div class="col-7">
                                    <input name="download" type="checkbox" class="form-control" checked>
                                </div>
                            </div>
                            <div class="row m-b-2">
                                <div class="col-5 align-right">
                                    <label>{$smarty.const.TEXT_COMMENTS}<span class="colon">:</span></label>
                                </div>
                                <div class="col-7">
                                    <textarea name="comment" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="popup-buttons">
                            <button type="submit" class="btn btn-primary">{$smarty.const.TEXT_EXPORT}</button>
                            <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
                        </div>
                    </form>
            `);

                const $form = $('form', $popUp);

                $form.on('submit', function (e) {
                    e.preventDefault();
                    const data = $(this).serializeArray();
                    data.push({ name: 'theme_name', value: '{$theme_name}'});
                    data.push({ name: 'type', value: type});

                    $.post('design/export-styles', data, function (response) {
                        if (response.error){
                            alertMessage(response.error, 'alert-message');
                        }
                        if (response.text){
                            const $message = alertMessage(response.text, 'alert-message');
                            setTimeout(() => $message.remove(), 2000);
                            if ($('input[name="download"]', $form).prop('checked') && response.filename) {

                                const url = new URL(window.entryData.mainUrl + '/design/download-block');
                                url.searchParams.set('filename', response.filename);
                                if ($('input[name="save-to-groups"]', $form).prop('checked') == false) {
                                    url.searchParams.set('delete', 1);
                                }

                                window.location = url.toString();
                            }
                        }
                    }, 'json')
                })
            }

            return {
                mainStyles,
                stylesGroups,
                stylesGroupTabs,
                removeStyle,
                onOrderChange,
                onOrderChangeGroup,
                addStyle,
                savePage,
                addGroup,
                addTab,
                exportStyles,
                removeGroup
            }
        }
    });
    app.use(sortablejs);

    app.component('colorpicker', {
        props: ['modelValue', 'format'],
        template: '#bootstrap-colorpicker',
        mounted() {
            const vm = this
            $(function () {
                $('input', vm.$el).val(vm.modelValue);
                $(vm.$el).colorpicker({
                    sliders: {
                        saturation: { maxLeft: 200, maxTop: 200},
                        hue: { maxTop: 200},
                        alpha: { maxTop: 200}
                    },
                    format: vm.format || 'auto'
                }).on('colorpickerChange', function (e) {
                    vm.$emit('update:modelValue', e.value)
                })
            })
        },
        watch: {
            modelValue(value) {
                $('input', this.$el).val(value).trigger('change')
            }
        },
        unmounted() {
            const $component = $(this.$el)
            if ($component.length && $component.hasClass('colorpicker-element')) {
                $component.colorpicker('destroy')
            }
        }
    });

    app.component('colorvars', {
        props: ['modelValue', 'mainStyles', 'stylesGroups'],
        template: '#color-vars',
        computed: {
            colors() {
                return this.searchColorVar()
            }
        },
        data() {
            return {
                keys: '',
                colorVal: '',
                opened: false
            }
        },
        mounted() {
            this.colorVal = startStyle(this.modelValue, this.mainStyles);

            function startStyle (name, mainStyles){
                const style = mainStyles.find(item => item.name == name);
                if (style.type == 'color-var') {
                    return startStyle(style.value, mainStyles);
                } else {
                    return style.value || '';
                }
            }
        },
        methods: {
            selectColor (name, value) {
                this.$emit('update:modelValue', name);
                this.colorVal = value;
                setTimeout(() => this.opened = false, 100);
            },
            searchColorVar (e) {
                const key = this.keys;
                const colors = [];
                const styles = this.mainStyles;
                styles.forEach(function (item) {
                    if (colors.find(i => i.name === item.name)) {
                        return
                    }
                    let value = '';
                    if (['color', 'color-opacity'].includes(item.type)) {
                        value = item.value;
                    } else if (item.type === 'color-var' && item.value) {
                        if (item.valu2) {
                            value = item.valu2;
                        } else {
                            value = getColor(item.value);
                            item.valu2 = getColor(item.value);
                        }
                    }
                    if (value && (!key || item.name.toLowerCase().includes(key.toLowerCase()))) {
                        colors.push({ name: item.name, value, group_id: item.group_id });
                    }
                });

                function getColor(colorVar) {
                    let color = '';
                    styles.forEach(function (item) {
                        if (item.name === colorVar) {
                            if (item.type === 'color-var') {
                                color = getColor(item.value);
                            } else {
                                color = item.value
                            }
                        }
                    });
                    return color;
                }
                return colors
            },
            open () {
                this.opened = true;
            },
            close() {
                setTimeout(() => this.opened = false, 100);
            }
        }
    });

    app.component('fontvars', {
        props: ['modelValue', 'mainStyles'],
        template: '#font-vars',
        computed: {
            fonts() {
                return this.searchFontVar()
            }
        },
        data() {
            return {
                keys: '',
                fontVal: '',
                opened: false
            }
        },
        mounted() {
            this.fontVal = startStyle(this.modelValue, this.mainStyles);

            function startStyle (name, mainStyles){
                const style = mainStyles.find(item => item.name == name);
                if (style.type == 'font-var') {
                    return startStyle(style.value, mainStyles);
                } else {
                    return style.value || '';
                }
            }
        },
        methods: {
            selectFont (name, value) {
                this.$emit('update:modelValue', name);
                this.fontVal = value;
                setTimeout(() => this.opened = false, 100);
            },
            searchFontVar (e) {
                const key = this.keys;
                const fonts = [];
                const styles = this.mainStyles;
                styles.forEach(function (item) {
                    if (fonts.find(i => i.name === item.name)) {
                        return
                    }
                    let value = '';
                    if (['font', 'font-opacity'].includes(item.type)) {
                        value = item.value;
                    } else if (item.type === 'font-var' && item.value) {
                        value = getFont(item.value);
                    }
                    if (value && (!key || item.name.toLowerCase().includes(key.toLowerCase()))) {
                        fonts.push({ name: item.name, value });
                    }
                });

                function getFont(fontVar) {
                    let font = '';
                    styles.forEach(function (item) {
                        if (item.name === fontVar) {
                            if (item.type === 'font-var') {
                                font = getFont(item.value);
                            } else {
                                font = item.value
                            }
                        }
                    });
                    return font;
                }
                return fonts
            },
            open () {
                this.opened = true;
            },
            close() {
                setTimeout(() => this.opened = false, 100);
            }
        }
    });
    app.mount('#app');

    $(function () {
        $('.btn-save-boxes').on('click', function () {
            $('.btn-save-styles').trigger('click')
        });
    });
</script>