<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_routes_are_accessible_by_authenticated_user(): void
    {
        $user = User::factory()->create();

        $routes = [
            'admin.dashboard',
            'admin.docs.api',
            'admin.movies.index',
        ];

        foreach ($routes as $routeName) {
            $response = $this->actingAs($user)->get(route($routeName));
            $response->assertStatus(200);
        }
    }

    public function test_root_redirects_to_admin_dashboard(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('admin.dashboard'));
    }
}
