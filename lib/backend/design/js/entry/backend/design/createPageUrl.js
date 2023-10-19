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

export default function(data){
    let base = $('base').attr('href').trim();
    if (base.slice(-1) === '/') {
        base = base.slice(0, -1);
    }
    let url = base.slice(0, base.lastIndexOf('/'));

    let platformId = 1;
    if (data.platformId) {
        platformId = data.platformId;
    } else if (entryData.platformSelect && entryData.platformSelect[0]) {
        platformId = entryData.platformSelect[0].id;
    } else {
        platformId = entryData.platformsList.find(platform => platform.is_default).id;
    }

    const platform = entryData.platformsList.find(platform => platform.id === platformId);
    if (platform && platform.platform_url) {
        let url1 = new URL(window.location.protocol + '//' + platform.platform_url);
        let url2 = new URL(url);
        if (url1.origin == url2.origin) {
            url = window.location.protocol + '//' + platform.platform_url;
        }
    }
    if (data.action) {
        url += '/' + data.action;
    }

    url += '?theme_name=' + window.entryData.theme_name;
    url += '&platform_id=' + platformId;
    url += '&page_name=' + data.page_name;
    url += '&language=' + window.entryData.languageCode;

    if (data.get_params && data.get_params[platformId]) {
        for (let param in data.get_params[platformId]) {
            if (!data.get_params[platformId].hasOwnProperty(param)) {
                continue;
            }
            url += '&' + param + '=' + data.get_params[platformId][param];
        }
    }

    return url;
}
