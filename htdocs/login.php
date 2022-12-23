<?php

require_once(dirname(__FILE__) . '/local.php');

$db = connectDb(config()->db);

$action = $_REQUEST['action'] ?? 'login';

try {
    switch ($action) {
    case 'login':
        if (isset($_POST['login'])) {
            $ur = \model\EntityRepository::byName($db, 'user');
            $user = $ur->findEntityByLoginAndCleartextPassword($_POST['login'], $_POST['password']);
            if ($user) {
                $_SESSION['user_id'] = $user->getId();
                header('Location: habits.php');
            } else {
                $template = new \ui\Template('site', ['template'=> 'login', 'pageTitle' => 'Login']);
                $template->display();
            }
            die;
        } else {
            $template = new \ui\Template('site', ['template'=> 'login', 'pageTitle' => 'Login']);
            $template->display();
        }
        break;
    default:
        throw new \Exception('no such action');
    }
} catch(\Throwable $e) {
    $template = new \ui\Template('site', ['template'=> 'login', 'pageTitle' => 'Login', 'errors' => [ $e->getMessage() ]]);
    $template->display();
}
