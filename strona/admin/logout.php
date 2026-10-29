<?php
require __DIR__ . '/inc/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    sprawdz_csrf();
    $_SESSION = [];
    session_destroy();
}
przekieruj('login.php');
