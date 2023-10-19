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

const MODE_ST = process.env.npm_lifecycle_event == 'build' ? 'build' : 'dev';
const webpack = require('webpack');

const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const LicenseWebpackPlugin = require('license-webpack-plugin').LicenseWebpackPlugin;

webpackConfig = {
    context: __dirname + '/entry',
    entry: {
        //mainCss:  "./backend/main-css",
        //information:  "./backend/information",
        //emailEditor:  "./backend/email-editor/edit",
        //bannerEditor:  "./backend/banner-editor/index",
        //editData:  "./frontend/edit-data/index",
        design:  './backend/design/index',
        //designer:  "./backend/designer/index",
        //backups:  "./backend/design/backups",
        fileManager:  "./backend/file-manager/index",
        localLinks:  "./backend/local-links/index",
        selectProducts:  './backend/select-products/index'
    },

    output: {
        path:     __dirname + '/../../../../',
        filename: (obj) => {
            switch (obj.chunk.name) {
                //case 'information': return 'admin/themes/basic/js/information.js';
                //case 'emailEditor': return 'admin/themes/basic/js/email-editor/edit.js';
                //case 'bannerEditor': return 'admin/themes/basic/js/banner-editor.js';
                //case 'editData': return 'themes/basic/js/edit-data.js';
                case 'design': return 'admin/themes/basic/js/design.js';
                //case 'designer': return 'admin/themes/basic/js/designer.js';
                //case 'backups': return 'admin/themes/basic/js/backups.js';
                case 'fileManager': return 'admin/themes/basic/js/file-manager.js';
                case 'localLinks': return 'admin/themes/basic/js/local-links.js';
                case 'selectProducts': return 'admin/themes/basic/js/select-products.js';
            }
            return obj.chunk.id + '.js';
        },
        chunkFilename: 'admin/themes/basic/js/chunks/[name].js',
        library: '[name]',
    },

    watch: MODE_ST == 'dev',

    devtool: MODE_ST == 'dev' ? 'source-map' : false,

    plugins: [
        new webpack.DefinePlugin({
            MODE_ST: JSON.stringify(MODE_ST)
        }),
        new MiniCssExtractPlugin({
            filename: (obj) => {
                switch (obj.chunk.name) {
                    //case 'mainCss': return 'admin/themes/basic/css/css.css';
                    //case 'information': return 'admin/themes/basic/css/information.css';
                    //case 'emailEditor': return 'admin/themes/basic/css/email-editor/edit.css';
                    //case 'editData': return 'themes/basic/css/edit-data.css';
                    case 'design': return 'admin/themes/basic/css/design.css';
                    //case 'designer': return 'admin/themes/basic/css/designer.css';
                    case 'fileManager': return 'admin/themes/basic/css/file-manager.css';
                    case 'selectProducts': return 'admin/themes/basic/css/select-products.css';
                }
                return obj.chunk.id + '.css';
            },
            chunkFilename: '[id].css'
        }),
        new LicenseWebpackPlugin({
            perChunkOutput: false,
            addBanner: true,
            outputFilename: 'admin/themes/basic/js/LICENSES.txt'
        })
    ],

    module: {
        rules: [
            {
                test: /\.m?(js|jsx)$/,
                exclude: /node_modules(?!\/svgedit)/,
                loader: 'babel-loader',
                options: {
                    presets: [
                        [
                            '@babel/preset-env',
                            {
                                'useBuiltIns': 'entry'
                            }
                        ]
                    ],
                    plugins: [
                        '@babel/plugin-syntax-dynamic-import',
                        '@babel/plugin-transform-runtime'
                    ]
                }
            },
            {
                test: /\.(css|scss)$/,
                use: [
                    {
                        loader: MiniCssExtractPlugin.loader,
                        options: {
                            publicPath:  __dirname + '/../../../../'
                        }
                    },
                    {
                        loader: 'css-loader',
                        options: {
                            sourceMap: true
                        }
                    },
                    {
                        loader: 'sass-loader',
                        options: {
                            sourceMap: true,
                        }
                    }
                ]
            }
        ]
    },

    resolve: {
        alias: {
            src: __dirname + '/modules/',
        }
    }
};

module.exports = webpackConfig;