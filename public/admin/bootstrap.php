<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
$adminPage = $adminPage ?? 'dashboard';
$adminTitle = $adminTitle ?? 'Admin | ' . $settings['branding']['store_name'];
