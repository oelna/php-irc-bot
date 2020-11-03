<?php

// config
$server = array(
    'url' => 'irc.chat.twitch.tv',
    'port' => 6697,
    'ssl' => true
);

// your user data
$user = array(
    'username' => 'oelna81',
    'realname' => 'Arno Richter',
    'nickname' => 'oelna81',
    'password' => 'oauth:3ip9fate9np66crjwbi28i7o00velf' // https://twitchapps.com/tmi/
);

// which channels to join on login
$channels = array(
    '#'.$user['nickname']
);

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

    fputs($socket, $string . EOL);
}

function message($string, $channel) {
    $message = 'PRIVMSG '.$channel.' :'.$string;
    send($message);
    echo($message.EOL);
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

send('CAP REQ :twitch.tv/membership');
send('CAP REQ :twitch.tv/tags');
send('CAP REQ :twitch.tv/commands');
send('CAP END');

foreach($channels as $channel) {
    fputs($socket, "JOIN ".$channel."\n");
}

// Set timout to 1 second
if (!stream_set_timeout($socket, 1)) die("Could not set timeout");

while(1) {
        
    while($data = fgets($socket)) {
        flush();

        $parsed = Parser::parse($data);

        // ping pong
        if($parsed->command && mb_strtoupper($parsed->command) == "PING") {
            send(str_replace('PING', 'PONG', $data));
        } else {
            echo $data;
        }

        if($parsed->command == "353") {
            
        }
        elseif($parsed->command == "421") {
            echo('invalid command!'.EOL);
        }
        elseif($parsed->command == "JOIN") {
            echo($parsed->prefix->nick.' joined the chat.'.EOL);
        }
        elseif($parsed->command == "PART") {
            
        }
        elseif($parsed->command == "MODE") {
            // Add mods
        }
        elseif($parsed->command == "PRIVMSG") {

            if ($parsed->command == 'PRIVMSG' && substr($parsed->params[1], 0, 1) == '!') {
                $nick = $parsed->prefix->nick;
                $channel = $parsed->params[0];
                $parts = explode(' ', trim($parsed->params[1]));
                $command = ltrim($parts[0], '!');
                $args = '';
                if(sizeof($parts) > 1) {
                    $args = implode(' ', array_slice($parts, 1));
                }

                message('oelnabot: testing something', $channels[0]);
            }
        }
    }
    
    if (!feof($socket)) {
        continue;
    }
    
    sleep(1);
}
?>