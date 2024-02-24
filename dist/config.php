<?php

$config = array(
    'db_file' => __DIR__ . '/assets/inc/database.sqlite3', // Path to the database file (db will be created if it doesn't exist)
    'replacements' => array( // Replacements for the chat, to filter spam, please refer to /assets/inc/blacklists/ for more info
        ':)' => '😃',
        ':(' => '😞',
        ':D' => '😄',
        'xD' => '😆',
        ':P' => '😛',
        ':O' => '😲',
        ':|' => '😐',
        ':[' => '😟',
        ':]' => '😊',
        '!frog' => '//frog.gif//', // Custom emoticon
    ),
    'title' => 'Chatroom', // Title of the application
    'maxTimeout' => 360, // 360s = 6min, after 6min of inactivity the user is as good as gone and can be removed
    'maxMessageLength' => 1000,
    'maxMessageAge' => 6000, // 60s = 1min, messages older than 1min will be removed
    'maxNameLength' => 20,
    'maxUsers' => 100,
    'fetchInterval' => 5000, // Fetch new messages every 5 seconds
    'backgroundFetchInterval' => 30000, // Fetch new messages every 30 seconds when the tab is in the background
    'filterSpam' => true, // Marks messages as spam if they match the spamRegex (see /assets/inc/blascklists/)
    'captcha' => true, // Use IconCaptcha to prevent spam
    'countryFlags' => false, // Show country flags next to the user's name
    'maxMessagePer10Seconds' => 3, // Will make the user wait if he sends more than 3 messages in 10 seconds
    'secret' => 'khgf8bduzdesuksgftfsd' // Salt for the token
);

// name cannot be one of the reserved names
$reservedNames = array(
    'admin',
    'administrator',
    'moderator',
    'mod',
    'owner',
    'bot',
    'system',
    'server',
    'jm26.net',
    'creator',
    'host',
    'guest',
    'anonymous',
    'anon',
    'null',
    'undefined',
    'unknown',
);
