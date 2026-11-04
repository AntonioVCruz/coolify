<?php

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    seedBrowserResourceStack();
    $user = User::query()->where('email', 'test@example.com')->sole();
    $user->createToken('ci-token', ['read'], null, 0);
    $user->createToken('backup-token', ['read'], null, 0);
});

it('finds a token created without a page reload in the token search', function () {
    loginAndSkipBoarding();

    $page = visit('/security/api-tokens')
        ->assertSee('ci-token')
        ->click('button:visible:has-text("New token")')
        ->type('#api-token-create-form input[type="text"]', 'deploy-token')
        ->click('button:visible:has-text("Create token")')
        ->assertSee('Copy your token')
        ->click('button:visible:has-text("Done")')
        ->wait(1)
        ->fill('input[aria-label="Search tokens"]', 'deploy')
        ->wait(1)
        ->assertSee('deploy-token')
        ->assertDontSee('ci-token')
        ->assertDontSee('No matching tokens');

    expect(PersonalAccessToken::query()->count())->toBe(3);
    $page->screenshot(filename: 'api-token-search-after-create');
});
