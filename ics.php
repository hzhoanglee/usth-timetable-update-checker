<?php
require_once 'vendor/autoload.php';
use ICal\ICal;
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$msg = '';
$tmp_file = [];
if (file_exists('events.json')) {
    $tmp_file = file_get_contents('events.json');
    $tmp_file = json_decode($tmp_file,true);
    if($tmp_file == NULL) {
        $tmp_file = [];
    }
}
try {
    $ical = new ICal('ICal.ics', array(
        'defaultSpan'                 => 2,     // Default value
        'defaultTimeZone'             => 'UTC+7',
        'defaultWeekStart'            => 'MO',  // Default value
        'disableCharacterReplacement' => false, // Default value
        'filterDaysAfter'             => null,  // Default value
        'filterDaysBefore'            => null,  // Default value
        'httpUserAgent'               => null,  // Default value
        'skipRecurrence'              => false, // Default value
    ));
    $ical->initUrl($_ENV['ICAL_URL'], $username = $_ENV['WEB_USERNAME'], $password = $_ENV['WEB_PASSWORD'], $userAgent = $_ENV['WEB_USERAGENT']);
} catch (\Exception $e) {
    die($e);
}
$currentDate = date('Y-m-d 00:00:00');
$events = $ical->eventsFromRange(date($currentDate), '2037-12-31 17:00:00');

$eventsMap = [];
foreach ($events as $event) {
    $dtstart = $ical->iCalDateToDateTime($event->dtstart_array[3]);
    $dtend = $ical->iCalDateToDateTime($event->dtend_array[3]);
    $eventsMap[$dtstart->format('Y-m-d')][] = [
        'summary' => $event->summary,
        'dtstart' => $dtstart->format('Y-m-d H:i:s'),
        'dtend' => $dtend->format('Y-m-d H:i:s'),
        'location' => $event->location,
        'description' => $event->description,
    ];
}
echo "Done fetching data\n";
$diff = compareArraysRecursive($tmp_file, $eventsMap);
foreach ($diff as $key => $value) {
    $msg .= $value;
}
if ($msg != '') {
    sendTelegram($msg);
}
$tmp = json_encode($eventsMap);
file_put_contents('events.json', $tmp);


// Functions
function dd()
{
    echo '<pre>';
    array_map(function ($x) {
        var_dump($x);
    }, func_get_args());
    echo '</pre>';
    die;
}


function compareArraysRecursive($array1, $array2, $parentKey = '')
{
    echo "Comparing arrays...\n";
    $changes = [];
    if (array_keys($array1) !== array_keys($array2)) {
        $diff = array_diff(array_keys($array2),array_keys($array1));
        $changes[$parentKey] = "New day added: \n";
        foreach($diff as $key) {
            foreach($array2[$key] as $event) {
                $changes[$parentKey] .= ($key) . ": " . $event['summary'] . "\n";
            }
        }
        return $changes;
    }

    foreach ($array1 as $key => $value1) {
        $value2 = $array2[$key];

        $currentKey = $parentKey ? $parentKey . '.' . $key : $key;

        if (is_array($value1) && is_array($value2)) {
            $nestedChanges = compareArraysRecursive($value1, $value2, $currentKey);
            $changes = array_merge($changes, $nestedChanges);
        } else {
            if ($value1 !== $value2) {
                $changes[$currentKey] = "*".$currentKey . "* changed from \n_" . $value1 . "_ \nto\n_" . $value2 . "_\n";
            }
        }
    }
    return $changes;
}

function sendTelegram($message) {
    $message = urlencode($message);
    $ch = curl_init();
    $uri = "https://api.telegram.org/bot".$_ENV['TELEGRAM_BOT_TOKEN']."/sendMessage?chat_id=".$_ENV['CHAT_ID']."&text=$message&parse_mode=markdown";
    curl_setopt($ch, CURLOPT_URL, $uri);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));
    curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
}