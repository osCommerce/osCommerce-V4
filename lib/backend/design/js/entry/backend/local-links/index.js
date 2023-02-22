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

import popup from './popup';

$.fn.localLinks = function(op){
    const _op = $.extend({
        field: '',
        languages_id: '',
        platform_id: '',
    }, op);

    return this.each(function() {
        const $button = $(this);

        if ($button.hasClass('applied')) {
            return false;
        }

        const options = {..._op, ...$button.data()};
        $button.addClass('applied');

        $button.on('click', () => popup(options));
    });
};