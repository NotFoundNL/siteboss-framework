<?php

namespace NotFound\Framework\Services\Forms;

/**
 * This class converts the filestype from mimetypes to extensions and the other way around.
 * The $id is the database entry.
 */
class MimetypeConverter
{
    private static $options = [
        'images' => [
            'mime' => ['image/*'],
            'ext' => [
                'art', 'bm', 'bmp', 'dwg', 'dxf', 'fif', 'flo', 'fpx', 'g3', 'gif', 'ico', 'ief', 'iefs', 'jfif', 'jpe', 'jpeg', 'jpg', 'jut', 'mcf', 'nap', 'naplps',
                'nif', 'pbm', 'pct', 'pcx', 'pgm', 'pgm', 'pic', 'pict', 'pm', 'png', 'pnm', 'ppm', 'qif', 'qti', 'qtif', 'ras', 'rast', 'rf', 'rgb', 'rp', 'svf',
                'tif', 'tiff', 'turbot', 'wbmp', 'xbm', 'xif', 'xpm', 'x-png', 'xwd',
            ],
        ],

        'pdf' => [
            'mime' => ['application/pdf'],
            'ext' => ['pdf'],
        ],

        'documents' => [
            'mime' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.oasis.opendocument.text'],
            'ext' => ['doc', 'dot', 'docx', 'odt', 'pdf'],
        ],
        'spreadsheets' => [
            'mime' => ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.oasis.opendocument.spreadsheet'],
            'ext' => ['xls', 'xlt', 'xlsx', 'ods'],
        ],
        'presentations' => [
            'mime' => ['application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/vnd.oasis.opendocument.presentation'],
            'ext' => ['ppt', 'pot', 'pptx', 'odp'],
        ],
        'archives' => [
            'mime' => ['application/zip', 'application/x-rar-compressed', 'application/x-tar', 'application/x-gzip', 'application/x-7z-compressed'],
            'ext' => ['zip', 'rar', 'tar', 'gz', '7z'],
        ],

    ];

    public static function getMimetype($id, $returnAsString = false)
    {
        // TODO: check if this is needed because of a bug in formbuilder
        if (! $id) {
            return null;
        }

        if (isset(self::$options[$id])) {
            $return = self::$options[$id]['mime'];
            if ($returnAsString) {
                $return = implode(',', $return);
            }

            return $return;
        }

        return null;
    }

    public static function getExtension($id, $returnAsString = false)
    {
        if (isset(self::$options[$id])) {
            $return = self::$options[$id]['ext'];
            if ($returnAsString) {
                $return = implode(',', $return);
            }

            return $return;
        }

        return null;
    }
}
