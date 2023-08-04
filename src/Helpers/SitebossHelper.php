<?php

namespace NotFound\Framework\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NotFound\Framework\Models\CmsConfig;

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

    public static function mail(string $to_name, string $to_email, string $subject, $html, $data = false)
    {
        $sendgrid_api_key = self::config('sendgrid_api_key', true);
        $sendgrid_sender_email = self::config('sendgrid_sender_email', true);
        $sendgrid_sender_name = self::config('sendgrid_sender_name', true);

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom($sendgrid_sender_email, $sendgrid_sender_name);
        $email->setSubject($subject);

        if (app()->hasDebugModeEnabled()) {
            $email->addTo(\env('SB_ERROR_EMAIL', $to_email));
        } else {
            if (filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
                $email->addTo($to_email, $to_name);
            } else {
                return 400;
            }
        }

        // if ($data != false) {
        //     // HTML is a Twig template with data
        //     if(!site::$page->twig)
        //     {
        //         site::$page->getTwig();
        //     }
        //     $html = site::$page->twig->render($html, $data);
        // }
        $email->addContent('text/html', $html);
        $sendgrid = new \SendGrid($sendgrid_api_key);
        try {
            $response = $sendgrid->send($email);

            return $response->statusCode();
        } catch (\Exception $e) {
            echo 'Caught exception: '.$e->getMessage()."\n";
        }
    }

    public static function makeDirectory($root, $dir): bool
    {
        $dir = Str::lower($dir);
        if (substr($dir, 0, 1) !== '/') {
            $dir = '/'.$dir;
        }

        if (! is_dir($root)) {
            // Root folder must exist!
            Log::error('[makeDirIfNotExist] Root directory does not exist: '.$root);

            return false;
        }
        // No sneaky going up paths
        if (str_contains($dir, '..')) {
            Log::error('[makeDirIfNotExist] Directory contains ..: '.$dir);

            return false;
        }
        if (is_dir($root.$dir)) {
            // All set
            return true;
        } else {
            // Directory does not exist, so lets check the parent directory
            $parentDir = dirname($dir);
            if (! is_dir($root.$parentDir)) {
                // Parent directory does not exist, so lets create it
                make_directories($root, $parentDir);
            }
        }
        if (! mkdir($root.$dir)) {
            Log::error('[makeDirIfNotExist] Permission denied: '.$dir);

            return false;
        }

        return true;
    }
}
