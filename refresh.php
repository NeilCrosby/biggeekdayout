<?php

define("UPCOMING_EVENT",            "2912229");
define("FILE_TEMPLATE",             "/templates/index.tpl");
define("FILE_OUTPUT",               "/index.html");
define("FLICKR_API_KEY",            "a8b7fdd6e5829535c40c017bf26c7585");
define("FLICKR_SEARCH",             "biggeekdayout bletchleypark");

function __autoload($class_name) {
    require_once $class_name . '.php';
}

$url = "http://upcoming.yahoo.com/ajax/event_page_all_attendees.php?event_id=".UPCOMING_EVENT;
$data = simplexml_load_file( $url );

$attend = (string)$data->attend;
$watch  = (string)$data->watch;

$attend  = getHtmlUserList($attend);
$watch   = getHtmlUserList($watch);
$ical    = "webcal://upcoming.yahoo.com/calendar/v2/event/".UPCOMING_EVENT;
$sign_up = "http://upcoming.yahoo.com/event/".UPCOMING_EVENT."/";

$attend_num = getUserCount($data->attend);
$attend_num = ($attend_num) ? " ($attend_num)" : '';

$watch_num  = getUserCount($data->watch);
$watch_num  = ($watch_num)  ? " ($watch_num)"  : '';

$flickr = getFlickrHtml(FLICKR_API_KEY, FLICKR_SEARCH);

mb_internal_encoding("UTF-8");

$template = getTemplate();

$template = str_replace( array('###attend###', '###watch###', '###ical###', '###sign-up###', '###attend-num###', '###watch-num###', '###flickr###'),
                         array($attend, $watch, $ical, $sign_up, $attend_num, $watch_num, $flickr),
                         $template );
saveOutput($template);

$server = ( 80 == $_SERVER['SERVER_PORT'])
        ? $_SERVER['SERVER_NAME']
        : $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];

if (!isset($_GET['no_redirect'])) {
    header("Location: http://$server/");
}

exit();


function getTemplate() {
    return file_get_contents($_SERVER['DOCUMENT_ROOT'].FILE_TEMPLATE);
}

function saveOutput( $template ) {
    return file_put_contents($_SERVER['DOCUMENT_ROOT'].FILE_OUTPUT, $template);
}

function getHtmlUserList($html) {
    $doc = new DOMDocument();
    // have to give charset otherwise loadHTML gets confused
    $doc->loadHTML(
        '<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head><body>'.
        $html.
        '</body></html>'
    );
    $xpath = new DOMXPath($doc);
    $items = $xpath->query("//a[@property='vcard:fn']");

    $output = '';
    if ($items->length > 0) {
        $output = "<ul class='bd'>";
        foreach ($items as $item) {
            $href = $item->attributes->getNamedItem('href')->nodeValue;
            $text = (string)$item->firstChild->nodeValue;

            $output .= "<li><a href='${href}'>${text}</a></li>";
        }
        $output .= "</ul>";
    }

    return $output;
}

function getUserCount($html) {
    $doc = new DOMDocument();
    // have to give charset otherwise loadHTML gets confused
    $doc->loadHTML(
        '<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head><body>'.
        $html.
        '</body></html>'
    );
    $xpath = new DOMXPath($doc);
    $items = $xpath->query("//a[@property='vcard:fn']");

    return $items->length;
}

?>