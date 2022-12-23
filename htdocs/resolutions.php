<?php

require_once(dirname(__FILE__) . '/local.php');

$db = connectDb(config()->db);

$action = $_REQUEST['action'] ?? 'list';

try {
    switch ($action) {
    case 'list':
        try {
            $rr = \model\EntityRepository::byName($db, 'resolution');
            $hr = \model\EntityRepository::byName($db, 'habit');
            $habitId = (int)$_REQUEST['habit_id'];
            $habit = $hr->findEntityById($habitId);
            $resolutions = $rr->findEntitiesByHabit($habit);
            $template = new \ui\Template('site', ['template'=> 'resolution_list', 'templateData' => ['habit' => $habit, 'resolutions' => $resolutions], 'pageTitle' => 'Resolutions']);
            $template->display();
        } catch(\Throwable $e) {
            $template = new \ui\Template('site', ['template'=> 'resolution_list', 'pageTitle' => 'Resolutions', 'errors' => [ $e->getMessage() ]]);
            $template->display();
        }
        break;
    case 'edit':
        try {
            $hr = \model\EntityRepository::byName($db, 'habit');
            if (isset($_POST['save'])) {
                $resolution = resolutionFromRequest();
                $resolution->setName($_POST['name']);
                $resolution->setDescription(\Util::nullIfEmpty($_POST['description']));
                $resolution->setAbbreviation($_POST['abbreviation']);
                $resolution->setFulfilment($_POST['fulfilment']);
                $resolution->save();
                header('Location: resolutions.php?habit_id='.(int)$_REQUEST['habit_id']);
                die;
            } else {
                $resolution = resolutionFromRequest();
                $habit = $resolution->getHabit();
                $template = new \ui\Template('site', ['template'=> 'resolution_edit', 'templateData' => ['habit' => $habit, 'resolution' => $resolution], 'pageTitle' => $resolution->getName() . ' - Resolution']);
                $template->display();
            }
        } catch(\Throwable $e) {
            $resolution = resolutionFromRequest();
            $habit = $resolution->getHabit();
            $template = new \ui\Template('site', ['template'=> 'resolution_edit', 'templateData' => ['resolution' => $resolution, 'habit' => $habit], 'errors' => [ $e->getMessage() ]]);
            $template->display();
        }
        break;
    case 'delete':
        $rr = \model\EntityRepository::byName($db, 'resolution');
        $resolutionId = (int)$_REQUEST['id'];
        $resolution = $rr->loadEntityByUserAndId(getUser($db), $resolutionId);
        $habitId = $resolution->getHabit()->getId();
        $rr->deleteEntityById($resolutionId);
        header('Location: resolutions.php?habit_id=' . $habitId);
        die;
        break;
    case 'change_seqs':
        $id = (int)$_REQUEST['id'];
        $direction = $_REQUEST['direction'] ?? 'down';
        $hr = \model\EntityRepository::byName($db, 'resolution');
        $hr->changeSeqs($id, $direction);
        header('Location: resolutions.php?habit_id='.(int)$_REQUEST['habit_id']);
        die;
        break;
    default:
        throw new \Exception('no such action');
    }
} catch(\Throwable $e) {
    $template = new \ui\Template('site', ['template'=> 'resolution_list', 'pageTitle' => 'Resolutions', 'errors' => [ $e->getMessage() ]]);
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
function resolutionFromRequest() {
    global $db;
    if (isset($_REQUEST['id'])) {
        $rr = \model\EntityRepository::byName($db, 'resolution');
        $id = (int)$_REQUEST['id'];
        $resolution = $rr->loadEntityById($id);
    } else {
        $habit = habitFromRequest();
        $resolution = new \model\Resolution($db);
        $resolution->setHabit($habit);
    }
    return $resolution;
}
