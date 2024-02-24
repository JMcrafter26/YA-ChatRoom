<?php


header('Content-Type: application/json');

header("Access-Control-Allow-Origin: *");
// get the language from the GET request
$lang = $_GET['lang'];
if (!isset($lang)) {
    ShowError('400 Bad Request', 'Language not specified');
}

// remove .json from the language
$lang = str_replace('.json', '', $lang);

// get the language file
$translationFile = file_get_contents('./' . $lang . '.json');
if ($translationFile === false) {
    ShowError('404 Not Found', 'Language file not found');
}

// decode the language file
$translation = json_decode($translationFile, true);
if ($translation === null) {
    ShowError('500 Internal Server Error', 'Language file could not be decoded');
}

// return the language file
header('HTTP/1.1 200 OK');

$translation['version'] = '1.1';

die(json_encode($translation));








function ShowError($status, $message)
{
    global $lang;
    header('HTTP/1.1 ' . $status);
    die(json_encode(array('status' => 'error', 'message' => $message, 'lang' => $lang)));
}