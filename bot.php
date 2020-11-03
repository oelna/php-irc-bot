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

require_once(__DIR__.DS.'parser.php');

if(!is_dir(COMMANDS)) {
    mkdir(COMMANDS);
}

function send($string) {
    global $socket;
    echo($string.EOL);
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
    send('PASS ' . $user['password']);
}
send('NICK ' . $user['nickname']);
send('USER ' . $user['username'] . " 0 * :" . $user['realname']);

send('CAP REQ :message-tags');
send('CAP REQ :server-time');
// send('CAP REQ :echo-message');
send('CAP END');

// force an endless while
while (1) {

    // continue the rest of the script here
    while ($data = fgets($socket, 128)) {
        if(empty($data)) continue;
        
        echo $data;
        flush();

        $parsed = Parser::parse($data);
        // var_dump($parsed);
        
        // separate all data
        $ex = explode(' ', $data);

        if(!isset($parsed->command)) {
            // echo('could not parse line: '.implode(',', unpack("C*", $data)).EOL);
            continue;
        };
        
        // send PONG back to the server
        if ($parsed->command && mb_strtoupper($parsed->command) == 'PING') {
            send(str_replace('PING', 'PONG', $data));
        }

        if(!isset($parsed->command)) continue;

        // execute this after MOTD
        if ($parsed->command == 376 || $parsed->command == 422) {
            // join channels
            foreach($channels as $channel) {
                send('JOIN ' . $channel);
            }
        }

        if(empty($parsed->params)) continue;

        // handle commands
        if ($parsed->command == 'PRIVMSG' && substr($parsed->params[1], 0, 1) == '!') {
            $nick = $parsed->prefix->nick;
            $channel = $parsed->params[0];
            $parts = explode(' ', trim($parsed->params[1]));
            $command = ltrim($parts[0], '!');
            $args = '';
            if(sizeof($parts) > 1) {
                $args = implode(' ', array_slice($parts, 1));
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