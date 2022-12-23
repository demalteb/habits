<?php

require_once(dirname(__FILE__) . '/local.php');

$db = connectDb(config()->db);

$user = getUser($db);

if ( isset($_REQUEST['date_start_span']) ) {
    $user->setDateStartSpan($_REQUEST['date_start_span']);
    $user->save();
}

[ $dateStartSpan, $startDate, $endDate ] = getDates($user->getDateStartSpan(), Util::nullIfEmpty($_REQUEST['start_date'] ?? null), Util::nullIfEmpty($_REQUEST['end_date'] ?? null));

$hr = \model\EntityRepository::byName($db, 'habit');
$habits = $hr->findActiveEntitiesByUserAndDates(getUser($db), $startDate, $endDate);

try {
    $template = new \ui\Template('site', [
        'template'=> 'stats', 
        'templateData' => [
            'habits' => $habits, 
            'dateStartSpan' => $dateStartSpan, 
            'startDate' => $startDate, 
            'endDate' => $endDate,
            'openedHabits' => getUser($db)->getOpenedHabits(),
        ], 
        'pageTitle' => 'Stats'
    ]);
    $template->display();
} catch(\Throwable $e) {
    echo "ERRROR";

    var_dump($e);
}

function getDates($startSpan, $startDateString, $endDateString) {
    switch ($startSpan) {
    case 'one_month':
        $endDate = new \DateTime();
        $startDate = new \DateTime($endDate->format('Y-m-d'));
        $startDate->sub(new \DateInterval('P1M'));
        return [ $startSpan, $startDate, $endDate ];
        break;
    case 'three_months':
    case '':
        $startSpan = 'three_months';
        $endDate = new \DateTime();
        $startDate = new \DateTime($endDate->format('Y-m-d'));
        $startDate->sub(new \DateInterval('P3M'));
        return [ $startSpan, $startDate, $endDate ];
        break;
    case 'six_months':
        $endDate = new \DateTime();
        $startDate = new \DateTime($endDate->format('Y-m-d'));
        $startDate->sub(new \DateInterval('P6M'));
        return [ $startSpan, $startDate, $endDate ];
        break;
    case 'one_year':
        $endDate = new \DateTime();
        $startDate = new \DateTime($endDate->format('Y-m-d'));
        $startDate->sub(new \DateInterval('P1Y'));
        return [ $startSpan, $startDate, $endDate ];
        break;
    case 'all_times':
        $endDate = new \DateTime();
        $startDate = new \DateTime($endDate->format('Y-m-d'));
        $startDate->sub(new \DateInterval('P30Y'));
        return [ $startSpan, $startDate, $endDate ];
        break;
    case 'specific_date':
        $endDate = $endDateString ? (new \DateTime($endDateString)) : null;
        return [ $startSpan, new \DateTime($startDateString), $endDate ];
    default:
        throw new \RuntimeException('invalid date values');
    }
}
