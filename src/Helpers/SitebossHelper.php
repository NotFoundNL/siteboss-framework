<?php

namespace NotFound\Framework\Helpers;

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
            \env('SB_ERROR_EMAIL', $to_email);
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
}
