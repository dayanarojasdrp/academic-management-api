<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsAcademicScenarios;
use Tests\TestCase;

class LoginAndRolesTest extends TestCase
{
    use BuildsAcademicScenarios;
    use RefreshDatabase;

    public function test_login_returns_token_roles_and_permissions(): void
    {
        $user = $this->userWithPermissions(
            ['students.view', 'enrollments.create'],
            ['academic_secretary']
        );

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'phpunit',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', $user->email)
            ->assertJsonPath('user.roles.0', 'academic_secretary')
            ->assertJsonPath('user.permissions.0', 'students.view')
            ->assertJsonStructure([
                'access_token',
                'user' => ['id', 'name', 'email', 'roles', 'permissions'],
            ]);
    }
}
