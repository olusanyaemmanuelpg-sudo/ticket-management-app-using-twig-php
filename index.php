<?php
echo "PHP is working";
require_once 'vendor/autoload.php';
require_once __DIR__ . '/auth.php';


$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
$twig   = new \Twig\Environment($loader, ['cache' => false]);

if (!isset($_SESSION['tickets'])) {
    $_SESSION['tickets'] = [
        [
            'id'          => 1,
            'title'       => 'Fix login bug',
            'status'      => 'open',
            'description' => 'Users cannot log in',
            'priority'    => 'high',
            'createdAt'   => time(),
        ],
        [
            'id'          => 2,
            'title'       => 'Update docs',
            'status'      => 'in_progress',
            'description' => 'Need to update API docs',
            'priority'    => 'medium',
            'createdAt'   => time(),
        ],
    ];
}
$tickets = &$_SESSION['tickets']; 

$features = [
    ['title' => 'Real-time Updates',   'desc' => 'Get instant notifications on ticket status changes'],
    ['title' => 'Team Collaboration', 'desc' => 'Work together seamlessly with your team'],
    ['title' => 'Advanced Filtering', 'desc' => 'Find tickets quickly with powerful search'],
];

$user = getCurrentUser();
$page = $_GET['page'] ?? 'landingpage';

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
            }
        }

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
        logout(); // destroys session

        session_start(); // NEW SESSION
        $_SESSION['toast'] = ['message' => 'Logged out successfully!', 'type' => 'success'];
        header('Location: ?page=login');
        exit;
    }
}

$toast = $_SESSION['toast'] ?? null;
unset($_SESSION['toast']);

switch ($page) {

    case 'login':
        echo $twig->render('login.twig', ['user' => $user, 'toast' => $toast]);
        break;

    case 'signin':
        echo $twig->render('signin.twig', [
            'user' => $user, 'toast' => $toast,
            'errors' => [], 'name' => '', 'email' => '', 'password' => '', 'show_password' => false
        ]);
        break;

    case 'dashboard':
        if (!$user) { header('Location: ?page=login'); exit; }

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
    if (!$user) { header('Location: ?page=login'); exit; }

    // Default state
    $show_form   = false;
    $editing_id  = null;
    $form        = ['title' => '', 'status' => 'open', 'description' => ''];
    $errors      = [];

    $status_colors = [
        'open'        => '#10b981',
        'in_progress' => '#f59e0b',
        'closed'      => '#6b7280'
    ];

    $action = $_GET['action'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // TOGGLE FORM (New or Cancel)
        if ($action === 'toggle_form') {
            $show_form = true;
            $editing_id = $_POST['editing_id'] ?? null;

            if ($editing_id) {
                foreach ($tickets as $t) {
                    if ($t['id'] == $editing_id) {
                        $form = [
                            'title'       => $t['title'],
                            'status'      => $t['status'],
                            'description' => $t['description']
                        ];
                        break;
                    }
                }
            } else {
                $form = ['title' => '', 'status' => 'open', 'description' => ''];
                
            }
        }

        // SAVE
        elseif ($action === 'save') {
            $id          = $_POST['id'] ?? null;
            $title       = trim($_POST['title'] ?? '');
            $status      = $_POST['status'] ?? 'open';
            $description = trim($_POST['description'] ?? '');

            if (!$title)       $errors['title']       = 'Title is required';
            if (!in_array($status, ['open', 'in_progress', 'closed'])) 
                               $errors['status']      = 'Invalid status';
            if (!$description) $errors['description'] = 'Description is required';

            if (!$errors) {
                if ($id) {
                    foreach ($tickets as &$t) {
                        if ($t['id'] == $id) {
                            $t['title']       = $title;
                            $t['status']      = $status;
                            $t['description'] = $description;
                            break;
                        }
                    }
                    $_SESSION['toast'] = ['message' => 'Ticket updated!', 'type' => 'success'];
                } else {
                    $tickets[] = [
                        'id'          => time() . rand(1, 999),
                        'title'       => $title,
                        'status'      => $status,
                        'description' => $description,
                        'createdAt'   => time()
                    ];
                    $_SESSION['toast'] = ['message' => 'Ticket created!', 'type' => 'success'];
                }
                header('Location: ?page=manage-tickets');
                exit;
            } else {
                // Keep form open with errors
                $show_form = true;
                $editing_id = $id;
                $form = ['title' => $title, 'status' => $status, 'description' => $description];
            }
        }

        // EDIT
        elseif ($action === 'edit') {
            $id = $_POST['id'] ?? null;
            if ($id) {
                foreach ($tickets as $t) {
                    if ($t['id'] == $id) {
                        $form = ['title' => $t['title'], 'status' => $t['status'], 'description' => $t['description']];
                        $editing_id = $id;
                        $show_form = true;
                        break;
                    }
                }
            }
        }

        // DELETE
        elseif ($action === 'delete') {
            $id = $_POST['id'] ?? null;
            if ($id) {
                $tickets = array_values(array_filter($tickets, fn($t) => $t['id'] != $id));
                $_SESSION['toast'] = ['message' => 'Ticket deleted!', 'type' => 'success'];
                header('Location: ?page=manage-tickets');
                exit;
            }
        }
    }

    // Render
    echo $twig->render('manage-tickets.twig', [
        'user'          => $user,
        'tickets'       => $tickets,
        'toast'         => $toast,
        'show_form'     => $show_form,
        'editing_id'    => $editing_id,
        'form'          => $form,
        'errors'        => $errors,
        'status_colors' => $status_colors
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