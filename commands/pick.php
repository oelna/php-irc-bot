<?php
return function (string $user, string $args, string $channel) {
    if ($args) {
        $args = preg_replace('!\s+!', ' ', $args);
    	$params = explode(' ', $args);

    	// remove empty values
    	$params = array_filter($params, function($value) { return !is_null($value) && $value !== ''; });

    	$length = sizeof($params);
    	if($length > 1) {
            return 'From '.$length.' choices I picked: '.$params[array_rand($params)];
    	}
    }

    return 'Usage: !pick [term1] [term2] … [term9], eg: !pick apple banana orange';
};
