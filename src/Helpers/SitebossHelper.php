<?php

namespace App\Helpers;

use App\Models\CmsConfig;

class SitebossHelper
{
    public static ?array $config = null;

    /**
     * config
     *
     * Returns a value from the cms_config table.
     *
     * @param  mixed  $code The internal code for the config value.
     * @param  mixed  $failOnMissing Whether to throw an exception if the config value is missing.
     * @return string|object|null The value of the config.
     */
    public static function config(string $code, bool $failOnMissing = true): string|object|null
    {
        if (is_null(self::$config)) {
            self::$config = CmsConfig::all()->keyBy('code')->toArray();
        }

        if (! isset(self::$config[$code])) {
            if ($failOnMissing) {
                throw new \Exception("Missing config code: {$code}");
            }

            return null;
        }

        if (self::$config[$code]['type'] === 2) {
            return json_decode(self::$config[$code]['value'], true);
        }

        return self::$config[$code]['value'];
    }
}
