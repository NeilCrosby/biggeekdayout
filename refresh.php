<?php

//define("UPCOMING_EVENT",            "2912229");
define("LANYRD_EVENT",              "big-geek-day-out-the-great-western-lego-show");
define("FILE_TEMPLATE",             "/templates/index.tpl");
define("FILE_OUTPUT",               "/index.html");
define("FLICKR_API_KEY",            "a8b7fdd6e5829535c40c017bf26c7585");
define("FLICKR_SEARCH",             "biggeekdayout bletchleypark");

function __autoload($class_name) {
    require_once $class_name . '.php';
}

#$url = "http://upcoming.yahoo.com/ajax/event_page_all_attendees.php?event_id=".UPCOMING_EVENT;
#$data = simplexml_load_file( $url );

$url = "http://lanyrd.com/2011/".LANYRD_EVENT."/";

$doc = getDataFromFeed($url);

$xpath = new DOMXPath($doc);

$attend = $xpath->query("//div[contains(@class, 'attendees-placeholder')]");
$watch  = $xpath->query("//div[contains(@class, 'trackers-placeholder')]");

$attend  = getHtmlUserList($doc, 'attendees');
$watch   = getHtmlUserList($doc, 'trackers');
$ical    = "http://lanyrd.com/2011/big-geek-day-out-the-great-western-lego-show/big-geek-day-out-the-great-western-lego-show.ics";
$sign_up = $url;

$attend_num = getUserCount($doc, 'attendees');
$attend_num = ($attend_num) ? " ($attend_num)" : '';

$watch_num  = getUserCount($doc, 'trackers');
$watch_num  = ($watch_num)  ? " ($watch_num)"  : '';

$flickr = '';//getFlickrHtml(FLICKR_API_KEY, FLICKR_SEARCH);

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

function getHtmlUserList($doc, $type) {
    $xpath = new DOMXPath($doc);
    $items = $xpath->query("//div[contains(@class, '${type}-placeholder')]//a");

    $output = '';
    if ($items->length > 0) {
        $output = "<ul class='bd'>";
        foreach ($items as $item) {
            $imgs = $xpath->query("img[not(contains(@class, 'youfollow'))]", $item);
            
            $img = $imgs->item(0)->attributes->getNamedItem('src')->nodeValue;

            $text = $item->attributes->getNamedItem('title')->nodeValue;
            
            $atPosition = strrpos($text, '@');
            if ( false === $atPosition ) {
                continue;
            }
            
            $text = substr($text, $atPosition + 1);
            
            $href= "http://twitter.com/${text}";
            
            $output .= "<li><a href='${href}'><img src='${img}' alt='@${text}' title='@${text}'></a></li>";
        }
        $output .= "</ul>";
    }

    return $output;
}

function getUserCount($doc, $type) {
    $xpath = new DOMXPath($doc);
    $items = $xpath->query("//div[contains(@class, '${type}-placeholder')]//a");

    return $items->length;
}

function getDataFromFeed( $url ) {
    $data = apc_fetch($url);
    if (!$data) {
        error_log('CACHE MISS: '.$url);

        $data = file_get_contents( $url );

        apc_store($url, $data, 600);
    }
    
    $doc = new DomDocument();
    @$doc->loadHtml( $data );
    
    return $doc;
}



?>