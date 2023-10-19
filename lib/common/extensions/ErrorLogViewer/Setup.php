<?php

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

namespace common\extensions\ErrorLogViewer;

class Setup extends \common\classes\modules\SetupExtensions
{

    public static function getDescription()
    {
        return 'This extension allows you to view error logs';
    }

    public static function getAdminMenu()
    {
        return [
            [
                'parent' => 'BOX_HEADING_TOOLS',
                'sort_order' => '6',
                'box_type' => '0',
                'acl_check' => 'ErrorLogViewer,allowed',
                'path' => 'error-log-viewer',
                'title' => 'BOX_ERROR_LOG_VIEWER',
            ],
        ];
    }

    public static function getTranslationArray()
    {
        return [
            'admin/main' => [
                'BOX_ERROR_LOG_VIEWER' => 'Error Log Viewer',
//                    'IMAGE_CANCEL' => 'Cancel',
//                    'IMAGE_DOWNLOAD' => 'Download',
//                    'IMAGE_DELETE' => 'Delete',
//                    'IMAGE_VIEW' => 'View',
//                    'TEXT_YES' => 'Yes',
//                    'TEXT_SEARCH_BY' => 'Search by',
//                    'TEXT_DELETE_SELECTED' => 'Delete selected',
//                    'TEXT_DATE' => 'Date',
            ],
            'extensions/error-log-viewer' => [
                'EXT_ELV_HEADING_TITLE' => 'Error Log Viewer',

                'EXT_ELV_TABLE_FILENAME' => 'File',
                'EXT_ELV_TABLE_FILESIZE' => 'Size',
                'EXT_ELV_TABLE_LAST_MODIFIED' => 'Last Modified',

                'EXT_ELV_TEXT_CLEAR_ALL' => 'Delete all logs',
                'EXT_ELV_TEXT_BACKEND' => 'Backend',
                'EXT_ELV_TEXT_FRONTEND' => 'Frontend',
                'EXT_ELV_TEXT_CONSOLE' => 'Console',
                'EXT_ELV_TEXT_SOURCE' => 'Log source',
                'EXT_ELV_TEXT_LOCATION' => 'File location',
                'EXT_ELV_TEXT_FILENAME' => 'Log file',
                'EXT_ELV_TEXT_MODIFIED' => 'Last modified',
                'EXT_ELV_TEXT_INFO' => 'Log Info',
                'EXT_ELV_TEXT_VIEW_AS_TEXT' => 'View as text',
                'EXT_ELV_TEXT_DOWNLOAD_ALL_LOGS' => 'Download all logs (zip)',
                'EXT_ELV_TEXT_IP' => 'IP',
                'EXT_ELV_TEXT_ERROR_LEVEL' => 'Level',
                'EXT_ELV_TEXT_CATEGORY' => 'Category',
                'EXT_ELV_TEXT_LOG_POSITION_DATE' => 'Position & date',
                'EXT_ELV_TEXT_ERROR_DESCRIPTION' => 'Error description',

                'EXT_ELV_DELETE_INTRO' => 'Are you sure you want to delete file \"%s\"?',
                'EXT_ELV_DELETE_TITLE' => 'Delete file?',

                'EXT_ELV_DELETE_SELECTED_INTRO' => 'Are you sure you want to delete %s log file(s)?',
                'EXT_ELV_DELETE_SELECTED_TITLE' => 'Delete selected Logs?',

                'EXT_ELV_DELETE_ALL_INTRO' => 'Are you sure you want to delete all files?',
                'EXT_ELV_DELETE_ALL_TITLE' => 'Delete all logs?',

                'EXT_ELV_ERR_SOURCE' => '<span color="#FF0000">ERROR!<br>Incorrect source</span>',
                'EXT_ELV_ERR_FILE' => '<span color="#FF0000">ERROR!<br>Incorrect filename or file not exists!</span>',
                'EXT_ELV_ERR_NO_FILE_TO_DOWNLOAD' => 'No logs to download',
                'EXT_ELV_ERR_CREATE_ZIP' => 'Failure to create zip file. Error: <strong>%s</strong>',
                'EXT_ELV_ERR_CREATE_TMP' => 'Can not create temporary folder',
                'EXT_ELV_ERR_DELETE_OLD_ZIP' => 'Failure to remove old zip file(s). Error: <strong>%s</strong>',
                'EXT_ELV_ERR_REQUEST' => 'Request error!',
            ],
        ];
    }

    public static function getAclArray()
    {
        return ['default' => ['TEXT_SETTINGS', 'BOX_HEADING_TOOLS', 'BOX_ERROR_LOG_VIEWER']];
    }

    public static function getVersionHistory()
    {
        return [
            '1.0.4' => 'Align top left buttons',
            '1.0.3' => 'Fixed error with large file',
            '1.0.2' => 'Added Refresh button in main page',
            '1.0.1' => 'Fixed a bug where the "Details" button was not displayed in the right menu',
            '1.0.0' => 'changed as AppStore Extensions',
        ];
    }
}