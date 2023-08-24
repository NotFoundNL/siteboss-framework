<?php

namespace NotFound\Framework\View\Components;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\Assert;
use Illuminate\View\Component;
use NotFound\Framework\Services\ClamAV\ClamAV;
use NotFound\Framework\Services\Indexer\IndexBuilderService;

class ConfigurationCheck extends Component
{
    private const debug = false;

    public function render()
    {
        return view('siteboss::configuration.configuration-check', ['tests' => [
            $this->testResult('ClamAV', [$this, 'clamavTest']),
            $this->testResult('MemCached', [$this, 'memcacheTest']),
            $this->testResult('Indexer', [$this, 'indexerTest']),
            $this->testResult('E-mail', [$this, 'emailTest']),
            $this->testResult('Configuration .env', [$this, 'envTest']),
        ]]);
    }

    private function testResult(string $name, callable $testMethod)
    {
        return (object) [
            'name' => $name,
            'result' => $testMethod(),
        ];
    }

    private function clamavTest(): bool|string
    {
        if (config('clamav.socket_type') === 'none') {
            return 'Clamav is disabled in .env';
        }
        $clamav = true;
        try {
            $clamav = ClamAV::uploadIsClean(public_path('index.php'));
        } catch (Exception $e) {
            return (ConfigurationCheck::debug) ? $e : 'Clamav configuration error (socket type: '.config('clamav.socket_type').')';
        }
        if (! $clamav) {
            return 'Virus detected';
        }

        return true;
    }

    private function memcacheTest(): bool|string
    {
        if (config('cache.default') == 'memcached') {
            try {
                Cache::put('test123', 'test123');

                Assert::assertEquals(Cache::get('test123'), 'test123');

                Cache::forget('test123');
            } catch (Exception $e) {
                return (ConfigurationCheck::debug) ? $e : 'Cache configuration error';
            }
        } else {
            return 'memcached disabled in .env';
        }

        return true;
    }

    private function indexerTest(): bool|string
    {
        $result = true;
        try {
            //     $indexer = new IndexBuilderService();
            //  $indexer->checkConnection();
        } catch (Exception $e) {
            $result = (ConfigurationCheck::debug) ? $e : 'Failed to connect to indexer';
        }

        return $result;
    }

    private function emailTest(): bool|string
    {
        try {
            Mail::getSymfonyTransport()->start();
        } catch (Exception $e) {
            return (ConfigurationCheck::debug) ? $e : 'Mail configuration error';
        }

        return true;
    }

    private function envTest(): bool|string
    {
        $result = [];

        $envCheck = ['OIDC_CONFIGURATION_URL', 'OIDC_CLIENT_ID', 'OIDC_ISSUER', 'SB_ADMIN_EMAIL', 'MAIL_PASSWORD'];

        foreach ($envCheck as $env) {
            if (env($env) == '') {
                $result[] = $env;
            }
        }
        if (count($result) === 0) {
            return true;
        }

        $result =
         'Missing: '.implode(', ', $result);

        return $result;
    }
}
