<?php
require_once 'vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
$twig = new \Twig\Environment($loader);

echo $twig->render('hello.twig', [
    'name' => 'Ticket App User',
    'message' => 'Welcome to your Twig-powered ticket app!'
]);