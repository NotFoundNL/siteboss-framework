<?php

namespace NotFound\Framework\View\Components;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\Assert;
use Illuminate\View\Component;
use NotFound\Framework\Services\ClamAV\ClamAV;

class ConfigurationCheck extends Component
{
    public function __construct()
    {
    }

    public function render()
    {
        return view('siteboss::configuration.configuration-check', [
            'clamavTest' => $this->clamavTest(),
            'memcacheTest' => $this->memchacheTest(),
            'indexerTest' => $this->indexerTest(),
            'emailTest' => $this->emailTest(),
            'envTest' => $this->envTest(),
        ]);
    }

    private const debug = false;

    private function clamavTest()
    {
        $result = 'Geslaagd';
        $clamav = true;
        try {
            $clamav = ClamAV::uploadIsClean(public_path('index.php'));
        } catch (Exception $e) {
            $result = (ConfigurationCheck::debug) ? $e : 'Clamav verkeerd geconfigureerd (huidig socket type: '.config('clamav.socket_type').')';
        }
        if (! $clamav) {
            $result = 'Virus detected';
        }

        return $result;
    }

    private function memchacheTest()
    {
        $result = 'Geslaagd';
        if (config('cache.default') == 'memcached') {
            try {
                Cache::put('test123', 'test123');

                Assert::assertEquals(Cache::get('test123'), 'test123');

                Cache::forget('test123');
            } catch (Exception $e) {
                $result = (ConfigurationCheck::debug) ? $e : 'Cache niet goed geconfigureerd.';
            }
        } else {
            $result = 'memcached niet default';
        }

        return $result;
    }

    private function indexerTest()
    {
        $result = 'Geslaagd';
        try {
            Assert::assertEquals(Artisan::call('siteboss:indexSite'), Command::SUCCESS);
        } catch (Exception $e) {
            $result = (ConfigurationCheck::debug) ? $e : 'Mislukt';
        }

        return $result;
    }

    private function emailTest()
    {
        $result = 'Geslaagd';
        try {
            Mail::getSymfonyTransport()->start();
        } catch (Exception $e) {
            $result = (ConfigurationCheck::debug) ? $e : 'Mail niet correct geconfigureerd';
        }

        return $result;
    }

    private function envTest()
    {
        $result = '';

        $envCheck = ['OIDC_CONFIGURATION_URL', 'OIDC_CLIENT_ID', 'OIDC_ISSUER', 'SB_ADMIN_EMAIL', 'MAIL_PASSWORD'];

        foreach ($envCheck as $env) {
            if (env($env) == '') {
                $result .= $env.', ';
            }
        }

        $result = ($result == '') ? 'Geslaagd' : 'De volgende env velden zijn niet ingevuld: '.rtrim($result, ', ').'.';

        return $result;
    }
}
