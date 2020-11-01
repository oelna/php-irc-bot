<?php
return function (string $user, string $args, string $channel) {
    if (mb_strlen(trim($args)) > 1) {
        $args = preg_replace('!\s+!', ' ', trim($args));
    	
        return 'Link to Keybase profile: https://keybase.io/'.urlencode($args).'/';
    }

    return 'Usage: !kb <keybase username>, eg: !kb oelna';
};
