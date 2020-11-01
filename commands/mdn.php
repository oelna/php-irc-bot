<?php
return function (string $user, string $args, string $channel) {
    if (mb_strlen(trim($args)) > 1) {
        $args = preg_replace('!\s+!', ' ', trim($args));
        $params = explode(' ', $args);

    	// remove empty values
    	$params = array_filter($params, function($value) { return !is_null($value) && $value !== ''; });

    	if(!empty($params[0]) && !empty($params[1]) && in_array(mb_strtolower($params[0]), array('html','css'))) {
    		if(mb_strtolower($params[0]) == 'css') {
    			return 'https://developer.mozilla.org/de/docs/Web/CSS/'.urlencode($params[1]);
    		} else {
    			return 'https://developer.mozilla.org/de/docs/Web/HTML/Element/'.urlencode($params[1]);
    		}
    	}
    }

    return 'Usage: !mdn <html|css> <element|property>, eg: !mdn html li';
};
