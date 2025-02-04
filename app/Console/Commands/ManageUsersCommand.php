<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\UserService;

/**
 * Пример artisan-команды для управления пользователями:
 * php artisan user:manage --action=create --name=John --email=john@example.com --password=secret
 */
class ManageUsersCommand extends Command
{
    protected $signature = 'user:manage
                            {--action= : Действие (create, list, show, update, delete)}
                            {--user_id= : ID пользователя (для show/update/delete)}
                            {--name= : Имя пользователя (для create/update)}
                            {--email= : E-mail пользователя (для create/update)}
                            {--password= : Пароль пользователя (для create/update)}';

    protected $description = 'Управление пользователями через консоль';

    protected UserService $userManagementService;

    public function __construct(UserService $userManagementService)
    {
        parent::__construct();
        $this->userManagementService = $userManagementService;
    }

    public function handle()
    {
        $action = $this->option('action');

        switch ($action) {
            case 'create':
                $this->createUser();
                break;
            case 'list':
                $this->listUsers();
                break;
            case 'show':
                $this->showUser();
                break;
            case 'update':
                $this->updateUser();
                break;
            case 'delete':
                $this->deleteUser();
                break;
            default:
                $this->error('Неизвестное действие. Доступные действия: create, list, show, update, delete.');
        }

        return 0;
    }

    private function createUser()
    {
        $name = $this->option('name');
        $email = $this->option('email');
        $password = $this->option('password');

        if (!$name || !$email || !$password) {
            $this->error('Для создания пользователя необходимо указать name, email и password.');
            return;
        }

        $user = $this->userManagementService->create([
            'name'     => $name,
            'email'    => $email,
            'password' => $password,
        ]);

        $this->info("Пользователь {$user->name} (ID {$user->id}) успешно создан.");
    }

    private function listUsers()
    {
        $users = $this->userManagementService->getAll();

        if ($users->isEmpty()) {
            $this->info('Пока нет ни одного пользователя.');
            return;
        }

        foreach ($users as $user) {
            $this->line("ID: {$user->id}; Name: {$user->name}; Email: {$user->email}");
        }
    }

    private function showUser()
    {
        $userId = $this->option('user_id');

        if (!$userId) {
            $this->error('Для команды show необходимо указать --user_id');
            return;
        }

        $user = $this->userManagementService->getById($userId);

        if (!$user) {
            $this->error("Пользователь с ID {$userId} не найден.");
            return;
        }

        $this->info("Данные пользователя: ID: {$user->id}, Name: {$user->name}, Email: {$user->email}");
    }

    private function updateUser()
    {
        $userId = $this->option('user_id');
        if (!$userId) {
            $this->error('Для команды update необходимо указать --user_id');
            return;
        }

        $updates = [];
        if ($this->option('name')) {
            $updates['name'] = $this->option('name');
        }
        if ($this->option('email')) {
            $updates['email'] = $this->option('email');
        }
        if ($this->option('password')) {
            $updates['password'] = $this->option('password');
        }

        if (empty($updates)) {
            $this->error('Не указаны поля для обновления. Доступно: --name, --email, --password.');
            return;
        }

        $user = $this->userManagementService->update($userId, $updates);

        if (!$user) {
            $this->error("Пользователь с ID {$userId} не найден.");
            return;
        }

        $this->info("Пользователь (ID: {$user->id}) обновлён.");
    }

    private function deleteUser()
    {
        $userId = $this->option('user_id');

        if (!$userId) {
            $this->error('Для команды delete необходимо указать --user_id');
            return;
        }

        $deleted = $this->userManagementService->delete($userId);

        if (!$deleted) {
            $this->error("Не удалось удалить пользователя с ID {$userId}. Возможно, он не существует.");
            return;
        }

        $this->info("Пользователь (ID: {$userId}) удалён.");
    }
}
