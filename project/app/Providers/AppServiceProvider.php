<?php

namespace App\Providers;

use App\Services\Biometric\BiometricRegistrar;
use App\Services\Biometric\CaptureDriver;
use App\Services\Biometric\PythonCameraDriver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bind(CaptureDriver::class, function () {
            return new PythonCameraDriver(
                baseUrl:        config('services.freqid_scanner.url', 'http://localhost:8001'),
                timeoutSeconds: config('services.freqid_scanner.timeout', 10),
            );
        });

        $this->app->bind(BiometricRegistrar::class, function ($app) {
            return new BiometricRegistrar(
                driver: $app->make(CaptureDriver::class),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
