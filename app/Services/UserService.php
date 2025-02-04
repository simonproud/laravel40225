<?php

namespace App\Services;

use App\Events\UserAdded;
use App\Events\UserDeleting;
use App\Events\UserUpdated;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;

class UserService
{

    public function create(array $data): User
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        event(new UserAdded($user));

        return $user;
    }

    /**
     * Получить список всех пользователей
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return User::all();
    }

    /**
     * Получить пользователя по ID
     */
    public function getById(int $userId): ?User
    {
        return User::find($userId);
    }

    /**
     * Обновить данные пользователя
     */
    public function update(int $userId, array $data): ?User
    {
        $user = User::find($userId);
        if (!$user) {
            return null;
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        event(new UserUpdated($user));

        return $user;
    }

    /**
     * Удалить пользователя
     */
    public function delete(int $userId): bool
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        event(new UserDeleting($user));

        return (bool) $user->delete();
    }
}
