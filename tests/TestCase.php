<?php

namespace Tv2regionerne\StatamicPrivateApi\Tests;

use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Extend\Manifest;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Stache\Stores\UsersStore;
use Statamic\Statamic;
use Statamic\Testing\AddonTestCase;
use Tv2regionerne\StatamicPrivateApi\ServiceProvider;

abstract class TestCase extends AddonTestCase
{
    use PreventSavingStacheItemsToDisk, RefreshDatabase;

    protected string $addonServiceProvider = ServiceProvider::class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->runLaravelMigrations();

        \Facades\Statamic\Version::shouldReceive('get')->andReturn('4.0.0-testing');
        $this->addToAssertionCount(-1); // Dont want to assert this

        $this->preventSavingStacheItemsToDisk();
    }

    protected function tearDown(): void
    {
        $this->deleteFakeStacheDirectory();

        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [
            StatamicServiceProvider::class,
            ServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Statamic' => Statamic::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app->make(Manifest::class)->manifest = [
            'tv2regionerne/statamic-private-api' => [
                'id' => 'tv2regionerne/statamic-private-api',
                'namespace' => 'Tv2regionerne\\StatamicPrivateApi',
            ],
        ];
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('app.key', 'base64:'.base64_encode(
            Encrypter::generateKey($app['config']['app.cipher'])
        ));

        $configs = [
            'assets',
            'cp',
            'forms',
            'static_caching',
            //'sites',
            'stache',
            'system',
            'users',
        ];

        foreach ($configs as $config) {
            $app['config']->set(
                "statamic.$config",
                require(__DIR__."/../vendor/statamic/cms/config/{$config}.php")
            );
        }

        $app['config']->set('statamic.editions.pro', true);
        $app['config']->set('statamic.users.repository', 'file');

        $app['config']->set('statamic.stache.stores.users', [
            'class' => UsersStore::class,
            'directory' => __DIR__.'/__fixtures__/users',
        ]);

        $app['config']->set('private-api', require(__DIR__.'/../config/private-api.php'));
        $app['config']->set('private-api.enabled', true);
        $app['config']->set('private-api.middleware', 'web');
        $app['config']->set('private-api.resources', [
            'collections' => true,
            'navs' => true,
            'taxonomies' => true,
            'assets' => true,
            'globals' => true,
            'forms' => true,
            'users' => true,
        ]);

        $app['config']->set('app.debug', true);

        $app['config']->set('auth.providers.users.driver', 'statamic');
    }
}
