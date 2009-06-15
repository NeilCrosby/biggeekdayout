<?php

define("UPCOMING_EVENT",            "2823351");
define("FILE_TEMPLATE",             "/templates/index.tpl");
define("FILE_OUTPUT",               "/index.html");

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

mb_internal_encoding("UTF-8");

$template = getTemplate();

$template = str_replace( array('###attend###', '###watch###', '###ical###', '###sign-up###'),
                         array($attend, $watch, $ical, $sign_up),
                         $template );
saveOutput($template);

$server = ( 80 == $_SERVER['SERVER_PORT'])
        ? $_SERVER['SERVER_NAME']
        : $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];

header("Location: http://$server/");


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

?>