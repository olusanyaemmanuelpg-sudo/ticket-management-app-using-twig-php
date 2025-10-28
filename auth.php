<?php
require_once '../vendor/autoload.php';
require_once 'auth.php';

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

$page = $_GET['page'] ?? 'landingpage';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($page === 'login') {
    $result = login($_POST['email'], $_POST['password']);
    if ($result['success']) {
      header('Location: ?page=dashboard');
      exit;
    } else {
      echo $twig->render('login.twig', ['error' => $result['error']]);
      exit;
    }
  }

  if ($page === 'signin') {
    $result = signup($_POST['name'], $_POST['email'], $_POST['password']);
    header('Location: ?page=dashboard');
    exit;
  }

  if ($page === 'logout') {
    logout();
    header('Location: ?page=login');
    exit;
  }
}

$user = getCurrentUser();

switch ($page) {
  case 'login':
    echo $twig->render('login.twig', ['user' => $user]);
    break;
  case 'signin':
    echo $twig->render('signin.twig', ['user' => $user]);
    break;
  case 'dashboard':
    if (!$user) {
      header('Location: ?page=login');
      exit;
    }
    echo $twig->render('dashboard.twig', ['user' => $user]);
    break;
  default:
    echo $twig->render('landingpage.twig', ['user' => $user]);
}