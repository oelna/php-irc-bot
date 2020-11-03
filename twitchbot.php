<?php

/*
TODO: some (not all) unicode characaters are breaking local console
filter them out
*/

error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();

// https://twitchapps.com/tmi/#access_token=xe8owdsyy6y4xkl1rbt2eg903nrqql&scope=chat%3Aread+chat%3Aedit+channel%3Amoderate+whispers%3Aread+whispers%3Aedit+channel_editor&token_type=bearer

// https://twitchapps.com/tmi/#access_token=3ip9fate9np66crjwbi28i7o00velf&scope=chat%3Aread+chat%3Aedit+channel%3Amoderate+channel_editor+channel_check_subscription&token_type=bearer

// https://id.twitch.tv/oauth2/authorize?response_type=token&client_id=g0x7jf5jcbzwo3lsna6ds9y7gv1onl&redirect_uri=https://twitchapps.com/tokengen/&scope=channel_check_subscription+channel_editor

$twitch_config = [
    'address'   => 'irc.chat.twitch.tv',
    'port'      => 6667,
    'username'  => 'oelna81',
    'password'  => 'oauth:3ip9fate9np66crjwbi28i7o00velf', // https://twitchapps.com/tmi/
    'client_id' => 'g0x7jf5jcbzwo3lsna6ds9y7gv1onl',
    'client_secret' => '7a3e6p7lws2hfg4x4qr99yz4cyawn6',
    'oauth_token' => 'k27xwky501g515v58qa2athn37sf85'
];

$twitch_channels = [
    $twitch_config['username'],
];


//require_once('altaform-core/core/afCli.inc.php');
//require_once('pudl/pudl.php');


//$pudl = pudl::instance($pudl_config);




////////////////////////////////////////////////////////////////////////////////
// SEND DATA TO TWITCH
////////////////////////////////////////////////////////////////////////////////
function twitch_send($socket, $buffer) {
    $buffer = trim($buffer);
//  echo afCli::fgCyan($buffer) . "\n";
    return socket_write($socket, $buffer."\n", strlen($buffer)+1);
}




////////////////////////////////////////////////////////////////////////////////
// CONNECT TO TWITCH
////////////////////////////////////////////////////////////////////////////////
function twitch_connect($config, $channels) {

    // CREATE SOCKET
    if (($socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
        echo    "socket_create() failed: reason: "
                . socket_strerror(socket_last_error())
                . "\n";
        exit(1);
    }


    // CONNECT SOCKET TO TWITCH
    if (socket_connect($socket, $config['address'], $config['port']) === false) {
        echo      "socket_connect() failed: reason: "
                . socket_strerror(socket_last_error($socket))
                . "\n";
        exit(1);
    }


    // SEND USERNAME AND PASSWORD INFORMATION
    twitch_send($socket, 'PASS ' . $config['password']);
    twitch_send($socket, 'NICK ' . strtolower($config['username']));


    // JOIN CHANNELS
    foreach ($channels as $channel) {
        twitch_send($socket, 'JOIN #' . strtolower($channel));
    }

    // SET NON-BLOCKING I/O
    socket_set_nonblock($socket);

    return $socket;
}




////////////////////////////////////////////////////////////////////////////////
// PROCESS A COMMAND FROM TWITCH
////////////////////////////////////////////////////////////////////////////////
function twitch_command($socket, $command) {
    //echo afCli::fgYellow($command) . "\n";
    $parts = explode(' ', $command, 4);
    if (strtoupper($parts[0]) === 'PING') {
        if (count($parts) > 1) {
            twitch_send($socket, 'PONG ' . $parts[1]);
        } else {
            twitch_send($socket, 'PONG');
        }
    }

    if (count($parts) > 3  &&  $parts[1] === 'PRIVMSG') {
        //chat($pudl, $parts);
    }
}




////////////////////////////////////////////////////////////////////////////////
// LOG A CHAT MESSAGE INTO THE DATABASE
////////////////////////////////////////////////////////////////////////////////
function chat($pudl, $parts) {
    static $channels    = [];
    static $users       = [];

    $pos        = strpos($parts[0], '!');
    if ($pos === false) return false;

    $user       = substr($parts[0], 1, $pos-1);
    $channel    = substr($parts[2], 1);
    $ret        = NULL;


    // PULL USER ID FROM DATABASE, OR INSERT IT IF IT DOESNT ALREADY EXIST
    if (empty($users[$user])) {
        $users[$user] = $pudl->cellId('users', 'user', 'uname', $user);
        if (empty($users[$user])) {
            $pudl->upsert('users', ['uname' => $user]);
            $users[$user] = $pudl->cellId('users', 'user', 'uname', $user);
        }
    }


    // PULL CHANNEL ID FROM DATABASE, OR INSERT IT IF IT DOESNT ALREADY EXIST
    if (empty($channels[$channel])) {
        $channels[$channel] = $pudl->cellId('channels', 'channel', 'cname', $channel);
        if (empty($channels[$channel])) {
            $pudl->upsert('channels', ['cname' => $channel]);
            $channels[$channel] = $pudl->cellId('channels', 'channel', 'cname', $channel);
        }
    }


    // INSERT CHAT MESSAGE INTO DATABASE
    return $pudl->insert('chatlog', [
        'user'      => $users[$user],
        'channel'   => $channels[$channel],
        'timestamp' => time(),
        'chat'      => substr($parts[3], 1),
    ]);
}


// ENABLE NON-BLOCKING I/O
//stream_set_blocking(STDIN, false);


// INITIALIZE STATE
$buffer = '';
$sleep  = 1;
//$pudl->begin();




////////////////////////////////////////////////////////////////////////////////
// MAIN PROGRAM LOOP
////////////////////////////////////////////////////////////////////////////////
while (true) {

    // (RE)CONNECT IF WE'RE NOT CURRENTLY CONNECTED
    if (empty($socket)) {
        print("Connecting to Twitch...\n");
        $buffer = '';
        $socket = twitch_connect($twitch_config, $twitch_channels);
        if (empty($socket)) {
            $sleep <<= 1;
            sleep($sleep);
            continue;
        }
    }

    // READ FROM SERVER, PROCESS DATA
    if ($status = socket_recv($socket, $out, 2048, MSG_DONTWAIT)) {
        $buffer .= $out;
        while (($pos = strpos($buffer, "\n")) !== false) {
            try {
                twitch_command($socket, trim(substr($buffer, 0, $pos)));
            } catch (pudlException $e) {
                var_dump($e);
            }
            $buffer = substr($buffer, $pos+1);
        }
    }

    // HANDLE SOCKET ERRORS (THIS WILL CAUSE A RECONNECT)
    if ( ($status === false)  &&  (socket_last_error() !== 35) ) {
        echo 'Socket error: ' . socket_last_error() . ' : ' . socket_strerror(socket_last_error());
        socket_close($socket);
        $socket = NULL;
        $sleep  = 1;
        continue;
    }

    // READ FROM LOCAL CONSOLE, SEND TYPED COMMAND TO SERVER
    if ($input = fread(STDIN, 2048)) {
        if (in_array(strtoupper(trim($input)), ['EXIT', 'QUIT'])) {
            break;
        }
        twitch_send($socket, $input);
    }


    // COMMIT CHUNKS EITHER EVERY 1000 INSERTS OR 5 SECONDS (WHICH EVER IS FIRST)
    //$pudl->chunk(1000, false, 5);

    //TODO: REPLACE THIS WITH THE STREAM WAIT FUNCTION WITH BOTH STDIN AND $SOCKET IN READ[]
    usleep(10000);
}


// CLEANUP
socket_close($socket);
//$pudl->commit();
//$pudl->disconnect();