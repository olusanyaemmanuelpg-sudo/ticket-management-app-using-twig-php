<?php
require_once 'vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
$twig = new \Twig\Environment($loader);

// Features data
$featuresDescription = [
    [
        'title' => 'Real-time Updates',
        'desc' => 'Get instant notifications on ticket status changes'
    ],
    [
        'title' => 'Team Collaboration',
        'desc' => 'Work together seamlessly with your team'
    ],
    [
        'title' => 'Advanced Filtering',
        'desc' => 'Find tickets quickly with powerful search'
    ]
];

echo $twig->render('landing_page.twig', [
    'features' => $featuresDescription
]);