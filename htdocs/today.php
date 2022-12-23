<?php

require_once(dirname(__FILE__) . '/local.php');

$db = connectDb(config()->db);

$hr = \model\EntityRepository::byName($db, 'habit');
$habits = $hr->findAllEntities();

try {
    $template = new \ui\Template('site', ['template'=> 'today', 'templateData' => $habits, 'pageTitle' => 'Today']);
    $template->display();
} catch(\Throwable $e) {
    echo "ERRROR";

    var_dump($e);
}
