<?php

// https://modern.ircdocs.horse/

class Parser {

	// https://github.com/williamkapke/irc-replies
	public static $replycodes = array("001" => "RPL_WELCOME", "002" => "RPL_YOURHOST", "003" => "RPL_CREATED", "004" => "RPL_MYINFO", "005" => "RPL_ISUPPORT", "010" => "RPL_BOUNCE", "015" => "RPL_MAP", "017" => "RPL_MAPEND", "018" => "RPL_MAPSTART", "020" => "RPL_HELLO", "042" => "RPL_YOURID", "043" => "RPL_SAVENICK", "200" => "RPL_TRACELINK", "201" => "RPL_TRACECONNECTING", "202" => "RPL_TRACEHANDSHAKE", "203" => "RPL_TRACEUNKNOWN", "204" => "RPL_TRACEOPERATOR", "205" => "RPL_TRACEUSER", "206" => "RPL_TRACESERVER", "207" => "RPL_TRACESERVICE", "208" => "RPL_TRACENEWTYPE", "209" => "RPL_TRACECLASS", "211" => "RPL_STATSLINKINFO", "212" => "RPL_STATSCOMMANDS", "213" => "RPL_STATSCLINE", "214" => "RPL_STATSNLINE", "215" => "RPL_STATSILINE", "216" => "RPL_STATSKLINE", "217" => "RPL_STATSQLINE", "218" => "RPL_STATSYLINE", "219" => "RPL_ENDOFSTATS", "221" => "RPL_UMODEIS", "231" => "RPL_SERVICEINFO", "232" => "RPL_ENDOFSERVICES", "233" => "RPL_SERVICE", "234" => "RPL_SERVLIST", "235" => "RPL_SERVLISTEND", "239" => "RPL_STATSIAUTH", "240" => "RPL_STATSVLINE", "241" => "RPL_STATSLLINE", "242" => "RPL_STATSUPTIME", "243" => "RPL_STATSOLINE", "244" => "RPL_STATSHLINE", "245" => "RPL_STATSSLINE", "246" => "RPL_STATSPING", "247" => "RPL_STATSBLINE", "248" => "RPL_STATSDEFINE", "249" => "RPL_STATSDEBUG", "250" => "RPL_STATSDLINE", "251" => "RPL_LUSERCLIENT", "252" => "RPL_LUSEROP", "253" => "RPL_LUSERUNKNOWN", "254" => "RPL_LUSERCHANNELS", "255" => "RPL_LUSERME", "256" => "RPL_ADMINME", "257" => "RPL_ADMINLOC1", "258" => "RPL_ADMINLOC2", "259" => "RPL_ADMINEMAIL", "261" => "RPL_TRACELOG", "262" => "RPL_TRACEEND", "263" => "RPL_TRYAGAIN", "265" => "RPL_LOCALUSERS", "266" => "RPL_GLOBALUSERS", "300" => "RPL_NONE", "301" => "RPL_AWAY", "302" => "RPL_USERHOST", "303" => "RPL_ISON", "304" => "RPL_TEXT", "305" => "RPL_UNAWAY", "306" => "RPL_NOWAWAY", "311" => "RPL_WHOISUSER", "312" => "RPL_WHOISSERVER", "313" => "RPL_WHOISOPERATOR", "314" => "RPL_WHOWASUSER", "315" => "RPL_ENDOFWHO", "316" => "RPL_WHOISCHANOP", "317" => "RPL_WHOISIDLE", "318" => "RPL_ENDOFWHOIS", "319" => "RPL_WHOISCHANNELS", "321" => "RPL_LISTSTART", "322" => "RPL_LIST", "323" => "RPL_LISTEND", "324" => "RPL_CHANNELMODEIS", "325" => "RPL_UNIQOPIS", "331" => "RPL_NOTOPIC", "332" => "RPL_TOPIC", "333" => "RPL_TOPIC_WHO_TIME", "341" => "RPL_INVITING", "342" => "RPL_SUMMONING", "344" => "RPL_REOPLIST", "345" => "RPL_ENDOFREOPLIST", "346" => "RPL_INVITELIST", "347" => "RPL_ENDOFINVITELIST", "348" => "RPL_EXCEPTLIST", "349" => "RPL_ENDOFEXCEPTLIST", "351" => "RPL_VERSION", "352" => "RPL_WHOREPLY", "353" => "RPL_NAMREPLY", "361" => "RPL_KILLDONE", "362" => "RPL_CLOSING", "363" => "RPL_CLOSEEND", "364" => "RPL_LINKS", "365" => "RPL_ENDOFLINKS", "366" => "RPL_ENDOFNAMES", "367" => "RPL_BANLIST", "368" => "RPL_ENDOFBANLIST", "369" => "RPL_ENDOFWHOWAS", "371" => "RPL_INFO", "372" => "RPL_MOTD", "373" => "RPL_INFOSTART", "374" => "RPL_ENDOFINFO", "375" => "RPL_MOTDSTART", "376" => "RPL_ENDOFMOTD", "381" => "RPL_YOUREOPER", "382" => "RPL_REHASHING", "383" => "RPL_YOURESERVICE", "384" => "RPL_MYPORTIS", "385" => "RPL_NOTOPERANYMORE", "391" => "RPL_TIME", "392" => "RPL_USERSSTART", "393" => "RPL_USERS", "394" => "RPL_ENDOFUSERS", "395" => "RPL_NOUSERS", "401" => "ERR_NOSUCHNICK", "402" => "ERR_NOSUCHSERVER", "403" => "ERR_NOSUCHCHANNEL", "404" => "ERR_CANNOTSENDTOCHAN", "405" => "ERR_TOOMANYCHANNELS", "406" => "ERR_WASNOSUCHNICK", "407" => "ERR_TOOMANYTARGETS", "408" => "ERR_NOSUCHSERVICE", "409" => "ERR_NOORIGIN", "411" => "ERR_NORECIPIENT", "412" => "ERR_NOTEXTTOSEND", "413" => "ERR_NOTOPLEVEL", "414" => "ERR_WILDTOPLEVEL", "415" => "ERR_BADMASK", "416" => "ERR_TOOMANYMATCHES", "421" => "ERR_UNKNOWNCOMMAND", "422" => "ERR_NOMOTD", "423" => "ERR_NOADMININFO", "424" => "ERR_FILEERROR", "431" => "ERR_NONICKNAMEGIVEN", "432" => "ERR_ERRONEOUSNICKNAME", "433" => "ERR_NICKNAMEINUSE", "434" => "ERR_SERVICENAMEINUSE", "435" => "ERR_SERVICECONFUSED", "436" => "ERR_NICKCOLLISION", "437" => "ERR_UNAVAILRESOURCE", "441" => "ERR_USERNOTINCHANNEL", "442" => "ERR_NOTONCHANNEL", "443" => "ERR_USERONCHANNEL", "444" => "ERR_NOLOGIN", "445" => "ERR_SUMMONDISABLED", "446" => "ERR_USERSDISABLED", "451" => "ERR_NOTREGISTERED", "461" => "ERR_NEEDMOREPARAMS", "462" => "ERR_ALREADYREGISTRED", "463" => "ERR_NOPERMFORHOST", "464" => "ERR_PASSWDMISMATCH", "465" => "ERR_YOUREBANNEDCREEP", "466" => "ERR_YOUWILLBEBANNED", "467" => "ERR_KEYSET", "471" => "ERR_CHANNELISFULL", "472" => "ERR_UNKNOWNMODE", "473" => "ERR_INVITEONLYCHAN", "474" => "ERR_BANNEDFROMCHAN", "475" => "ERR_BADCHANNELKEY", "476" => "ERR_BADCHANMASK", "477" => "ERR_NOCHANMODES", "478" => "ERR_BANLISTFULL", "481" => "ERR_NOPRIVILEGES", "482" => "ERR_CHANOPRIVSNEEDED", "483" => "ERR_CANTKILLSERVER", "484" => "ERR_RESTRICTED", "485" => "ERR_UNIQOPRIVSNEEDED", "491" => "ERR_NOOPERHOST", "492" => "ERR_NOSERVICEHOST", "499" => "ERR_STATSKLINE", "501" => "ERR_UMODEUNKNOWNFLAG", "502" => "ERR_USERSDONTMATCH", "708" => "RPL_ETRACEFULL", "759" => "RPL_ETRACEEND");
	
	public function __construct($params) {
		
	}

	// via https://github.com/crccheck/crc-irc
	public static function parse($data) {
		// tags source/prefix command parameters
		$message = new stdClass();
		$message->raw = $data;
		$message->tags = array();
		$message->prefix = array();
		$message->command = null;
		$message->commandString = null;
		$message->params = array();
		
		$position = 0;
		$nextspace = 0;

		// remove EOL characters
		$data = rtrim($data);

		if (mb_substr($data, 0, 1) === '@') {
			$nextspace = strpos($data, ' ');
			

			if ($nextspace === false) {
				// Malformed IRC message.
				return null;
			}

			$rawTags = explode(';', substr($data, 1, $nextspace));

			for ($i = 0; $i < sizeof($rawTags); $i++) {
				$tag = $rawTags[$i];
				$pair = explode('=', $tag);
				if(isset	($pair[1])) {
					$message->tags[$pair[0]] = $pair[1];
				} else {
					$message->tags[$pair[0]] = null;
				}
			}

			$position = $nextspace + 1;
		}

		while (mb_substr($data, $position, 1) === ' ') {
			$position += 1;
		}

		if (mb_substr($data, $position, 1) === ':') {
			$nextspace = strpos($data, ' ', $position);

			if ($nextspace === false) {
				// Malformed IRC message.
				return null;
			}

			$message->prefix = substr($data, $position + 1, $nextspace - $position - 1);
			$position = $nextspace + 1;
			
			while (mb_substr($data, $position, 1) === ' ') {
				$position += 1;
			}
		}

		// parse prefix
		$message->prefix = self::parsePrefix($message->prefix);

		$nextspace = strpos($data, ' ', $position);

		if ($nextspace === false) {
			if (mb_strlen($data) > $position) {
				$message->command = substr($data, $position);
				return $message;
			}

			return null;
		}

		$message->command = substr($data, $position, $nextspace - $position);

		if (is_numeric($message->command)) {
			// save a human readable status in case it's a numeric code
			$message->commandString = self::codeToReply($message->command);
		}

		$position = $nextspace + 1;

		while (mb_substr($data, $position, 1) === ' ') {
			$position += 1;
		}

		while ($position < mb_strlen($data)) {
			$nextspace = strpos($data, ' ', $position);

			if (mb_substr($data, $position, 1) === ':') {
				$message_end = substr($data, $position + 1);
				$further_params = explode(' :', $message_end);
				
				$message->params = array_merge($message->params, $further_params);

				break;
			}

			if ($nextspace !== false) {
				$message->params[] = substr($data, $position, $nextspace - $position);
				$position = $nextspace + 1;

				while (mb_substr($data, $position, 1) === ' ') {
					$position += 1;
				}

				continue;
			}

			if ($nextspace === false) {
				$message->params[] = substr($data, $position);
				break;
			}
		}
		return $message;
	}

	/* https://github.com/sigkell/irc-prefix-parser */
	public static function parsePrefix($prefix) {
		if (!$prefix || mb_strlen($prefix) === 0) {
			return null;
		}

		if (mb_substr($prefix, 0, 1) === ':') {
			$prefix = ltrim($prefix, ':');
		}

		$dpos = strpos($prefix, '.') + 1;
		$upos = strpos($prefix, '!') + 1;
		$hpos = strpos($prefix, '@', $upos) + 1;
		
		if ($upos === 2 || $hpos === 2) {
			return null;
		}

		$result = new stdClass();
		$result->raw = $prefix;
		$result->isServer = false;
		$result->nick = null;
		$result->user = null;
		$result->host = null;
		
		if ($upos > 1) {
			$result->nick = substr($prefix, 0, $upos - 1);
			if ($hpos > 1) {
				$result->user = substr($prefix, $upos, $hpos - 1 - $upos);
				$result->host = substr($prefix, $hpos);
			} else {
				$result->user = substr($prefix, $upos);
			}
		} else if ($hpos > 1) {
			$result->nick = substr($prefix, 0, $hpos - 1);
			$result->host = substr($prefix, $hpos);
		} else if ($dpos > 1) {
			$result->host = $prefix;
			$result->isServer = true;
		} else {
			$result->nick = $prefix;
		}

		return $result;
	}

	public static function codeToReply($numericCode) {
		if (!is_numeric($numericCode)) {
			return null;
		}

		return (isset(self::$replycodes[(string) $numericCode])) ? self::$replycodes[(string) $numericCode] : null;
	}
};