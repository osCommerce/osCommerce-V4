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

export default {
    name: 'canvas-border',
    async init (S) {
        const svgEditor = this,
            $ = jQuery,
            svgCanvas = svgEditor.canvas;

        let svgroot = document.getElementById('svgroot');
        let safetyLine;
        let safetyLineKey = false;

        return {
            name: 'bleed',
            callback () {
            },
            canvasUpdated (opts) {
                if (!svgEditor.bannerUploaded) return

                svgEditor.bannerUploaded.then(function(){

                    safetyLine = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
                    svgroot.appendChild(safetyLine);

                    let zoom = svgCanvas.getZoom();

                    svgCanvas.assignAttributes(safetyLine, {
                        'fill-opacity': 0,
                        'stroke-opacity': 0.5,
                        stroke: "#000000",
                        id: "safetyLine",
                        style: "pointer-events:none",

                        'stroke-width': svgCanvas.contentW * 10 * zoom,
                        x: opts.new_x - (svgCanvas.contentW * 5 * zoom),
                        y: opts.new_y - (svgCanvas.contentW * 5 * zoom),
                        width: svgCanvas.contentW * zoom + svgCanvas.contentW * 10 * zoom,
                        height: svgCanvas.contentH * zoom + svgCanvas.contentW * 10 * zoom,
                    });
                }).catch(function (error) {
                    console.error(new Error(error))
                })

                if (safetyLine) {

                    let zoom = svgCanvas.getZoom();

                    svgCanvas.assignAttributes(safetyLine, {
                        'stroke-width': svgCanvas.contentW * 10 * zoom,
                        x: opts.new_x - (svgCanvas.contentW * 5 * zoom),
                        y: opts.new_y - (svgCanvas.contentW * 5 * zoom),
                        width: svgCanvas.contentW * zoom + svgCanvas.contentW * 10 * zoom,
                        height: svgCanvas.contentH * zoom + svgCanvas.contentW * 10 * zoom,
                    });
                }
            },
            selectedChanged (opts) {
            }
        };
    }
};
