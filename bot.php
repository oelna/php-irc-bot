<?php

// config
$server = array(
    'url' => 'irc.arnorichter.de',
    'port' => 47362,
    'ssl' => true
);

// your user data
$user = array(
    'username' => 'oelnabot',
    'realname' => 'Arno Richter',
    'nickname' => 'oelnabot',
    'password' => ''
);

// which channels to join on login
$channels = array(
    '#int', 
    '#html'
);

// stop editing here!

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Europe/Berlin');
set_time_limit(0); // Prevent PHP from stopping the script after 30 sec

DEFINE('DS', DIRECTORY_SEPARATOR);
DEFINE('EOL', "\n");
DEFINE('COMMANDS', __DIR__.DS.'commands');

if(!is_dir(COMMANDS)) {
    mkdir(COMMANDS);
}

function send($string) {
    global $socket;

    fputs($socket, $string . EOL);
}

function message($string, $channel) {
    send('PRIVMSG '.$channel.' :'.$string);
}

// opening the socket to the network
if(!$socket = fsockopen(($server['ssl'] ? 'ssl://' : '').$server['url'], $server['port'])) {
    die('Could not establish connection using '.$server['url'].':'.$server['port']);
};

// send auth info
if(!empty($user['password'])) {
    send('PASS' . $user['password']);
}
send('NICK ' . $user['nickname']);
send('USER ' . $user['username'] . " 0 * :" . $user['realname']);

// force an endless while
while (1) {

    // continue the rest of the script here
    while ($data = fgets($socket, 128)) {
        echo $data;
        flush();
        
        // separate all data
        $ex = explode(' ', $data);
        
        // send PONG back to the server
        if ($ex[0] == 'PING') {
            send('PONG ' . $ex[1]);
        }

        if(!isset($ex[1])) continue;

        // execute this after MOTD
        if ($ex[1] == 376 || $ex[1] == 422) {
            // join channels
            foreach($channels as $channel) {
                send('JOIN ' . $channel);
            }
        }

        if(!isset($ex[3])) continue;

        // handle commands
        if ($ex[1] == 'PRIVMSG' && @$ex[3]{1} == '!') {
            list($nick, $ip) = explode('!', trim($ex[0], ':'));
            $channel = $ex[2];
            $command = ltrim($ex[3], ':!');
            $args = '';
            for ($i = 4; $i < count($ex); $i++) {
                $args .= $ex[$i] . ' ';
            }

            // look for custom command function
            $file = COMMANDS.DIRECTORY_SEPARATOR.trim($command).'.php';
            if(file_exists($file) && is_file($file)) {
                $fn = require($file);
                try {
                    $result = $fn($nick, $args, $channel);
                    if ($result !== null) {
                        $lines = explode(EOL, trim($result, EOL));
                        foreach ($lines as $line) {
                            if(!empty($line) && $line != ' ') {
                                message($line, $channel);
                            }
                        }
                    }
                } catch (Exception $e) {
                    message('ERR: ' . $e->getMessage(), $channel);
                }
            }
        }
    }
}

// supervisorctl tail php-chatbot
// supervisorctl <start|stop|restart> php-chatbot
?>