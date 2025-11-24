<?php
function load_config(): array
{
    static $config;
    if ($config === null) {
        $config = require __DIR__ . '/config.php';
    }
    return $config;
}

function load_json(string $path): array
{
    if (!file_exists($path)) {
        return [];
    }
    $content = file_get_contents($path);
    $decoded = json_decode($content, true);
    return is_array($decoded) ? $decoded : [];
}

function save_json(string $path, array $data): void
{
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
}

function generate_id(): string
{
    return bin2hex(random_bytes(8));
}

function ensure_session_started(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function require_admin(): void
{
    $user = current_user();
    if (!$user || ($user['role'] ?? 'customer') !== 'admin') {
        header('Location: /index.php');
        exit;
    }
}
