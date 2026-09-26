<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordEnforceTest extends TestCase
{
    use RefreshDatabase;

    public function test_mandatory_password_change_page_is_displayed_and_contains_validation_logic(): void
    {
        $user = User::factory()->create([
            'require_password_change' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/security/password-change');

        $response->assertOk();
        $response->assertSee('Password Change Required');
        $response->assertSee('isValid');
        $response->assertSee(':disabled="!isValid"', false);
    }
}
