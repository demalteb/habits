<?php

require_once(dirname(__FILE__) . '/local.php');

$db = connectDb(config()->db);

$hr = \model\EntityRepository::byName($db, 'habit');
$habits = $hr->findEntitiesByUser(getUser($db));

try {
    $template = new \ui\Template('site', ['template'=> 'index', 'templateData' => $habits, 'pageTitle' => 'Calendar']);
    $template->display();
} catch(\Throwable $e) {
    echo "ERRROR";

    var_dump($e);
}
