<?php

namespace Adldap\Laravel\Tests;

use Adldap\Connections\Ldap;
use Adldap\Schemas\ActiveDirectory;
use Adldap\Laravel\Tests\Models\TestUser;
use Adldap\Laravel\Auth\DatabaseUserProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseTestCase extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        // Create the users table for testing
        Schema::create('users', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password', 60);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Hash::setRounds(4);
    }

    /**
     * Define the environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetup($app)
    {
        // Laravel database setup.
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Adldap connection setup.
        $app['config']->set('adldap.connections.default.auto_connect', false);
        $app['config']->set('adldap.connections.default.connection', Ldap::class);
        $app['config']->set('adldap.connections.default.settings', [
            'username' => 'admin@email.com',
            'password' => 'password',
            'schema' => ActiveDirectory::class,
        ]);

        // Adldap auth setup.
        $app['config']->set('adldap_auth.provider', DatabaseUserProvider::class);

        // Laravel auth setup.
        $app['config']->set('auth.guards.web.provider', 'adldap');
        $app['config']->set('auth.providers', [
            'adldap' => [
                'driver' => 'adldap',
                'model'  => TestUser::class,
            ],
            'users'  => [
                'driver' => 'eloquent',
                'model'  => TestUser::class,
            ],
        ]);
    }
}
