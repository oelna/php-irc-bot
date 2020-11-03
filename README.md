# PHP IRC Chatbot

A minimal IRC Chatbot written in PHP following a subset of [RFC1459](https://tools.ietf.org/html/rfc1459).

This is originally a fork of https://github.com/Hammster/php-irc-bot

**I updated it with:**

- external structured config files, can be specified via command line
- customizable chat commands in separate `.php` files, eg. [`random.php`](/commands/random.php) or [`chance.php`](/commands/chance.php)
- convenience functions for sending raw messages and `PRIVMSG`
- improved channel joining for better RFC compatibility
- a total `PHP` rewrite of [this parser class](https://github.com/oelna/websocket-irc/blob/master/parser.js)! Allows for more sensible handling of incoming messages
- in some places, different error handling
- SSL/TLS support
- Twitch support

## Usage

- configure values in a config file, like `yourconfig.php`
- add or modify commands in the `commands` dir, following the examples
- run `php ./bot.php yourconfig.php` in you command line or set up a daemon (supervisord, etc.)
- (can run several bots at once if you specify different config files!)

## Example config file

### For use with regular IRC servers

```
return array(
	'server' => array(
		'url' => 'chat.freenode.net',
		'port' => 6697,
		'ssl' => true
	),
	'user' => array(
		'username' => 'oelnabot',
		'realname' => 'Arno Richter',
		'nickname' => 'oelnabot',
		'password' => ''
	),
	'channels' => array( // channels to auto-join on connect
		'##linux-beginners',
		'##crypto'
	)
);
```

### For use with Twitch IRC

Get your OAuth token here: https://twitchapps.com/tmi/

```
return array(
	'server' => array(
		'url' => 'irc.chat.twitch.tv',
		'port' => 6697,
		'ssl' => true
	),
	'user' => array(
		'username' => 'oelna81',
		'realname' => 'Arno Richter',
		'nickname' => 'oelna81',
		'password' => 'oauth:3ip9fate9np66crjwbi28i7o00velf'
	),
	'channels' => array(
		'#oelna81'
	)
);
```

## Run as daemon

### Supervisord

This is mostly a note to myself, in case I need to deal with this again:  
(This assumes `php-chatbot` is set as name in your `.ini`)

See last output: `supervisorctl tail php-chatbot`  
Start service: `supervisorctl start php-chatbot`  
Stop service: `supervisorctl stop php-chatbot`  
Restart service: `supervisorctl restart php-chatbot` (eg. after file modifications)  