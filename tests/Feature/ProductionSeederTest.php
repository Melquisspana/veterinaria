<?php

namespace Tests\Feature;

use App\Models\Owner;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_seed_creates_only_the_configured_admin(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        config(['vet.admin_email' => 'doctora@example.com', 'vet.admin_password' => 'clave-segura-123']);

        $this->app->make(DatabaseSeeder::class)->__invoke();

        $this->assertSame(['doctora@example.com'], User::pluck('email')->all());
        $this->assertTrue(Hash::check('clave-segura-123', User::sole()->password));
        $this->assertSame(0, Owner::count());
    }

    public function test_production_seed_without_credentials_creates_no_user(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        config(['vet.admin_email' => null, 'vet.admin_password' => null]);

        $this->app->make(DatabaseSeeder::class)->__invoke();

        $this->assertSame(0, User::count());
    }

    public function test_reseeding_does_not_overwrite_a_changed_password(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        config(['vet.admin_email' => 'doctora@example.com', 'vet.admin_password' => 'clave-inicial']);
        $this->app->make(DatabaseSeeder::class)->__invoke();
        User::sole()->update(['password' => 'clave-nueva']);

        $this->app->make(DatabaseSeeder::class)->__invoke();

        $this->assertTrue(Hash::check('clave-nueva', User::sole()->password));
    }
}
