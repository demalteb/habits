<?php

try {
    require_once(dirname(__FILE__) . '/local.php');

    $db = connectDb(config()->db);

    $tr = \model\EntityRepository::byName($db, 'touchcounter');

    $action = ( isset($_REQUEST['action']) ? $_REQUEST['action'] : null );

    switch ( $action ) {
    case 'add':
        $tr->addTouch();
        $touches = $tr->loadTouches();
        showWithTemplate($db, $touches);
        break;
    case 'add_json':
        $tr->addTouch();
        $touches = $tr->loadTouches();
        die(json_encode(['status' => 'ok', 'touches' => count($touches)]));
        break;
    case 'show':
    default:
        $touches = $tr->loadTouches();
        $touchesPerDay = $tr->findDaySumsBetweenDates(new DateTime('1970-01-01 00:00:00'), new DateTime());
        showWithTemplate($db, $touches, $touchesPerDay);
        break;
    };
} catch (\Exception $e ) {
    Util::jsonReturn('error', [ 'message' => $e->getMessage() ]);
}


function showWithTemplate($db, $touches, $touchesPerDay) {
    try {
        $template = new \ui\Template('site', [
            'template'=> 'touchcounter', 
            'templateData' => [
                'touches' => $touches,
                'touchesPerDay' => $touchesPerDay,
            ], 
            'pageTitle' => 'Touch Counter'
        ]);
        $template->display();
    } catch(\Throwable $e) {
        echo "ERRROR";

        var_dump($e);
    }
}

