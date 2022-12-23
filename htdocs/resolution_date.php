<?php

require_once(dirname(__FILE__) . '/local.php');

$db = connectDb(config()->db);

$rdr = \model\EntityRepository::byName($db, 'resolutionsDates');

$action = $_REQUEST['action'];

try {
    switch ($action) {
    case 'next_resolution':
        $id = (int)$_REQUEST['id'];
        $rd = $rdr->loadEntityById($id);
        $rd->loadNextResolution();
        $rd->save();
        $habit = $rd->getResolution()->getHabit();
        // no design!
        $template = new \ui\Template('resolution_date', ['habit' => $habit, 'resolutionDate' => $rd]);
        $html = $template->fetch();
        $rv = [ 'status' => 'ok', 'resolutionDate' => $rd->toJson(), 'html' => $html ];
        break;
    case 'insert':
        $habitId = (int)$_REQUEST['habitId'];
        $year = (int)$_REQUEST['year'];
        $month = (int)$_REQUEST['month'];
        $dom = (int)$_REQUEST['dom'];

        $hr = \model\EntityRepository::byName($db, 'habit');
        $habit = $hr->loadEntityById($habitId);
        $resolution = $habit->findFirstResolution();

        $rd = new model\ResolutionsDates($db);
        $rd->setResolution($resolution);
        $rd->setDate(new \DateTime($year.'-'.$month.'-'.$dom));
        $rd->save();
        // no design!
        $template = new \ui\Template('resolution_date', ['habit' => $habit, 'resolutionDate' => $rd]);
        $html = $template->fetch();
        $rv = [ 'status' => 'ok', 'resolutionDate' => $rd->toJson(), 'html' => $html ];
        break;
    case 'update_comment':
        $id = (int)$_REQUEST['id'];
        $comment = $_REQUEST['comment'];
        $rd = $rdr->loadEntityById($id);
        $rd->setComment($comment);
        $rd->save();
        $habit = $rd->getResolution()->getHabit();
        // no design!
        $template = new \ui\Template('resolution_date', ['habit' => $habit, 'resolutionDate' => $rd]);
        $html = $template->fetch();
        $rv = [ 'status' => 'ok', 'resolutionDate' => $rd->toJson(), 'html' => $html ];
        break;
    default:
        throw new \Exception('no such action');
    }
} catch(\Throwable $e) {
    $template = new \ui\Template('site', ['template'=> 'resolution_date', 'pageTitle' => 'Resolution Date', 'errors' => [ $e->getMessage() ]]);
    $template->display();
}

die(json_encode($rv));
