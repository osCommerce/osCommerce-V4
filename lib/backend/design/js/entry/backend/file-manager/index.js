/**
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 *
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 *
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

import style from './style.scss';
import template from './template';
import gallery from './gallery';
import upload from './upload';
import fileHolder from './file-holder';

$.fn.fileManager = function(op){
    const _op = $.extend({
        name: 'file',
        value: '',
        upload: 'upload',
        delete: 'delete',
        url: 'upload/index',
        type: 'image',
        folder: 'images/',
        template: template(),
    }, op);

    return this.each(function() {
        const $box = $(this);

        if ($box.hasClass('applied')) {
            return false;
        }

        const options = {..._op, ...$box.data(), svg: $('textarea', $box).val()};

        $box.addClass('applied');
        $box.html(options.template);

        if (options.value) {
            let folder = options.folder;
            if (options.value.substr(0, 7) == 'images/' || options.value.substr(0, 7) == 'themes/') {
                folder = '';
            }
            fileHolder($box, options, entryData.frontendUrl + folder + options.value, '', options.type);
        } else {
            fileHolder($box, options, '', '', options.type);
        }

        gallery($box, options);
        upload($box, options);

    });
};