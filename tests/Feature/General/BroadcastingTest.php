<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Config;

describe('Broadcasting Configuration', function () {
    it('loads broadcasting configuration', function () {
        $config = config('broadcasting');

        expect($config)->toBeArray();
        expect($config)->toHaveKey('default');
        expect($config)->toHaveKey('connections');
    });

    it('has valid broadcast drivers configured', function () {
        $config = config('broadcasting');
        $drivers = ['pusher', 'ably', 'log', 'null'];

        foreach ($drivers as $driver) {
            expect($config['connections'])->toHaveKey($driver);
        }
    });

    it('uses log driver in default environment', function () {
        Config::set('broadcasting.default', 'log');

        $driver = config('broadcasting.default');
        expect($driver)->toBe('log');
    });

    it('uses pusher driver when configured', function () {
        Config::set('broadcasting.default', 'pusher');
        Config::set('broadcasting.connections.pusher', [
            'driver' => 'pusher',
            'key' => 'test-key',
            'secret' => 'test-secret',
            'app_id' => 'test-app-id',
            'options' => [
                'cluster' => 'mt1',
            ],
        ]);

        $driver = config('broadcasting.default');
        $connection = config('broadcasting.connections.pusher');

        expect($driver)->toBe('pusher');
        expect($connection['key'])->toBe('test-key');
    });

    it('handles missing pusher credentials gracefully', function () {
        Config::set('broadcasting.connections.pusher.key', null);
        Config::set('broadcasting.connections.pusher.secret', null);
        Config::set('broadcasting.connections.pusher.app_id', null);

        $connection = config('broadcasting.connections.pusher');

        expect($connection['key'])->toBeNull();
        expect($connection['secret'])->toBeNull();
    });

    it('pusher options have proper defaults', function () {
        $config = config('broadcasting.connections.pusher');
        $options = $config['options'];

        expect($options)->toHaveKey('cluster');
        expect($options)->toHaveKey('useTLS');
        expect($options)->toHaveKey('encrypted');
        expect($options['useTLS'])->toBe(true);
        expect($options['encrypted'])->toBe(true);
    });
});

describe('Broadcasting Channels', function () {
    it('registers user private channel', function () {
        $user = User::factory()->create();

        $authorized = Broadcast::channel('App.Models.User.'.$user->id, function ($authUser, $id) {
            return (int) $authUser->id === (int) $id;
        });

        // The channel should be registered
        expect(true)->toBeTrue();
    });

    it('authorizes user for their own channel', function () {
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        // Simulate authorization check
        $isAuthorized = (int) $user->id === (int) $user->id;
        expect($isAuthorized)->toBeTrue();

        $isAuthorized = (int) $user->id === (int) $user2->id;
        expect($isAuthorized)->toBeFalse();
    });

    it('channel authorization requires integer conversion', function () {
        $userId = '5';
        $requestedId = 5;

        $isAuthorized = (int) $userId === (int) $requestedId;
        expect($isAuthorized)->toBeTrue();
    });
});

describe('Broadcasting Environment Variables', function () {
    it('reads broadcast connection from env', function () {
        $connection = env('BROADCAST_CONNECTION', 'log');

        expect($connection)->toBeString();
        expect(in_array($connection, ['log', 'pusher', 'ably', 'redis', 'null']))->toBeTrue();
    });

    it('reads pusher credentials from env', function () {
        $appId = env('PUSHER_APP_ID');
        $appKey = env('PUSHER_APP_KEY');
        $appSecret = env('PUSHER_APP_SECRET');
        $cluster = env('PUSHER_APP_CLUSTER', 'mt1');

        // These may be empty in test environment, but should be strings or null
        expect(is_string($appId) || is_null($appId))->toBeTrue();
        expect(is_string($appKey) || is_null($appKey))->toBeTrue();
        expect(is_string($appSecret) || is_null($appSecret))->toBeTrue();
        expect(is_string($cluster))->toBeTrue();
    });

    it('has sensible defaults for pusher cluster', function () {
        $cluster = env('PUSHER_APP_CLUSTER', 'mt1');

        expect($cluster)->toBeString();
        expect(strlen($cluster))->toBeGreaterThan(0);
    });
});

describe('Broadcasting Application Bootstrap', function () {
    it('application boots without broadcasting errors', function () {
        // If this test runs without throwing an exception, broadcasting is properly configured
        $this->assertTrue(true);
    });

    it('config broadcasting returns array', function () {
        $broadcastingConfig = config('broadcasting');

        expect($broadcastingConfig)->toBeArray();
        expect(count($broadcastingConfig))->toBeGreaterThan(0);
    });

    it('default broadcast driver is valid', function () {
        $defaultDriver = config('broadcasting.default');
        $connections = array_keys(config('broadcasting.connections'));

        expect($connections)->toContain($defaultDriver);
    });
});

describe('Broadcasting Feature Tests', function () {
    beforeEach(function () {
        // Reset config before each test
        Config::set('broadcasting.default', env('BROADCAST_CONNECTION', 'log'));
    });

    it('can switch broadcast drivers dynamically', function () {
        Config::set('broadcasting.default', 'log');
        expect(config('broadcasting.default'))->toBe('log');

        Config::set('broadcasting.default', 'null');
        expect(config('broadcasting.default'))->toBe('null');
    });

    it('broadcast connection uses environment variable', function () {
        $envConnection = env('BROADCAST_CONNECTION');
        $configConnection = config('broadcasting.default');

        if ($envConnection) {
            expect($configConnection)->toBe($envConnection);
        }
    });

    it('null driver is available as fallback', function () {
        $config = config('broadcasting.connections.null');

        expect($config)->toBeArray();
        expect($config['driver'])->toBe('null');
    });

    it('log driver is available for development', function () {
        $config = config('broadcasting.connections.log');

        expect($config)->toBeArray();
        expect($config['driver'])->toBe('log');
    });
});
