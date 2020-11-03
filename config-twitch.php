<?php

// config file for use with Twitch IRC

return array(
	'server' => array(
		'url' => 'irc.chat.twitch.tv',
		'port' => 6697,
		'ssl' => true
	),
	// your user data
	'user' => array(
		'username' => 'oelna81', // this does not matter
		'realname' => 'Arno Richter', // this does not matter
		'nickname' => 'oelna81', // this has to be your twitch username
		'password' => 'oauth:3ip9fate9np66crjwbi28i7o00velf' // https://twitchapps.com/tmi/
	),
	// which channels to join on login
	'channels' => array(
		'#oelna81' // this also has to be your twitch username aka. channel name
	)
);
