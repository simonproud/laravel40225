<?php

namespace Tests\Unit\Console\Commands;

use Faker\Provider\en_US\Address;
use Tests\TestCase;

class ManageUsersCommandTest extends TestCase
{
    public function testHandle_WithUnknownAction_ShouldShowError()
    {
        $this->artisan('user:manage', [
            '--action' => 'unknown'
        ])
            ->expectsOutput('Неизвестное действие. Доступные действия: create, list, show, update, delete.')
            ->assertExitCode(0);
    }

    public function testHandle_CreateUser_WithAllRequiredFields_ShouldSucceed()
    {
        $this->artisan('user:manage', [
            '--action'   => 'create',
            '--name'     => Address::randomAscii(),
            '--email'    => Address::randomAscii().'@example.com',
            '--password' => 'secret'
        ])
            ->assertExitCode(0);
    }

    public function testHandle_CreateUser_MissingRequiredFields_ShouldFail()
    {
        $this->artisan('user:manage', [
            '--action' => 'create',
            '--name'   => 'John'
        ])
            ->expectsOutput('Для создания пользователя необходимо указать name, email и password.')
            ->assertExitCode(0);
    }

    public function testHandle_ShowUser_MissingUserId_ShouldFail()
    {
        $this->artisan('user:manage', [
            '--action' => 'show'
        ])
            ->expectsOutput('Для команды show необходимо указать --user_id')
            ->assertExitCode(0);
    }

    public function testHandle_ShowUser_NonExistent_ShouldFail()
    {
        $this->artisan('user:manage', [
            '--action' => 'show',
            '--user_id' => '9999'
        ])
            ->expectsOutput('Пользователь с ID 9999 не найден.')
            ->assertExitCode(0);
    }

    public function testHandle_UpdateUser_MissingUserId_ShouldFail()
    {
        $this->artisan('user:manage', [
            '--action' => 'update',
            '--name'   => 'NewName'
        ])
            ->expectsOutput('Для команды update необходимо указать --user_id')
            ->assertExitCode(0);
    }

    public function testHandle_UpdateUser_WithNoFieldsToUpdate_ShouldFail()
    {
        $this->artisan('user:manage', [
            '--action'  => 'update',
            '--user_id' => '1'
        ])
            ->expectsOutput('Не указаны поля для обновления. Доступно: --name, --email, --password.')
            ->assertExitCode(0);
    }

    public function testHandle_DeleteUser_MissingUserId_ShouldFail()
    {
        $this->artisan('user:manage', [
            '--action' => 'delete'
        ])
            ->expectsOutput('Для команды delete необходимо указать --user_id')
            ->assertExitCode(0);
    }

    public function testHandle_DeleteUser_NonExistent_ShouldFail()
    {
        $this->artisan('user:manage', [
            '--action'  => 'delete',
            '--user_id' => '9999'
        ])
            ->expectsOutput('Не удалось удалить пользователя с ID 9999. Возможно, он не существует.')
            ->assertExitCode(0);
    }
}
