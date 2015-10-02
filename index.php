<?php

/**
 * CheetahChat 1.0b
 * simple php chat room
 * Hamid Reza Samak
 * https://github.com/hamidsamak/cheetahchat
 */


/** 
 * sqlite3 database file path
 * @var string
 */
$db_file = __DIR__ . '/database.db';



/** 
 * open database
 * @var SQLite3
 */
$db = new SQLite3($db_file);



/**
 * timeout in milliseconds until database is not locked
 */
$db->busyTimeout(2500);



/**
 * create tables if database is new
 */
$db->exec('CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY, name TEXT NOT NULL, ip TEXT NOT NULL, activity INTEGER NOT NULL)');
$db->exec('CREATE TABLE IF NOT EXISTS chats (id INTEGER PRIMARY KEY, name TEXT NOT NULL, message TEXT NOT NULL, time INTEGER NOT NULL)');



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

				while ($break === false) {
					if ($i > 0)
						$name = $_GET['name'] . ' (' . $i . ')';

					$result = $db->query('SELECT COUNT(*) AS count FROM users WHERE name = "' . $db->escapeString(htmlspecialchars($name)) . '"');
					if ($count = $result->fetchArray(SQLITE3_ASSOC))
						if ($count['count'] < 1) {
							$break = true;
							break;
						}

					$i += 1;
				}

				$db->exec('INSERT INTO users (name, ip, activity) VALUES ("' . $db->escapeString(htmlspecialchars($name)) . '", "' . $_SERVER['REMOTE_ADDR'] . '", strftime("%s", "now"))');

				echo $name;
			}
			break;



		/**
		 * online users list
		 */
		case 'users':
			$db->exec('DELETE FROM users WHERE strftime("%s", "now") - activity > 30');

			$users = array();

			$result = $db->query('SELECT id, name, ip FROM users ORDER BY activity DESC');
			while ($user = $result->fetchArray(SQLITE3_ASSOC))
				$users[] = $user;

			echo json_encode($users);
			break;



		/**
		 * messages list
		 */
		case 'chats':
			$db->exec('DELETE FROM chats WHERE strftime("%s", "now") - time > 60');

			if (isset($_GET['name']) && empty($_GET['name']) === false)
				$db->exec('UPDATE users SET activity = strftime("%s", "now") WHERE name = "' . $db->escapeString($_GET['name']) . '"');

			$last = @ceil($_GET['last']);

			$chats = array();

			$result = $db->query('SELECT id, name, message FROM chats WHERE id > ' . $db->escapeString($last) . ' ORDER BY time');
			while ($chat = $result->fetchArray(SQLITE3_ASSOC))
				$chats[] = $chat;

			echo json_encode($chats);
			break;



		/**
		 * send message
		 */
		case 'send':
			if (isset($_GET['name']) && empty($_GET['name']) === false && isset($_GET['message']) && empty($_GET['message']) === false)
				$db->exec('INSERT INTO chats (name, message, time) VALUES ("' . $db->escapeString(htmlspecialchars($_GET['name'])) . '", "' . $db->escapeString(htmlspecialchars($_GET['message'])) . '", strftime("%s", "now"))');
			break;
	}

	$db->close();

	exit;
}

/**
 * main template
 */
echo '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"> 
<title>CheetahChat</title>
<link rel="stylesheet" href="style.css">
<script type="text/javascript" src="http://code.jquery.com/jquery-2.1.4.min.js"></script>
<script type="text/javascript" src="script.js"></script>
</head>
<body>
<aside></aside>
<section></section>
<input type="text" placeholder="Enter message" disabled>
<footer><a href="https://github.com/hamidsamak/CheetahChat" target="_blank">CheetahChat 1.0b</a></footer>
</body>
</html>';

/**
 * close database connection
 */
$db->close();

?>