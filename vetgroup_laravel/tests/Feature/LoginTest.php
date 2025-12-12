<?php

namespace Tests\Feature;

use App\Models\VetgroupUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_jwt_and_document_id(): void
    {
        VetgroupUser::query()->create([
            'email' => 'test@test.com',
            'username' => 'test',
            'password' => Hash::make('test1234'),
            'user_id' => 'doc_123',
            'code' => 'CODE1',
            'company' => 'Company 1',
        ]);

        $response = $this->postJson('/api/login', [
            'identifier' => 'test@test.com',
            'password' => 'test1234',
        ]);

        $response->assertOk();
        $response->assertJsonPath('jwt', fn ($value) => is_string($value) && $value !== '');
        $response->assertJsonPath('documentId', 'doc_123');
        $response->assertJsonPath('user.documentId', 'doc_123');
    }
}

