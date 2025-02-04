<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = app(UserService::class);
    }

    public function testCreateUser_ShouldPersistUser()
    {
        $userData = [
            'name'     => 'Alice',
            'email'    => 'alice@example.com',
            'password' => 'secret',
        ];

        $user = $this->userService->create($userData);

        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'name'  => 'Alice',
            'email' => 'alice@example.com',
        ]);
        $this->assertNotNull($user->id);
    }

    public function testGetAll_ShouldReturnAllUsers()
    {
        User::factory()->count(3)->create();
        $users = $this->userService->getAll();
        $this->assertCount(3, $users);
    }

    public function testGetById_ShouldReturnCorrectUserOrNull()
    {
        $user = User::factory()->create(['name' => 'Bob']);
        $foundUser = $this->userService->getById($user->id);
        $this->assertNotNull($foundUser);
        $this->assertEquals('Bob', $foundUser->name);

        $notFound = $this->userService->getById(99999);
        $this->assertNull($notFound);
    }

    public function testUpdateUser_ShouldChangeAttributes()
    {
        $user = User::factory()->create(['email' => 'old@example.com']);
        $updatedUser = $this->userService->update($user->id, [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'password' => 'newsecret',
        ]);

        $this->assertEquals('New Name', $updatedUser->name);
        $this->assertEquals('new@example.com', $updatedUser->email);
    }

    public function testDeleteUser_ShouldRemoveUser()
    {
        $user = User::factory()->create();
        $result = $this->userService->delete($user->id);
        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function testDeleteUser_WithNonExistentId_ShouldReturnFalse()
    {
        $result = $this->userService->delete(99999);
        $this->assertFalse($result);
    }
}
