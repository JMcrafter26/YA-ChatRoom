<?php


/**
 * CheetahChat 1.0b
 * simple php chat room
 * Hamid Reza Samak
 * https://github.com/hamidsamak/cheetahchat
 */
require_once 'config.php';


/** 
 * sqlite3 database file path
 * @var string
 */
$db_file = $config['db_file'];
$replacements = $config['replacements'];



// DEBUG, remove in production
// $replacements = array(
//     ':)' => '😃',
//     ':(' => '😞',
//     ':D' => '😄',
//     ':P' => '😛',
//     ':O' => '😲',
//     ':|' => '😐',
//     ':/' => '😕',
//     ':*' => '😘',
//     ':3' => '😺',
//     ':>' => '😏',
//     ':<' => '😔',
//     ':[' => '😟',
//     ':]' => '😊'
// );

/** 
 * open database
 * @var SQLite3
 */
$db = new SQLite3($db_file);

// set database permissions to 0777
chmod($db_file, 0777);


/**
 * timeout in milliseconds until database is not locked
 */
$db->busyTimeout(2500);

// disable error reporting
error_reporting(0);
// disable xdebug
ini_set('xdebug.default_enable', 0);
// disable error display
ini_set('display_errors', 0);


/**
 * create tables if database is new
 */
$db->exec('CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY, name TEXT NOT NULL, ip TEXT NOT NULL, activity INTEGER NOT NULL, countryCode TEXT NOT NULL, isFocus INTEGER NOT NULL, color TEXT NOT NULL)');
$db->exec('CREATE TABLE IF NOT EXISTS chats (id INTEGER PRIMARY KEY, name TEXT NOT NULL, message TEXT NOT NULL, time INTEGER NOT NULL, spam INTEGER NOT NULL)');


session_start();

// if POST is set instead of GET, set GET to POST
if (isset($_POST) && empty($_POST) === false) {
    $_GET = $_POST;
}


// if user is not logged in and action is not login, return error
if (!isset($_SESSION['name']) && $_GET['action'] != 'login' && $_GET['action'] != 'logout' && !isset($_GET['token']) && !isset($_SESSION['token']) && $_GET['token'] != $_SESSION['token']) {
    $response = array(
        'status' => 'error',
        'errCode' => 'token-expired',
        'message' => 'You are not logged in, or your session has expired'
    );
    header('Content-Type: application/json');
    die(json_encode($response));
}

// check if user is in database and action is not login or logout
$result = $db->query('SELECT COUNT(*) AS count FROM users WHERE name = "' . $db->escapeString($_SESSION['name']) . '"');
if ($count = $result->fetchArray(SQLITE3_ASSOC)) {
    if ($count['count'] < 1 && $_GET['action'] != 'login' && $_GET['action'] != 'logout') {
        // destroy session
        session_destroy();
        $response = array(
            'status' => 'error',
            'errCode' => 'token-expired',
            'message' => 'Your session has expired'
        );
        header('Content-Type: application/json');
        die(json_encode($response));
    }
}

if($config['captcha'] == true && $config['filterSpam'] == true && isset($_SESSION['spamScore']) && $_SESSION['spamScore'] > 5) {
    if(isset($_GET['action']) && $_GET['action'] == 'verify') {
        require_once './assets/inc/vendor/autoload.php';

        // Load the IconCaptcha options.
        $options = require 'assets/inc/captcha-config.php';
    
        // Create an instance of IconCaptcha.
        $captcha = new \IconCaptcha\IconCaptcha($options);
    
        // Validate the captcha.
        $validation = $captcha->validate($_POST);
    
        // Confirm the captcha was validated.
        if ($validation->success()) {
            $response = array(
                'status' => 'success',
                'message' => 'Captcha validation successful'
            );
            $_SESSION['spamScore'] = 0;
            header('Content-Type: application/json');
            die(json_encode($response));
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Captcha validation failed, please try again',
                'errCode' => 'captcha-failed'
            );
            header('Content-Type: application/json');
            die(json_encode($response));
        } 
    }
    $response = array(
        'status' => 'error',
        'errCode' => 'verify-captcha',
        'message' => 'You are sending too many spam messages. Please verify the captcha to continue'
    );
    header('Content-Type: application/json');
    die(json_encode($response));
}



// check if user is in database
if (isset($_SESSION['name']) && isset($_SESSION['token'])) {
    $result = $db->query('SELECT COUNT(*) AS count FROM users WHERE name = "' . $db->escapeString($_SESSION['name']) . '"');
    if ($count = $result->fetchArray(SQLITE3_ASSOC))
        if ($count['count'] < 1) {
            // destroy session
            session_destroy();
            $response = array(
                'status' => 'error',
                'errCode' => 'token-expired',
                'message' => 'Your session has expired'
            );
            header('Content-Type: application/json');
            die(json_encode($response));
        }
}


if (isset($_GET['action'])) {
    switch ($_GET['action']) {
            /**
         * login as a user
         * no authentication
         * identification just as a name
         */
        case 'login':
            if (isset($_GET['name']) && empty($_GET['name']) === false) {
                $i = 0;
                $break = false;
                $name = $_GET['name'];
                // sanitize name
                $name = htmlspecialchars($name);
                // lowercase name
                $name = strtolower($name);

                // check if the maxUsers limit is reached
                $result = $db->query('SELECT COUNT(*) AS count FROM users');
                if ($count = $result->fetchArray(SQLITE3_ASSOC))
                    if ($count['count'] >= $config['maxUsers']) {
                        $response = array(
                            'status' => 'error',
                            'message' => 'Max users limit reached, please try again later'
                        );
                        header('Content-Type: application/json');
                        die(json_encode($response));
                    }

                if(isset($config['captcha']) && $config['captcha'] == true) {

                    require_once './assets/inc/vendor/autoload.php';

                    // Load the IconCaptcha options.
                    $options = require 'assets/inc/captcha-config.php';

                    // Create an instance of IconCaptcha.
                    $captcha = new \IconCaptcha\IconCaptcha($options);

                    // Validate the captcha.
                    $validation = $captcha->validate($_POST);

                    // Confirm the captcha was validated.
                    if (!$validation->success()) {
                        $response = array(
                            'status' => 'error',
                            'message' => 'Captcha validation failed, please try again',
                            'errCode' => 'captcha-failed'
                        );
                        header('Content-Type: application/json');
                        die(json_encode($response));
                    }
                }

                // check if name is valid
                if (strlen($name) > $config['maxNameLength']) {
                    $response = array(
                        'status' => 'error',
                        'message' => 'Name is too long'
                    );
                    header('Content-Type: application/json');
                    die(json_encode($response));
                } else if (strlen($name) < 2) {
                    $response = array(
                        'status' => 'error',
                        'message' => 'Name is too short'
                    );
                    header('Content-Type: application/json');
                    die(json_encode($response));
                } else if (preg_match('/[^a-z0-9 ]/i', $name)) {
                    $response = array(
                        'status' => 'error',
                        'message' => 'Name contains invalid characters'
                    );
                    header('Content-Type: application/json');
                    die(json_encode($response));
                }

                // name canot be only numbersor only spaces
                if (preg_match('/^[0-9 ]+$/', $name)) {
                    $response = array(
                        'status' => 'error',
                        'message' => 'Name cannot be only numbers'
                    );
                    header('Content-Type: application/json');
                    die(json_encode($response));
                }

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
                    'jmcrafter26'
                );
                if (in_array($name, $reservedNames)) {
                    $response = array(
                        'status' => 'error',
                        'message' => 'Name is reserved'
                    );
                    header('Content-Type: application/json');
                    die(json_encode($response));
                }

                if ($_SESSION['randomToken'] != $_GET['token']) {
                    $response = array(
                        'status' => 'error',
                        'errCode' => 'invalid-token',
                        'message' => 'Invalid token'
                    );
                    header('Content-Type: application/json');
                    die(json_encode($response));
                }


                if (!isset($_GET['code']) || empty($_GET['code'])) {
                    $response = array(
                        'status' => 'error',
                        'message' => 'Invalid ip or country code'
                    );
                    header('Content-Type: application/json');
                    die(json_encode($response));
                }

                // while ($break === false) {
                //     if ($i > 0)
                //         $name = $_GET['name'] . ' (' . $i . ')';

                //     $result = $db->query('SELECT COUNT(*) AS count FROM users WHERE name = "' . $db->escapeString(htmlspecialchars($name)) . '"');
                //     if ($count = $result->fetchArray(SQLITE3_ASSOC))
                //         if ($count['count'] < 1) {
                //             $break = true;
                //             break;
                //         }

                //     $i += 1;
                // }


                $ip = isset($_SERVER['HTTP_CLIENT_IP'])
                    ? $_SERVER['HTTP_CLIENT_IP']
                    : (isset($_SERVER['HTTP_X_FORWARDED_FOR'])
                        ? $_SERVER['HTTP_X_FORWARDED_FOR']
                        : $_SERVER['REMOTE_ADDR']);


                // get country code from useragent
                // $countryCode = substr($_SERVER['HTTP_USER_AGENT'], -2);
                $countryCode = $_GET['code'];

                // generate a color code from the name and the ip of the user
                $color = substr(md5($name . $ip), 0, 6);


                // if name and color is the same as another user, add a number to the name
                $result = $db->query('SELECT COUNT(*) AS count FROM users WHERE name = "' . $db->escapeString(htmlspecialchars($name)) . '" AND color = "' . $color . '"');
                if ($count = $result->fetchArray(SQLITE3_ASSOC)) {
                    if ($count['count'] > 0) {
                        $name = $name . ' (' . $i + 1 . ')';
                    }
                }

                // generate a color code from the name and the ip of the user
                $color = substr(md5($name . $ip), 0, 6);


                $db->exec('INSERT INTO users (name, ip, activity, countryCode, isFocus, color) VALUES ("' . $db->escapeString(htmlspecialchars($name)) . '", "' . $ip . '", strftime("%s", "now"),"' . $countryCode . '", 1, "' . $color . '")');
                // check if an error occured
                if ($db->lastErrorCode() != 0) {
                    $response = array(
                        'status' => 'error',
                        'message' => 'An error occured',
                        'data' => array(
                            'error' => $db->lastErrorMsg()
                        )
                    );
                    header('Content-Type: application/json');
                    die(json_encode($response));
                }

                $_SESSION['name'] = $name;
                $_SESSION['token'] = md5($name . $config['secret']);

                $response = array(
                    'status' => 'success',
                    'message' => 'User logged in successfully',
                    'data' => array(
                        'name' => $name
                    )
                );
                header('Content-Type: application/json');
                $db->close();
                die(json_encode($response));
            }
            break;

        case 'logout':
            if (isset($_GET['token']) && $_GET['token'] == $_SESSION['token']) {
                $db->exec('DELETE FROM users WHERE name = "' . $db->escapeString($_SESSION['name']) . '"');
                session_destroy();

                $response = array(
                    'status' => 'success',
                    'message' => 'User logged out successfully'
                );
                header('Content-Type: application/json');
                $db->close();
                die(json_encode($response));
            } else {
                $response = array(
                    'status' => 'success',
                    'message' => 'Already logged out'
                );
                header('Content-Type: application/json');
                $db->close();
                die(json_encode($response));
            }



            /**
             * online users list
             */
            // case 'users':

            //     // set activity to current time for current user
            //     if (isset($_GET['focus']) && $_GET['focus'] == 'true') {
            //         $db->exec('UPDATE users SET activity = strftime("%s", "now"), isFocus = 1 WHERE name = "' . $db->escapeString($_SESSION['name']) . '"');
            //     } else {
            //         $db->exec('UPDATE users SET activity = strftime("%s", "now"), isFocus = 0 WHERE name = "' . $db->escapeString($_SESSION['name']) . '"');
            //     }

            //     $db->exec('DELETE FROM users WHERE strftime("%s", "now") - activity > ' . $config['maxTimeout']);

            //     $users = array();

            //     $result = $db->query('SELECT id, name, countryCode, activity, isFocus FROM users ORDER BY activity DESC');
            //     while ($user = $result->fetchArray(SQLITE3_ASSOC))
            //         $users[] = $user;

            //     $response = array(
            //         'status' => 'success',
            //         'message' => 'Users fetched successfully',
            //         'data' => $users
            //     );
            //     header('Content-Type: application/json');
            //     $db->close();
            //     die(json_encode($response));

            //     break;



            /**
             * messages list
             */
            // case 'chats':
            //     $db->exec('DELETE FROM chats WHERE strftime("%s", "now") - time > 60');

            //         $db->exec('UPDATE users SET activity = strftime("%s", "now") WHERE name = "' . $db->escapeString($_SESSION['name']) . '"');

            //     $last = @ceil($_GET['last']);

            //     $chats = array();

            //     $result = $db->query('SELECT id, name, message FROM chats WHERE id > ' . $db->escapeString($last) . ' ORDER BY time');
            //     while ($chat = $result->fetchArray(SQLITE3_ASSOC))
            //         $chats[] = $chat;

            //     $response = array(
            //         'status' => 'success',
            //         'message' => 'Chats fetched successfully',
            //         'data' => $chats
            //     );
            //     header('Content-Type: application/json');
            //     $db->close();
            //     die(json_encode($response));

            //     break;

        case 'all':
            // get chats and users
            if (isset($_GET['focus']) && $_GET['focus'] == 'true') {
                $db->exec('UPDATE users SET activity = strftime("%s", "now"), isFocus = 1 WHERE name = "' . $db->escapeString($_SESSION['name']) . '"');
            } else {
                $db->exec('UPDATE users SET activity = strftime("%s", "now"), isFocus = 0 WHERE name = "' . $db->escapeString($_SESSION['name']) . '"');
            }

            $db->exec('DELETE FROM users WHERE strftime("%s", "now") - activity > ' . $config['maxTimeout']);
            $users = array();

            $result = $db->query('SELECT id, name, countryCode, activity, isFocus, color FROM users ORDER BY activity DESC');
            while ($user = $result->fetchArray(SQLITE3_ASSOC))
                $users[] = $user;

            $last = @ceil($_GET['last']);


            $chats = array();
            $db->exec('DELETE FROM chats WHERE strftime("%s", "now") - time > 60');
            $resultChats = $db->query('SELECT id, name, message, time, spam FROM chats WHERE id > ' . $db->escapeString($last) . ' ORDER BY time');
            while ($chat = $resultChats->fetchArray(SQLITE3_ASSOC))
                $chats[] = $chat;

            // for each chat, generate a hash out of it 
            foreach ($chats as $key => $chat) {
                $chats[$key]['hash'] = md5($chat['name'] . $chat['message'] . $chat['time']);
            }

            // if chat is empty, set it to empty array
            if (empty($chats)) {
                $chats = array();
            }

            $response = array(
                'status' => 'success',
                'message' => 'Users and chats fetched successfully',
                'data' => array(
                    'users' => $users,
                    'chats' => $chats
                )
            );
            header('Content-Type: application/json');
            $db->close();
            die(json_encode($response));




            /**
             * send message
             */
        case 'send':
            if (isset($_SESSION['name']) && empty($_SESSION['name']) === false && isset($_GET['message']) && empty($_GET['message']) === false) {
                $message = htmlspecialchars($_GET['message']);
                // $message = $_GET['message'];

                // check if message is empty
                if (empty($message)) {
                    $response = array(
                        'status' => 'error',
                        'message' => 'Message cannot be empty'
                    );
                    header('Content-Type: application/json');
                    die(json_encode($response));
                }

                // check if message is too long
                if (strlen($message) > $config['maxMessageLength']) {
                    $response = array(
                        'status' => 'error',
                        'message' => 'Message is too long'
                    );
                    header('Content-Type: application/json');
                    die(json_encode($response));
                }

                // check if message is too short
                if (strlen($message) < 1) {
                    $response = array(
                        'status' => 'error',
                        'message' => 'Message is too short'
                    );
                    header('Content-Type: application/json');
                    die(json_encode($response));
                }

                // check if message is only spaces or newlines or invisible characters
                if (preg_match('/^\s+$/', $message)) {
                    $response = array(
                        'status' => 'error',
                        'message' => 'Message cannot be only spaces'
                    );
                    header('Content-Type: application/json');
                    die(json_encode($response));
                }

                // DEBUG
                // header('HTTP/1.1 500 Internal Server Error');
                // die('DEBUG');

                // check if user is spamming (by sending too many messages in a short time)
                $result = $db->query('SELECT COUNT(*) AS count FROM chats WHERE name = "' . $db->escapeString($_SESSION['name']) . '" AND time > strftime("%s", "now") - 10');
                if ($count = $result->fetchArray(SQLITE3_ASSOC)) {
                    if ($count['count'] > $config['maxMessagePer10Seconds']) {
                        // get the last message time and calculate the time left
                        $result = $db->query('SELECT time FROM chats WHERE name = "' . $db->escapeString($_SESSION['name']) . '" ORDER BY time DESC LIMIT 1');
                        $until = $result->fetchArray(SQLITE3_ASSOC);
                        $response = array(
                            'status' => 'error',
                            'message' => 'It seems like you are spamming',
                            'errCode' => 'spamming',
                            'data' => array(
                                'until' => $until['time'] + 10
                            )
                        );
                        header('Content-Type: application/json');
                        die(json_encode($response));
                    }
                }


                // check if message is spam
                // not implemented yet
                require_once 'assets/inc/spamfilter.inc.php';


                // Search in all available blacklists
                $filter = new SpamFilter();

                $result = $filter->check_text_and_blacklist($message);
                if ($result) {
                    $spam = 1;
                    // $response = array(
                    //     'status' => 'error',
                    //     'errCode' => 'spam',
                    //     'message' => 'Message is spam. Please do not talk about ' . $result
                    // );
                    // header('Content-Type: application/json');
                    // die(json_encode($response));
                    if(!isset($_SESSION['spamScore'])) {
                        $_SESSION['spamScore'] = 0;
                    }
                    $_SESSION['spamScore'] = $_SESSION['spamScore'] + 1;
                } else {
                    $spam = 0;
                }

                $message = str_replace(array_keys($replacements), array_values($replacements), $message);

                $db->exec('INSERT INTO chats (name, message, time, spam) VALUES ("' . $db->escapeString(htmlspecialchars($_SESSION['name'])) . '", "' . $db->escapeString($message) . '", strftime("%s", "now"), ' . $spam . ')');
                // check if an error occured
                if ($db->lastErrorCode() != 0) {
                    $response = array(
                        'status' => 'error',
                        'message' => 'An error occured',
                        'data' => array(
                            'error' => $db->lastErrorMsg()
                        )
                    );
                    header('Content-Type: application/json');
                    die(json_encode($response));
                } else {
                    $response = array(
                        'status' => 'success',
                        'message' => 'Message sent successfully',
                        // if spam is 1 set it to true, else set it to false
                        'data' => array(
                            'spam' => $spam == 1 ? true : false,
                            'spam_category' => $result[1] ?? null
                        )
                    );
                    header('Content-Type: application/json');
                    $db->close();
                    die(json_encode($response));
                }
            }
            break;
        default:
            $response = array(
                'status' => 'error',
                'message' => 'Invalid request'
            );
            header('Content-Type: application/json');
            $db->close();
            die(json_encode($response));
    }

    $db->close();

    exit;
} else {
    $response = array(
        'status' => 'error',
        'message' => 'Invalid request'
    );
    header('Content-Type: application/json');
    die(json_encode($response));
}


/**
 * close database connection
 */
$db->close();
exit;
