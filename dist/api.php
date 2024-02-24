<?php
require_once 'config.php';


/** 
 * sqlite3 database file path
 * @var string
 */
$db_file = $config['db_file'];
$replacements = $config['replacements'];




















/** 
 * open database
 * @var SQLite3
 */
$db = new SQLite3($db_file);


chmod($db_file, 0777);


/**
 * timeout in milliseconds until database is not locked
 */
$db->busyTimeout(2500);

error_reporting(0);
ini_set('xdebug.default_enable', 0);
ini_set('display_errors', 0);

/**
 * create tables if database is new
 */
$db->exec('CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY, name TEXT NOT NULL, ip TEXT NOT NULL, activity INTEGER NOT NULL, countryCode TEXT NOT NULL, isFocused INTEGER NOT NULL, color TEXT NOT NULL)');
$db->exec('CREATE TABLE IF NOT EXISTS chats (id INTEGER PRIMARY KEY, name TEXT NOT NULL, message TEXT NOT NULL, time INTEGER NOT NULL, spam INTEGER NOT NULL, hash TEXT)');

session_start();

if (isset($_POST) && empty($_POST) === false) {
    $_GET = $_POST;
}

if (!isset($_SESSION['name']) && $_GET['action'] != 'login' && $_GET['action'] != 'logout' && !isset($_GET['token']) && !isset($_SESSION['token']) && $_GET['token'] != $_SESSION['token']) {
    $response = array(
        'status' => 'error',
        'errCode' => 'token-expired',
        'message' => 'You are not logged in, or your session has expired'
    );
    header('Content-Type: application/json');
    die(json_encode($response));
}


$result = $db->query('SELECT COUNT(*) AS count FROM users WHERE name = "' . $db->escapeString($_SESSION['name']) . '"');
if ($count = $result->fetchArray(SQLITE3_ASSOC)) {
    if ($count['count'] < 1 && $_GET['action'] != 'login' && $_GET['action'] != 'logout') {

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

if ($config['captcha'] == true && $config['filterSpam'] == true && isset($_SESSION['spamScore']) && $_SESSION['spamScore'] > 5) {
    if (isset($_GET['action']) && $_GET['action'] == 'verify') {
        require_once './assets/inc/vendor/autoload.php';
        $options = require 'assets/inc/captcha-config.php';

        $captcha = new \IconCaptcha\IconCaptcha($options);
        $validation = $captcha->validate($_POST);


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

if (isset($_SESSION['name']) && isset($_SESSION['token'])) {
    $result = $db->query('SELECT COUNT(*) AS count FROM users WHERE name = "' . $db->escapeString($_SESSION['name']) . '"');
    if ($count = $result->fetchArray(SQLITE3_ASSOC))
        if ($count['count'] < 1) {

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

                $name = htmlspecialchars($name);
                $name = strtolower($name);
                if (isset($_SESSION['name']) && !empty($_SESSION['name'])) {
                    $response = array(
                        'status' => 'error',
                        'message' => 'You are already logged in'
                    );
                    header('Content-Type: application/json');
                    die(json_encode($response));
                }
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
                if (isset($config['captcha']) && $config['captcha'] == true) {
                    require_once './assets/inc/vendor/autoload.php';
                    $options = require 'assets/inc/captcha-config.php';
                    $captcha = new \IconCaptcha\IconCaptcha($options);
                    $validation = $captcha->validate($_POST);
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
                if (preg_match('/^[0-9 ]+$/', $name)) {
                    $response = array(
                        'status' => 'error',
                        'message' => 'Name cannot be only numbers'
                    );
                    header('Content-Type: application/json');
                    die(json_encode($response));
                }
                if (in_array($name, $reservedNames)) {
                    $response = array(
                        'status' => 'error',
                        'message' => 'To prevent impersonation, this name is reserved'
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
                if (!isset($_GET['code']) || empty($_GET['code']) && $config['countryFlags'] == true) {
                    $response = array(
                        'status' => 'error',
                        'message' => 'Invalid ip or country code'
                    );
                    header('Content-Type: application/json');
                    die(json_encode($response));
                }
                if ($config['countryFlags'] == true) {
                    $countryCode = $_GET['code'];
                } else {
                    $countryCode = 'XX';
                }

                $ip = isset($_SERVER['HTTP_CLIENT_IP'])
                    ? $_SERVER['HTTP_CLIENT_IP']
                    : (isset($_SERVER['HTTP_X_FORWARDED_FOR'])
                        ? $_SERVER['HTTP_X_FORWARDED_FOR']
                        : $_SERVER['REMOTE_ADDR']);


                $color = substr(md5($name . $ip), 0, 6);

                $result = $db->query('SELECT COUNT(*) AS count FROM users WHERE name = "' . $db->escapeString(htmlspecialchars($name)) . '" AND color = "' . $color . '"');
                if ($count = $result->fetchArray(SQLITE3_ASSOC)) {
                    if ($count['count'] > 0) {
                        $name = $name . ' (' . $i + 1 . ')';
                    }
                }
                $color = substr(md5($name . $ip), 0, 6);
                $db->exec('INSERT INTO users (name, ip, activity, countryCode, isFocused, color) VALUES ("' . $db->escapeString(htmlspecialchars($name)) . '", "' . $ip . '", strftime("%s", "now"),"' . $countryCode . '", 1, "' . $color . '")');

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
        case 'getChat':


            if (isset($_SESSION['lastRequest']) && $_SESSION['lastRequest'] > time() - 1) {
                $_SESSION['lastRequest'] = time();
                $response = array(
                    'status' => 'error',
                    'message' => 'Too many requests, wait a few seconds and then try again'
                );
                header('Content-Type: application/json');
                die(json_encode($response));
            }
            $_SESSION['lastRequest'] = time();
            if (isset($_GET['focus']) && $_GET['focus'] == 'true') {
                $db->exec('UPDATE users SET activity = strftime("%s", "now"), isFocused = 1 WHERE name = "' . $db->escapeString($_SESSION['name']) . '"');
            } else {
                $db->exec('UPDATE users SET activity = strftime("%s", "now"), isFocused = 0 WHERE name = "' . $db->escapeString($_SESSION['name']) . '"');
            }
            $db->exec('DELETE FROM users WHERE strftime("%s", "now") - activity > ' . $config['maxTimeout']);
            $users = array();
            $result = $db->query('SELECT id, name, countryCode, activity, isFocused, color FROM users ORDER BY activity DESC');
            while ($user = $result->fetchArray(SQLITE3_ASSOC)) {

                if ($user['isFocused'] == true) {
                    if ($user['activity'] < (time() - 90)) {
                        $user['status'] = 'offline';
                    } else {
                        $user['status'] = 'online';
                    }
                } else {
                    if ($user['activity'] < (time() - 90)) {
                        $user['status'] = 'offline';
                    } else {
                        $user['status'] = 'busy';
                    }
                }
                unset($user['activity']);
                unset($user['isFocused']);
                $users[] = $user;
            }
            $lastHash = $_GET['lastHash'];
            $lastId = 0;
            $chats = array();
            $db->exec('DELETE FROM chats WHERE strftime("%s", "now") - time > ' . $config['maxMessageAge']);
            if (isset($_GET['lastHash']) && !empty($_GET['lastHash']) && $_GET['lastHash'] != 0) {

                $result = $db->query('SELECT id FROM chats WHERE hash = "' . $db->escapeString($lastHash) . '"');
                $lastId = $result->fetchArray(SQLITE3_ASSOC);
                if ($lastId == false) {
                    $lastId = 0;
                } else {
                    $lastId = $lastId['id'];
                }
            }

            $resultChats = $db->query('SELECT name, message, time, spam, hash FROM chats WHERE id > ' . $db->escapeString($lastId) . ' ORDER BY time');
            while ($chat = $resultChats->fetchArray(SQLITE3_ASSOC)) {


                $chats[] = $chat;
            }

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
            if (isset($_SESSION['name']) && empty($_SESSION['name']) === false && isset($_POST['message']) && empty($_POST['message']) === false) {
                $message = htmlspecialchars($_POST['message']);

                if (empty($message)) {
                    $response = array(
                        'status' => 'error',
                        'message' => 'Message cannot be empty'
                    );
                    header('Content-Type: application/json');
                    die(json_encode($response));
                }
                if (strlen($message) > $config['maxMessageLength']) {
                    $response = array(
                        'status' => 'error',
                        'message' => 'Message is too long'
                    );
                    header('Content-Type: application/json');
                    die(json_encode($response));
                }
                if (strlen($message) < 1) {
                    $response = array(
                        'status' => 'error',
                        'message' => 'Message is too short'
                    );
                    header('Content-Type: application/json');
                    die(json_encode($response));
                }
                if (preg_match('/^\s+$/', $message)) {
                    $response = array(
                        'status' => 'error',
                        'message' => 'Message cannot be only spaces'
                    );
                    header('Content-Type: application/json');
                    die(json_encode($response));
                }


                $result = $db->query('SELECT COUNT(*) AS count FROM chats WHERE name = "' . $db->escapeString($_SESSION['name']) . '" AND time > strftime("%s", "now") - 10');
                if ($count = $result->fetchArray(SQLITE3_ASSOC)) {
                    if ($count['count'] > $config['maxMessagePer10Seconds']) {

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


                require_once 'assets/inc/spamfilter.inc.php';

                $filter = new SpamFilter();
                $result = $filter->check_text_and_blacklist($message);
                if ($result) {
                    $spam = 1;

                    if (!isset($_SESSION['spamScore'])) {
                        $_SESSION['spamScore'] = 0;
                    }
                    $_SESSION['spamScore'] = $_SESSION['spamScore'] + 1;
                } else {
                    $spam = 0;
                }
                $message = str_replace(array_keys($replacements), array_values($replacements), $message);
                $hash = md5($_SESSION['name'] . $message . time());
                $db->exec('INSERT INTO chats (name, message, time, spam, hash) VALUES ("' . $db->escapeString(htmlspecialchars($_SESSION['name'])) . '", "' . $db->escapeString($message) . '", strftime("%s", "now"), ' . $spam . ', "' . $hash . '")');

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
