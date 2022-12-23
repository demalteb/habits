<?php

require_once(dirname(__FILE__) . '/local.php');

$db = connectDb(config()->db);

$action = $_REQUEST['action'] ?? 'list';

try {
    switch ($action) {
    case 'list':
        try {
            $rr = \model\EntityRepository::byName($db, 'habit_pause');
            $hr = \model\EntityRepository::byName($db, 'habit');
            $habitId = (int)$_REQUEST['habit_id'];
            $habit = $hr->findEntityById($habitId);
            $pauses = $rr->findEntitiesByHabit($habit);
            $template = new \ui\Template('site', ['template'=> 'pause_list', 'templateData' => ['habit' => $habit, 'pauses' => $pauses], 'pageTitle' => 'pauses']);
            $template->display();
        } catch(\Throwable $e) {
            $template = new \ui\Template('site', ['template'=> 'pause_list', 'pageTitle' => 'pauses', 'errors' => [ $e->getMessage() ]]);
            $template->display();
        }
        break;
    case 'edit':
        try {
            $hr = \model\EntityRepository::byName($db, 'habit_pause');
            if (isset($_POST['save'])) {
                $pause = pauseFromRequest();
                $pause->setStartDate(new \DateTime($_POST['start_date']));
                $pause->setEndDate(\Util::dateOrNull($_POST['end_date']));
                $pause->setDescription(\Util::nullIfEmpty($_POST['description']));
                $pause->save();
                header('Location: pauses.php?habit_id='.(int)$_REQUEST['habit_id']);
                die;
            } else {
                $pause = pauseFromRequest();
                $habit = $pause->getHabit();
                $template = new \ui\Template('site', ['template'=> 'pause_edit', 'templateData' => ['habit' => $habit, 'pause' => $pause], 'pageTitle' => ' pause']);
                $template->display();
            }
        } catch(\Throwable $e) {
            $pause = pauseFromRequest();
            $habit = $pause->getHabit();
            $template = new \ui\Template('site', ['template'=> 'pause_edit', 'templateData' => ['pause' => $pause, 'habit' => $habit], 'errors' => [ $e->getMessage() ]]);
            $template->display();
        }
        break;
    case 'delete':
        $rr = \model\EntityRepository::byName($db, 'habit_pause');
        $pauseId = (int)$_REQUEST['id'];
        $pause = $rr->loadEntityByUserAndId(getUser($db), $pauseId);
        $habitId = $pause->getHabit()->getId();
        $rr->deleteEntityById($pauseId);
        header('Location: pauses.php?habit_id=' . $habitId);
        die;
        break;
    default:
        throw new \Exception('no such action');
    }
} catch(\Throwable $e) {
    $template = new \ui\Template('site', ['template'=> 'pause_list', 'pageTitle' => 'pauses', 'errors' => [ $e->getMessage() ]]);
    $template->display();
}

function habitFromRequest($id = null) {
    global $db;
    $hr = \model\EntityRepository::byName($db, 'habit');
    if (isset($_REQUEST['habit_id'])) {
        $id = (int)$_REQUEST['habit_id'];
        $habit = $hr->loadEntityById($id);
    } elseif ($id) {
        $habit = $hr->loadEntityById($id);
    } else {
        throw new \Exception('no habit id in request');
    }
    return $habit;
}
function pauseFromRequest() {
    global $db;
    if (isset($_REQUEST['id'])) {
        $rr = \model\EntityRepository::byName($db, 'habit_pause');
        $id = (int)$_REQUEST['id'];
        $pause = $rr->loadEntityById($id);
    } else {
        $habit = habitFromRequest();
        $pause = new \model\HabitPause($db);
        $pause->setHabit($habit);
    }
    return $pause;
}
