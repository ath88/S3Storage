<?php

namespace Modules\S3Storage\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

// Module alias.
define('S3_MODULE', 's3storage');

require_once __DIR__.'/../vendor/autoload.php';

class S3StorageServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->hooks();
    }

    /**
     * Module hooks.
     */
    public function hooks()
    {
        // Section settings
        \Eventy::addFilter('email.reply_to_customer.attach', function($attached, $message, $attachment, $thread_id) {

            if ($attached) {
                return $attached;
            }
            
            if (!$attachment->fileExists()) {
                \Log::error('[S3 Storage] ReplyToCustomer: For thread '.$thread_id.' attachment file not find in S3 storage: '.$attachment->getStorageFilePath());
                return false;
            }

            $stream = \Storage::readStream($attachment->getStorageFilePath());
            if (!is_resource($stream)) {
                \Log::error('[S3 Storage] ReplyToCustomer: For thread '.$thread_id.' could not open attachment stream: '.$attachment->getStorageFilePath());
                return false;
            }

            $temp_file = new \Swift_ByteStream_TemporaryFileByteStream();
            while (!feof($stream)) {
                $temp_file->write(fread($stream, 8192));
            }
            $temp_file->commit();
            fclose($stream);

            $swift_attachment = (new \Swift_Attachment())->setFile($temp_file, $attachment->mime_type);
            $swift_attachment->setFilename($attachment->file_name);

            // Attach file to mail.
            $message->withSwiftMessage(function ($swiftmessage) use ($swift_attachment) {
                $swiftmessage->attach($swift_attachment);
                return $swiftmessage;
            });

            return true;
        }, 20, 4);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerTranslations();
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('s3storage.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 's3storage'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/s3storage');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/s3storage';
        }, \Config::get('view.paths')), [$sourcePath]), 's3storage');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $this->loadJsonTranslationsFrom(__DIR__ .'/../Resources/lang');
    }

    /**
     * Register an additional directory of factories.
     * @source https://github.com/sebastiaanluca/laravel-resource-flow/blob/develop/src/Modules/ModuleServiceProvider.php#L66
     */
    public function registerFactories()
    {
        if (! app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/../Database/factories');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
