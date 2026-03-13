<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class ApiTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_generate_api_token(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.docs.api.token'));

        $response->assertRedirect();
        $response->assertSessionHas('api_token');
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'admin-token',
        ]);
    }

    public function test_api_hits_are_tracked(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin-token')->plainTextToken;

        // Hit the API with the token
        $this->withHeaders(['Authorization' => 'Bearer ' . $token])
             ->getJson('/api/movies');

        $dbToken = PersonalAccessToken::findToken(explode('|', $token)[1]);
        $this->assertEquals(1, $dbToken->hits);

        // Hit again
        $this->withHeaders(['Authorization' => 'Bearer ' . $token])
             ->getJson('/api/movies');

        $this->assertEquals(2, $dbToken->fresh()->hits);
    }

    public function test_regenerating_token_revokes_old_one(): void
    {
        $user = User::factory()->create();
        $oldToken = $user->createToken('admin-token')->plainTextToken;

        $this->actingAs($user)->post(route('admin.docs.api.token'));

        $this->assertDatabaseMissing('personal_access_tokens', [
            'token' => hash('sha256', explode('|', $oldToken)[1]),
        ]);
        
        $this->assertEquals(1, $user->tokens()->count());
    }
}
