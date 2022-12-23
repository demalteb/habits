<?php

require_once(dirname(__FILE__) . '/local.php');

$db = connectDb(config()->db);

$action = $_REQUEST['action'] ?? 'list';

try {
    switch ($action) {
    case 'list':
        $hr = \model\EntityRepository::byName($db, 'habit');
        $habits = $hr->findEntitiesByUser(getUser($db));
        $template = new \ui\Template('site', ['template'=> 'habit_list', 'templateData' => $habits, 'pageTitle' => 'Habits']);
        $template->display();
        break;
    case 'edit':
        try {
            $hr = \model\EntityRepository::byName($db, 'habit');
            if (isset($_POST['save'])) {
                $habit = habitFromRequest();
                $habit->setName($_POST['name']);
                $habit->setDescription(\Util::nullIfEmpty($_POST['description']));
                $habit->setStartDate(\Util::dateOrNull($_POST['start_date']));
                $habit->setEndDate(\Util::dateOrNull($_POST['end_date']));
                $habit->setIsFulfilmentRelative(\Util::intToBool($_POST['is_fulfilment_relative'] ?? 0));
                $habit->setFulfilmentUnit($_POST['fulfilment_unit']);
                $habit->setFulfilmentMax((int)$_POST['fulfilment_max']);
                $habit->save();

                if ( count(array_values(array_filter(array_keys($_POST), function($p) { return preg_match('#^weekday_#', $p); }))) === 7 ) {
                    for ($dayNum = 0; $dayNum < 7; ++$dayNum ) {
                        $habit->removeWeekDay($dayNum);
                    }
                } else {
                    for ($dayNum = 0; $dayNum < 7; ++$dayNum ) {
                        if ( isset($_POST['weekday_' . $dayNum]) ) {
                            $habit->createWeekDayIfNotExists($dayNum);
                        } else {
                            $habit->removeWeekDay($dayNum);
                        }
                    }
                }
                header('Location: habits.php');
                die;
            } else {
                $habit = habitFromRequest();
                $template = new \ui\Template('site', ['template'=> 'habit_edit', 'templateData' => ['habit' => $habit, 'action' => 'edit'], 'pageTitle' => $habit->getName() . ' - Habit']);
                $template->display();
            }
        } catch(\Throwable $e) {
            $habit = habitFromRequest();
            $template = new \ui\Template('site', ['template'=> 'habit_edit', 'pageTitle' => 'Habit', 'templateData' => ['habit' => $habit, 'action' => 'edit'], 'errors' => [ $e->getMessage() ]]);
            $template->display();
        }
        break;
    case 'delete':
        $rr = \model\EntityRepository::byName($db, 'habit');
        $habitId = (int)$_REQUEST['id'];
        $habit = $rr->loadEntityByUserAndId(getUser($db), $habitId);
        $rr->deleteEntityById($habitId);
        header('Location: habits.php');
        die;
        break;
    case 'create':
        if (isset($_POST['save'])) {
            try {
                $hr = \model\EntityRepository::byName($db, 'habit');
                $hr->createEntity(
                    getUser($db),
                    $_POST['name'],
                    \Util::nullIfEmpty($_POST['description']),
                    \Util::dateOrNull($_POST['start_date']),
                    \Util::dateOrNull($_POST['end_date']),
                    \Util::intToBool($_POST['is_fulfilment_relative'] ?? 0),
                    $_POST['fulfilment_unit'],
                    (int)$_POST['fulfilment_max']);
                header('Location: habits.php');
                die;
            } catch(\Throwable $e) {
                $habit = habitFromRequest();
                $template = new \ui\Template('site', ['template'=> 'habit_edit', 'templateData' => ['habit' => $habit, 'action' => 'create'], 'pageTitle' => $habit->getName() . ' - Habit', 'errors' => [ $e->getMessage() ]]);
                $template->display();
            }
            break;
        } else {
            try {
                $habit = habitFromRequest();
                $template = new \ui\Template('site', ['template'=> 'habit_edit', 'templateData' => ['habit' => $habit, 'action' => 'create'], 'pageTitle' => $habit->getName() . ' - Habit']);
                $template->display();
            } catch(\Throwable $e) {
                $template = new \ui\Template('site', ['template'=> 'habit_edit', 'templateData' => ['habit' => null, 'action' => 'create'], 'pageTitle' => $habit->getName() . ' - Habit', 'errors' => [ $e->getMessage() ]]);
                $template->display();
            }
        }
        break;
    case 'change_seqs':
        $id = (int)$_REQUEST['id'];
        $direction = $_REQUEST['direction'] ?? 'down';
        $hr = \model\EntityRepository::byName($db, 'habit');
        $hr->changeSeqs($id, $direction);
        header('Location: habits.php');
        die;
        break;
    default:
        throw new \Exception('no such action');
    }
} catch(\Throwable $e) {
    $template = new \ui\Template('site', ['template'=> 'login', 'pageTitle' => 'Login', 'errors' => [ $e->getMessage() ]]);
    $template->display();
}

function habitFromRequest() {
    global $db;
    $user = getUser($db);
    $hr = \model\EntityRepository::byName($db, 'habit');
    if (isset($_REQUEST['id'])) {
        $id = (int)$_REQUEST['id'];
        $habit = $hr->loadEntityById($id);
        if ($habit->getUser()->getId() !== $user->getId()) {
            throw new \Exception('wrong user');
        }
    } else {
        $habit = new \model\Habit($db);
        $habit->setUser($user);
    }
    return $habit;
}
