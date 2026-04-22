<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesSsgData;
use Tests\TestCase;

class PortalAccessTest extends TestCase
{
    use CreatesSsgData;
    use RefreshDatabase;

    public function test_officer_can_access_officer_portal_but_not_admin_portal(): void
    {
        $officer = $this->createUserWithRole(User::ROLE_OFFICER);

        $this->actingAs($officer)
            ->get(route('officer.dashboard'))
            ->assertOk();

        $this->actingAs($officer)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_cannot_use_officer_only_portal(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);

        $this->actingAs($admin)
            ->get(route('officer.dashboard'))
            ->assertForbidden();
    }
}
