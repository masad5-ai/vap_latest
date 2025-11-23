<?php
require_once __DIR__ . '/../../src/helpers.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/products.php';
require_once __DIR__ . '/../../src/cart.php';
require_once __DIR__ . '/../../src/orders.php';
require_once __DIR__ . '/../../src/settings.php';

ensure_session_started();
$config = load_config();
$settings = load_settings();
$user = current_user();
