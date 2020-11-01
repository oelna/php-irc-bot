<?php
return function (string $user, string $args, string $channel) {
    if ($args) {
        $args = preg_replace('!\s+!', ' ', $args);
    	$params = explode(' ', $args);

    	// remove empty values
    	$params = array_filter($params, function($value) { return !is_null($value) && $value !== ''; });
    	
        return 'Send an encrypted message to '.$params[0].' at: https://keybase.io/encrypt';
    }

    return 'Send an encrypted message with Keybase: https://keybase.io/encrypt';
};
