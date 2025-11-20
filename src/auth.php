<?php
require_once __DIR__ . '/helpers.php';

function users_path(): string
{
    return __DIR__ . '/../data/users.json';
}

function load_users(): array
{
    return load_json(users_path());
}

function save_users(array $users): void
{
    save_json(users_path(), $users);
}

function set_user_role(string $userId, string $role): array
{
    $users = load_users();
    foreach ($users as &$user) {
        if ($user['id'] === $userId) {
            $user['role'] = $role;
            save_users($users);
            return $user;
        }
    }
    throw new RuntimeException('User not found');
}

function register_user(string $name, string $email, string $password): array
{
    $users = load_users();
    foreach ($users as $user) {
        if (strcasecmp($user['email'], $email) === 0) {
            throw new RuntimeException('Email already registered');
        }
    }

    $user = [
        'id' => generate_id(),
        'name' => $name,
        'email' => strtolower($email),
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'role' => 'customer',
        'created_at' => date('c'),
    ];

    $users[] = $user;
    save_users($users);
    return $user;
}

function login_user(string $email, string $password): ?array
{
    ensure_session_started();
    $users = load_users();
    foreach ($users as $user) {
        if (strcasecmp($user['email'], $email) === 0 && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            return $user;
        }
    }
    return null;
}

function logout_user(): void
{
    ensure_session_started();
    unset($_SESSION['user']);
}

function current_user(): ?array
{
    ensure_session_started();
    return $_SESSION['user'] ?? null;
}
