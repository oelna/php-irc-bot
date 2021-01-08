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

$connected = false;
$interval = 0;
$reconnect_attempt = 1;
$reconnect_time = 0;
$notified_admin = false;

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
    if(!@fputs($socket, $string . EOL)) {
        echo('socket connection error'.EOL);
    }
}

function message($string, $channel) {
    send('PRIVMSG '.$channel.' :'.$string);
}

function connect() {
    global $c;
    global $socket;
    global $reconnect_attempt;

    echo('connecting … try #'.$reconnect_attempt.EOL);

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
}

connect();

// force an endless while
while (1) {

    // handle reconnect
    if ($connected == false && $reconnect_time > 0) {
        echo('reconnecting in '.($reconnect_time-$interval).EOL);
        if ($reconnect_time <= $interval) {
            // todo: handle date wrap!
            echo('reconnecting NOW!'.EOL);
            $reconnect_time = 0;
            connect();
        }

        $interval += 1;
        sleep(1);
        continue;
    }

    // periodic messages
    $minuteinterval = 60;
    if ($interval > 0 && $interval % ($minuteinterval*60) === 0) {
        message('Denk dran, wenn du eine Frage hast, oder etwas am Thema nicht verstehst, frag einfach direkt. Es ist einfacher, als es später nachzuholen.', '#oelna81');
        message('Es gibt verschiedene praktische Kommandos, die man im Chat eingeben kann, zB. !int oder !html, um Info über die Fächer zu bekommen. Um eine Liste zu sehen, tippe einfach !commands', '#oelna81');
    }
    if($interval > 60*60*24) $interval = 0; // wrap days
    $interval += 1;

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

        if ($parsed->command && mb_strtoupper($parsed->command) == 'NOTICE') {
            if (strpos($parsed->params[1], 'authentication failed') !== false) {
                echo 'AN ERROR OCCURRED DURING LOGIN ATTEMPT '.$reconnect_attempt.'! ('.$interval.')'.EOL;
                $reconnect_time = 30*$reconnect_attempt + $interval;
                $reconnect_attempt += 1;

                if ($reconnect_attempt > 20 && !$notified_admin) {
                    $notified_admin = true;
                    @mail($c['admin']['email'], 'error connecting chatbot', 'you should check this error! '.__FILE__);
                }

                // maybe time_sleep_until()?
                // todo: handle error
                continue;
            }
        }

        if ($parsed->command && mb_strtoupper($parsed->command) == '001') {
            // connected successfully
            $connected = true;
            $reconnect_attempt = 1;
            $reconnect_time = 0;
            $notified_admin = false;
            echo('connection successful'.EOL);
        }
        
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
                    $result = $fn($nick, $args, $channel, $parsed);
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