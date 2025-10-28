<?php
require_once 'vendor/autoload.php';
require_once __DIR__ . '/auth.php';

session_start();

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
$twig   = new \Twig\Environment($loader, ['cache' => false]);

/* ------------------------------------------------------------------
   MOCK DATA
------------------------------------------------------------------ */
$tickets = [[
			'id' => 1,
			'title' => 'Fix login bug',
			'status' => 'open',
			'description' =>  'Users cannot log in',
			'priority' =>  'high',
			'createdAt' =>  time(),
],
		[
			'id' =>  2,
			'title' => 'Update docs',
			'status' => 'in_progress',
			'description' =>  'Need to update API docs',
			'priority' => 'medium',
			'createdAt' =>  time(),
        ], ];
$features = [ [
			'title' =>  'Real-time Updates',
			'desc' =>  'Get instant notifications on ticket status changes',
],
		[
			'title' => 'Team Collaboration',
			'desc' =>  'Work together seamlessly with your team',
        ],
		[
			'title' =>  'Advanced Filtering',
			'desc' =>  'Find tickets quickly with powerful search',
        ],];

/* ------------------------------------------------------------------
   HELPERS
------------------------------------------------------------------ */
$user = getCurrentUser();
$page = $_GET['page'] ?? 'landingpage';

/* ------------------------------------------------------------------
   POST HANDLING (login / signup / logout)
------------------------------------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ---------- LOGIN ----------
    if ($page === 'login') {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors   = [];

        if (!$email)    $errors['email']    = 'Email is required';
        if (!$password) $errors['password'] = 'Password is required';

        if (!$errors) {
            $result = login($email, $password);
            if ($result['success']) {
                $_SESSION['toast'] = ['message' => 'Login successful!', 'type' => 'success'];
                header('Location: ?page=dashboard');
                exit;
            } else {
                $_SESSION['toast'] = ['message' => $result['error'], 'type' => 'error'];
            }
        }

        // Render login with errors
        $toast = $_SESSION['toast'] ?? null;
        unset($_SESSION['toast']);

        echo $twig->render('login.twig', [
            'user'   => $user,
            'toast'  => $toast,
            'errors' => $errors,
            'email'  => $email,
        ]);
        exit;
    }

    // ---------- SIGNUP ----------
    if ($page === 'signin') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $errors   = [];

    if (!$name)     $errors['name']     = 'Full Name is required';
    if (!$email)    $errors['email']    = 'Email is required';
    if (!$password) $errors['password'] = 'Password is required';

    if (!$errors) {
        $result = signup($name, $email, $password);
        if ($result['success']) {
            $_SESSION['toast'] = ['message' => 'Account created successfully!', 'type' => 'success'];
            header('Location: ?page=dashboard');
            exit;
        } else {
            $_SESSION['toast'] = ['message' => $result['error'] ?? 'Signup failed', 'type' => 'error'];
        }
    }

    // Re-render with errors
    echo $twig->render('signin.twig', [
        'user'     => $user,
        'toast'    => $_SESSION['toast'] ?? null,
        'errors'   => $errors,
        'name'     => $name,
        'email'    => $email,
        'password' => $password,
        'show_password' => false
    ]);
    unset($_SESSION['toast']);
    exit;
}

    // ---------- LOGOUT ----------
    if ($page === 'logout') {
        logout();
        $_SESSION['toast'] = ['message' => 'Logged out successfully', 'type' => 'success'];
        header('Location: ?page=login');
        exit;
    }
}

/* ------------------------------------------------------------------
   READ TOAST ONLY NOW — AFTER ALL REDIRECTS
------------------------------------------------------------------ */
$toast = $_SESSION['toast'] ?? null;
unset($_SESSION['toast']);  // Clear so it shows only once

/* ------------------------------------------------------------------
   PAGE ROUTING
------------------------------------------------------------------ */
switch ($page) {

    case 'login':
        echo $twig->render('login.twig', [
            'user'  => $user,
            'toast' => $toast
        ]);
        break;

    case 'signin':
        echo $twig->render('signin.twig', [
            'user'  => $user,
            'toast' => $toast,
            'errors' => [],
            'name' => '',
            'email' => '',
            'password' => '',
            'show_password' => false,
        ]);
        break;

    case 'dashboard':
        if (!$user) {
            header('Location: ?page=login');
            exit;
        }

        $stats = [
            'total'       => count($tickets),
            'open'        => count(array_filter($tickets, fn($t) => $t['status'] === 'open')),
            'in_progress' => count(array_filter($tickets, fn($t) => $t['status'] === 'in_progress')),
            'closed'      => count(array_filter($tickets, fn($t) => $t['status'] === 'closed')),
        ];

        echo $twig->render('dashboard.twig', [
            'user'    => $user,
            'tickets' => $tickets,
            'stats'   => $stats,
            'toast'   => $toast
        ]);
        break;

    case 'manage-tickets':
        if (!$user) {
            header('Location: ?page=login');
            exit;
        }
        echo $twig->render('manage-tickets.twig', [
            'user'    => $user,
            'tickets' => $tickets,
            'toast'   => $toast
        ]);
        break;

    default:
        echo $twig->render('landing_page.twig', [
            'user'     => $user,
            'features' => $features,
            'toast'    => $toast
        ]);
        break;
}