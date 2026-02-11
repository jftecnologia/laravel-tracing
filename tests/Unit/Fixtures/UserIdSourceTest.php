<?php

declare(strict_types = 1);

use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Tests\Fixtures\UserIdSource;

describe('UserIdSource', function () {
    it('resolves authenticated user ID when user is logged in', function () {
        $source = new UserIdSource();

        // Create a mock user with ID
        $user = new User();
        $user->id = 42;

        // Create request with authenticated user
        $request = Request::create('/');
        $request->setUserResolver(fn () => $user);

        $result = $source->resolve($request);

        expect($result)->toBe('42');
    });

    it('returns guest when no user is authenticated', function () {
        $source = new UserIdSource();

        // Create request with no authenticated user
        $request = Request::create('/');
        $request->setUserResolver(fn () => null);

        $result = $source->resolve($request);

        expect($result)->toBe('guest');
    });

    it('returns guest when user resolver returns null', function () {
        $source = new UserIdSource();

        $request = Request::create('/');
        $request->setUserResolver(fn () => null);

        $result = $source->resolve($request);

        expect($result)->toBe('guest');
    });

    it('converts user ID to string', function () {
        $source = new UserIdSource();

        // Create user with integer ID
        $user = new User();
        $user->id = 123;

        $request = Request::create('/');
        $request->setUserResolver(fn () => $user);

        $result = $source->resolve($request);

        expect($result)->toBeString()
            ->and($result)->toBe('123');
    });

    it('handles string user IDs', function () {
        $source = new UserIdSource();

        // Create mock user object with string ID (simulating UUID primary keys)
        $user = new class
        {
            public string $id = 'user-uuid-123';
        };

        $request = Request::create('/');
        $request->setUserResolver(fn () => $user);

        $result = $source->resolve($request);

        expect($result)->toBe('user-uuid-123');
    });

    it('returns X-User-Id as header name', function () {
        $source = new UserIdSource();

        expect($source->headerName())->toBe('X-User-Id');
    });

    it('returns value unchanged in restoreFromJob()', function () {
        $source = new UserIdSource();

        $input = '42';

        expect($source->restoreFromJob($input))->toBe($input);
    });

    it('restores guest value from job', function () {
        $source = new UserIdSource();

        $input = 'guest';

        expect($source->restoreFromJob($input))->toBe('guest');
    });

    it('restores any user ID value from job unchanged', function () {
        $source = new UserIdSource();

        expect($source->restoreFromJob('123'))->toBe('123')
            ->and($source->restoreFromJob('user-uuid-abc'))->toBe('user-uuid-abc')
            ->and($source->restoreFromJob('guest'))->toBe('guest')
            ->and($source->restoreFromJob('admin-42'))->toBe('admin-42');
    });

    it('resolves different user IDs for different authenticated users', function () {
        $source = new UserIdSource();

        // User 1
        $user1 = new User();
        $user1->id = 10;

        $request1 = Request::create('/');
        $request1->setUserResolver(fn () => $user1);

        // User 2
        $user2 = new User();
        $user2->id = 20;

        $request2 = Request::create('/');
        $request2->setUserResolver(fn () => $user2);

        $result1 = $source->resolve($request1);
        $result2 = $source->resolve($request2);

        expect($result1)->toBe('10')
            ->and($result2)->toBe('20')
            ->and($result1)->not->toBe($result2);
    });

    it('returns consistent user ID for same authenticated user', function () {
        $source = new UserIdSource();

        $user = new User();
        $user->id = 42;

        $request = Request::create('/');
        $request->setUserResolver(fn () => $user);

        // Call resolve multiple times
        $result1 = $source->resolve($request);
        $result2 = $source->resolve($request);
        $result3 = $source->resolve($request);

        expect($result1)->toBe('42')
            ->and($result2)->toBe('42')
            ->and($result3)->toBe('42')
            ->and($result1)->toBe($result2)
            ->and($result2)->toBe($result3);
    });

    it('returns guest consistently when no user is authenticated', function () {
        $source = new UserIdSource();

        $request = Request::create('/');
        $request->setUserResolver(fn () => null);

        // Call resolve multiple times
        $result1 = $source->resolve($request);
        $result2 = $source->resolve($request);
        $result3 = $source->resolve($request);

        expect($result1)->toBe('guest')
            ->and($result2)->toBe('guest')
            ->and($result3)->toBe('guest');
    });
});
