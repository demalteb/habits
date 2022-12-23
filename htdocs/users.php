<?php

require_once(dirname(__FILE__) . '/local.php');

$db = connectDb(config()->db);

$action = $_REQUEST['action'] ?? 'list';

try {
    switch ($action) {
    case 'list':
        $ur = \model\EntityRepository::byName($db, 'user');
        $users = $ur->findAllEntities();
        $template = new \ui\Template('site', ['template'=> 'user_list', 'templateData' => $users, 'pageTitle' => 'Users']);
        $template->display();
        break;
    case 'edit':
        $ur = \model\EntityRepository::byName($db, 'user');
        if (isset($_POST['save'])) {
            $user = userFromRequest();
            $user->setName($_POST['name']);
            $user->setLogin($_POST['login']);
            if (isset($_POST['password']) && strlen($_POST['password']) > 0) {
                if ($_POST['password'] !== $_POST['password2']) {
                    throw new \Exception('passwords need to match');
                }
                $user->setPasswordFromCleartext($_POST['password']);
            }
            $user->save();
            header('Location: users.php');
            die;
        } else {
            $user = userFromRequest();
            $template = new \ui\Template('site', ['template'=> 'user_edit', 'templateData' => ['user' => $user, 'action' => 'edit'], 'pageTitle' => $user->getName() . ' - user']);
            $template->display();
        }
        break;
    case 'save_opened_habits':
        $user = getUser($db);
        $opened_habits = (string)$_REQUEST['opened_habits'];
        $user->setOpenedHabits($opened_habits);
        $user->save();
        die('{"status":"ok"}');
        break;
    default:
        throw new \Exception('no such action');
    }
} catch(\Throwable $e) {
    echo "ERRROR";

    var_dump($e);
}

function userFromRequest() {
    global $db;
    $ur = \model\EntityRepository::byName($db, 'user');
    if (isset($_REQUEST['id'])) {
        $id = (int)$_REQUEST['id'];
        $user = $ur->loadEntityById($id);
    } else {
        $user = new \model\User($db);
    }
    return $user;
}

