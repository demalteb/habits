<?php

require_once(dirname(__FILE__) . '/local.php');

$db = connectDb(config()->db);

$hr = \model\EntityRepository::byName($db, 'habit');
$now = new \DateTime();
$habits = $hr->findActiveEntitiesByUserAndDates(getUser($db), $now, $now);

try {
    $template = new \ui\Template('site', ['template'=> 'this_week', 'templateData' => $habits, 'pageTitle' => 'This week']);
    $template->display();
} catch(\Throwable $e) {
    echo "ERRROR";

    var_dump($e);
}
