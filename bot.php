<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Europe/Berlin');
set_time_limit(0); // Prevent PHP from stopping the script after 30 sec

DEFINE('DS', DIRECTORY_SEPARATOR);
DEFINE('EOL', "\n");
DEFINE('COMMANDS', __DIR__.DS.'commands');
if(!is_dir(COMMANDS)) { mkdir(COMMANDS); }

require_once(__DIR__.DS.'parser.php');

// load config
if(isset($argv[1]) && file_exists($argv[1])) {
    echo('using custom config file: '.$argv[1].EOL);
    $config = require_once($argv[1]);
} else {
    echo('using default config file: config.php'.EOL);
    // load the default config
    $config = require_once(__DIR__.DIRECTORY_SEPARATOR.'config.php');
}
$c = $config;

function send($string) {
    global $socket;
    fputs($socket, $string . EOL);
}

function message($string, $channel) {
    send('PRIVMSG '.$channel.' :'.$string);
}

// opening the socket to the network
if(!$socket = fsockopen(($c['server']['ssl'] ? 'ssl://' : '').$c['server']['url'], $c['server']['port'])) {
    die('Could not establish connection using '.$c['server']['url'].':'.$c['server']['port']);
};

// set timout to 1 second
if (!stream_set_timeout($socket, 1)) { die('Could not set timeout'); }

// send auth info
if(!empty($c['user']['password'])) {
    send('PASS ' . $c['user']['password']);
}
send('NICK ' . $c['user']['nickname']);
send('USER ' . $c['user']['username'] . ' 0 * :' . $c['user']['realname']);

// special capabilities for twitch
if(substr_compare($c['server']['url'], '.twitch.tv', -strlen('.twitch.tv')) === 0) {
    send('CAP REQ :twitch.tv/membership');
    send('CAP REQ :twitch.tv/tags');
    send('CAP REQ :twitch.tv/commands');
} else {
    send('CAP REQ :message-tags');
    send('CAP REQ :server-time');
    send('CAP REQ :echo-message');
}
send('CAP END');

// force an endless while
while (1) {

    // actual message receiving
    while ($data = fgets($socket, 512)) { // set to 512 to match IRC message length limit
        flush();
        
        echo($data);

        // separate all data
        $parsed = Parser::parse($data);

        if($parsed === null || !isset($parsed->command)) {
            echo('could not parse line: '.$data);
            echo('characters: '.implode(',', unpack('C*', $data)).EOL);
            continue;
        };
        
        // send PONG back to the server
        if ($parsed->command && mb_strtoupper($parsed->command) == 'PING') {
            send(str_replace('PING', 'PONG', $data));
        }

        // execute this after MOTD
        if ($parsed->command == 376 || $parsed->command == 422) {
            // join channels
            foreach($c['channels'] as $channel) {
                send('JOIN ' . $channel);
            }
        }

        // examples for how to react to messages
        if(mb_strtoupper($parsed->command) == 'JOIN') {
            echo((($parsed->prefix->isServer) ? $parsed->prefix->host : $parsed->prefix->nick).' joined channel '.$parsed->params[0].EOL);
        }
        
        if(mb_strtoupper($parsed->command) == 'PART') {
            echo((($parsed->prefix->isServer) ? $parsed->prefix->host : $parsed->prefix->nick).' left channel '.$parsed->params[0].EOL);
        }

        if(empty($parsed->params)) continue;

        // handle !commands
        if ($parsed->command == 'PRIVMSG' && substr($parsed->params[1], 0, 1) == '!') {
            $nick = ($parsed->prefix->isServer) ? $parsed->prefix->host : $parsed->prefix->nick;
            $channel = $parsed->params[0];
            $parts = explode(' ', trim($parsed->params[1]));
            $command = ltrim($parts[0], '!');
            $args = '';
            if(sizeof($parts) > 1) {
                $args = implode(' ', array_slice($parts, 1));
            }

            // look for custom command function
            $file = COMMANDS.DS.trim($command).'.php';
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

?>