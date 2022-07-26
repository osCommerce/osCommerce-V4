import createUrl from "src/createUrl";
import openEditDataPopup from "./openEditDataPopup";
import store from "./store";

export default {
    iframeSettings(element) {
        let height;
        let page = $(element).data('page');

        switch (page){
            case 'seo': height = 400; break;
            case 'menu': height = 400; break;
            default: height = 700;
        }

        let frameParams = {
            page: page,
            field: $(element).data('field'),
            id: $(element).data('id'),
            split: $(element).data('split'),
            platform_id: store.data.platformId,
            language_id: store.data.languageId,
            is_guest: store.data.isGuest,
        };

        if ($(element).data('entity')) {
            frameParams.entity = $(element).data('entity');
        }
        if ($(element).data('key')) {
            frameParams.key = $(element).data('key');
        }

        return {
            src: createUrl(store.data.DIR_WS_HTTP_ADMIN_CATALOG + '/edit-data/' + $(element).data('page'), frameParams),
            height: height,
        }
    },

    frameScripts(frame, popup){
        $('.btn-primary', frame).on('click', function(){
            setTimeout(function(){
                //popup.remove();
                window.location.reload()
            }, 100)
        });
        $('.btn-cancel', frame).on('click', function(){
            setTimeout(function(){
                popup.remove();
                $.get(store.data.setFrontendTranslationTimeUrl)
            }, 100)
        });
    },

    hints(hintPosition){
        let _this = this;

        $('.edit-data-element').off('mouseenter').on('mouseenter', function(){
            store.data.$editButtons.html('');

            let page = $(this).data('edit-data-page');
            let field = $(this).data('edit-data-field');
            let id = $(this).data('edit-data-id');
            let split = $(this).data('edit-data-split');
            let text = $(this).text();

            let entity = $('.translation-key', this).data('translation-entity');
            if (entity) {
                entity = ` data-entity="${entity}" `;
            }
            let key = $('.translation-key', this).data('translation-key');
            if (key) {
                key = ` data-key="${key}" `;
            }

            let editBtn = $(`<div class="edit-data-button"
                        data-page="${page}"
                        data-field="${field}"
                        data-id="${id}"
                        data-split="${split}"
                        ${entity}${key}
                    >${page} - ${field}: ${text}</div>`);

            store.data.$editButtons.append(editBtn);

            let pageName;

            switch (page){
                case 'info': pageName = store.data.TEXT_INFORMATION; break;
                case 'seo': pageName = 'SEO heading'; break;
                default: pageName = page;
            }

            const popUpSettings = {
                heading: pageName + ' - ' + field
            }

            editBtn.on('click', function(){
                openEditDataPopup(_this.iframeSettings(this), _this.frameScripts, popUpSettings)
            })

            hintPosition($(this))
        });
    }
}