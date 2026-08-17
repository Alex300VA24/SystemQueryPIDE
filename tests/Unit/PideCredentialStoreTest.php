<?php

namespace Tests\Unit;

use App\Services\Pide\PideCredentialStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PideCredentialStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_and_get_round_trip(): void
    {
        $store = app(PideCredentialStore::class);

        $store->store('mi-clave-pide');

        $this->assertTrue($store->has());
        $this->assertSame('mi-clave-pide', $store->get());
    }

    public function test_has_is_false_when_nothing_stored(): void
    {
        $store = app(PideCredentialStore::class);

        $this->assertFalse($store->has());
        $this->assertNull($store->get());
    }

    public function test_forget_removes_the_credential(): void
    {
        $store = app(PideCredentialStore::class);
        $store->store('mi-clave-pide');

        $store->forget();

        $this->assertFalse($store->has());
        $this->assertNull($store->get());
    }

    public function test_expired_credential_returns_null_and_is_cleared(): void
    {
        config(['pide.credential_ttl_minutes' => 15]);
        $store = app(PideCredentialStore::class);

        $store->store('mi-clave-pide');
        $this->travel(16)->minutes();

        $this->assertNull($store->get());
        $this->assertFalse($store->has());
    }

    public function test_credential_within_ttl_is_still_available(): void
    {
        config(['pide.credential_ttl_minutes' => 15]);
        $store = app(PideCredentialStore::class);

        $store->store('mi-clave-pide');
        $this->travel(10)->minutes();

        $this->assertSame('mi-clave-pide', $store->get());
    }
}
