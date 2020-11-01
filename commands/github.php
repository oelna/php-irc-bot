<?php
return function (string $user, string $args, string $channel) {
    if (mb_strlen(trim($args)) > 1) {
        $args = preg_replace('!\s+!', ' ', trim($args));
    	
        return 'Link to Github profile: https://github.com/'.urlencode($args).'/';
    }

    return 'Usage: !github <github username>, eg: !github oelna';
};
