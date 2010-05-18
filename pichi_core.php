 <?php
 
require_once("database.php");
require_once("syntax_analizer.php");
require_once("eventHandler.php");

/* Main Functional Core */
class PichiCore
{
	// Base objects
	protected $db;
	protected $jabber;
	protected $log;
	protected $syntax;
	protected $event;

	// Configure options
	public $options;
	
	// Account settings
	public $server;
	public $room_service;
	public $room;
	public $user;
	public $room_user;
    
	// Last message settings
	protected $last_massage;
	protected $last_from;
	protected $last_type;
	protected $last_room;
	// Last data recive settings
	public $last_id;                                           
	public $last_data;
	
	// Timers
	public $wait;
	public $wait_time = 5;
	public $cycle_interval = 5;
    
	// Privilegy
	public $admins = array();
	public $ignore = array();
    
	
	
	public function __construct(&$jabber)
	{
		$this->db = new PichiDatabase();
		$this->jabber = &$jabber;
		$this->db->log = & $this->log;
		$this->syntax = new SyntaxAnalizer();
		$this->syntax->db = & $this->db;
		$this->syntax->log = & $this->log;
		$this->event = new eventHandler();
		$this->event->db = & $this->db;
		$this->event->log = & $this->log;
		$this->event->jabber = & $this->jabber;
	}

	//some stubs
	public function _db()
	{
		return $this->db;
	}
	
	public function _log(& $log)
	{
		$this->log = $log;
	}

	public function cycle()
	{
		$this->cycle_interval--;
		// begin
		if($this->cycle_interval <= 0)
		{
			$this->cycle_interval = 5;
			$this->log->log("do CYCLE()", PichiLog::LEVEL_VERBOSE);
			// ----------------------
			// Unban
			$this->db->query("SELECT `jid`,`value`,`name` FROM users_data WHERE name = 'ban' OR name = 'kick';");
			while($bans = $this->db->fetch_array())
			{
				if((int)$bans['value'] <= time())
				{
					if($bans['name'] == 'ban')
						$this->unban($bans['jid']);
					else if($bans['name'] == 'kick')
						$this->unkick($bans['jid']);
				}
			}
		}
	}
	
	protected function ban($jid, $time = NULL, $reason = NULL)
	{
		$this->jabber->ban(($jid = $this->getJID($jid)), $this->room, (($reason) ? $reason : NULL));
		if($time != NULL)
		{
			$time = $this->convertTime($time);
			$this->setJIDinfo($jid, 'ban', $time + time());
			$this->setJIDinfo($jid, 'ban_reason', $reason);
		}
	}
	
	protected function unban($jid, $reason = NULL)
	{
		$jid = $this->getJID($jid);
		$this->jabber->unban($jid, $this->room, (($reason) ? $reason : NULL));
		$this->delJIDinfo($jid, 'ban');
		$this->delJIDinfo($jid, 'ban_reason');
	}
	
	protected function kick($jid, $time = NULL, $reason = NULL)
	{
		$this->jabber->kick($this->getName($jid = $this->getJID($jid)), $this->room, (($reason) ? $reason : NULL));
		if($time != NULL)
		{
			$time = $this->convertTime($time);
			$this->setJIDinfo($jid, 'kick', $time + time());
			$this->setJIDinfo($jid, 'kick_reason', $reason);
		}
	}
	
	protected function unkick($jid)
	{
		$jid = $this->getJID($jid);
		$this->delJIDinfo($jid, 'kick');
		$this->delJIDinfo($jid, 'kick_reason');
	}
	
	protected function convertTime(){} // заглушка

	public function joinRoom($room, $nick, $status = "BotWorld!")
	{
		if(strpos($room, "@") === FALSE)
			$room .= "@" . $this->room_service . "." . $this->server;
		$this->jabber->presence($status, 'available', $room."/".$nick);
		$this->wait = time();
		($hook = PichiPlugin::fetch_hook('pichicore_room_join')) ? eval($hook) : false;
		$this->setUserInfo($this->user."@".$this->server, $nick, NULL, $room, 'available');
		$this->log->log("Join to room $room as $nick", PichiLog::LEVEL_DEBUG);
	}
	
	public function leftRoom($room, $nick, $status)
	{
	  	if(strpos($room, "@") === FALSE)
			$room .= "@" . $this->room_service . "." . $this->server;
		$this->jabber->presence($status, 'unavailable', $room."/".$nick);
		($hook = PichiPlugin::fetch_hook('pichicore_room_left')) ? eval($hook) : false;
		$this->setUserInfo($this->user."@".$this->server, $nick, NULL, $room, 'unavailable');
		$this->log->log("Left room $room as $nick", PichiLog::LEVEL_DEBUG);
	}

	public function ping($jid, $id = "pichi_ping")
	{
		$jid = $this->getJID($jid); // На всякий случай
		$this->log->log("Send ping to $jid", PichiLog::LEVEL_DEBUG);
		$this->jabber->ping($jid, $id);
		$this->last_data['ping_time'] = microtime(true);                   
		$this->last_id['ping'] = $id;
	}
	
	public function recivePing($id)
	{
		if($id == $this->last_id['ping'])
		{
			$time = microtime(true) - $this->last_data['ping_time'];
			$time = number_format($time, 3); // до 3 символов
			$this->log->log("Recived ping if $time", PichiLog::LEVEL_DEBUG);
			$this->sendAnswer("Pong in $time sec.");
		}
	}

	// get JID from nick
	public function getJID($nick)
	{
		$this->log->log("Get JID from $nick", PichiLog::LEVEL_VERBOSE);
		if(strpos($nick, "@") === FALSE)
		{
			$this->db->query("SELECT `jid` FROM users WHERE nick = '" . $this->db->db->escapeString($nick) . "';");
			$jid = $this->db->fetchColumn(0);
			if($jid != NULL)
				return $jid;
			else
				return false;
		}
		else
		{
			$mes=explode("/",$nick);
			return $mes[0];
		}
	}

	// get Jid from last messager
	public function getJIDlast()
	{
		if($this->last_type == "groupchat")
			return $this->getJID($this->getName($this->last_from));
		else
			return $this->getJID($this->last_from);	
	}

	// get name from jid
	public function getName($jid)
	{
		$this->log->log("Get Nick from JID $jid", PichiLog::LEVEL_VERBOSE);
		if(strpos($jid, "@") === FALSE)
			return $jid;
		if(strpos($jid, "/") === FALSE)
		{
			$this->db->query("SELECT `nick` FROM users WHERE jid = '" . $this->db->db->escapeString($jid) . "';");
			$name = $this->db->fetchColumn(0);
			if($name != NULL)
				return $name;
			else
				return false;
		}
		else
		{
			$mes=explode("/", $jid);
			return $mes[1];
		}
	}

	// Отправляет ответ тому, от кого пришло сообщение
	protected function sendAnswer($message)
	{
		if($this->last_type == "groupchat")
			$to = $this->last_room;
		else
			$to = $this->last_from;
		$type = $this->last_type;
		($hook = PichiPlugin::fetch_hook('pichicore_answer_send')) ? eval($hook) : false;
		if(strlen($message) < $this->options['msg_limit'] || $this->options['msg_limit'] < 1 || $this->last_type != "groupchat")
			$this->jabber->message($to, $message, $type);
		else
			$this->jabber->message($this->getJID($this->getName($this->last_from)), $message, "chat");
		$this->log->log("Send answer to $to:\n$message", PichiLog::LEVEL_VERBOSE);
	}

	//Вспомогательная функция с поиском пользователя в списке
	//допустемые значения: jid, ник
	protected function inUser(&$array_users, $user)
	{
		if(in_array($this->getJID($user), $array_users))
		{
			return true;
		}
		else
		{	  
			$this->db->query("SELECT `jid`,`nick` FROM users WHERE status = 'available';");
			while($users = $this->db->fetch_array())
			{
				if($users['nick'] == $this->getName($user))
					if(in_array($users['jid'], $array_users))
						return true;
			}
		}
		return false;
	}

	protected function isAccess()
	{
		$this->log->log("Test access ". $this->last_from . " in admins", PichiLog::LEVEL_VERBOSE);
		return $this->inUser($this->admins, $this->last_from);
	}
	
	protected function isIgnore()
	{
		$this->log->log("Test ignore ". $this->last_from . " in ignorelist", PichiLog::LEVEL_VERBOSE);
		return $this->inUser($this->ignore, $this->last_from);
	}
    
	protected function isOnline($user)
	{
		$this->db->query("SELECT `jid`,`nick` FROM users WHERE status = 'available';");
		while($users = $this->db->fetch_array())
		{
			if($users['nick'] == $user || $users['jid'] == $user)
				return true;
		}
		return false;
	}
    
	public function doExit()
	{
		global $config;
		$this->log->log("Do disconnect", PichiLog::LEVEL_INFO);
		if($this->isAccess())
			$this->jabber->disconnect();
		if($config['daemon_mode'])
			System_Daemon::stop();
	}
    
	public function parseOptions()
	{
		$this->db->query("SELECT * FROM settings;");
		$this->log->log("Parse Settings", PichiLog::LEVEL_DEBUG);
		while($data = $this->db->fetch_array())
		{
			$this->options[$data['name']] = $data['value'];
			$this->log->log("$data[name] = $data[value]", PichiLog::LEVEL_VERBOSE);
		}
		$this->postParseOptions();
	}
	
	// Значения, которые должны немедленно измениться после вызова парсера.
	private function postParseOptions()
	{
		//Syntaxis limit
		$this->syntax->query_limit = (int)$this->options['answer_word_limit'];
		//...
		($hook = PichiPlugin::fetch_hook('pichicore_options_parse')) ? eval($hook) : false;
	}
    
	/*
	* Вставляет инофрмацию о пользователе
	* jid, nick, время
	*/
	public function setUserInfo($jid, $nick, $role, $room, $status)
	{      
		if($role == NULL)
			$role = "participant"; //Default permission
			
		// Old status test
		$this->db->query("SELECT `status` FROM users WHERE jid = '" . $this->db->db->escapeString($jid) . "' AND room = '" . $this->db->db->escapeString($room) . "';");
		$old_status = $this->db->fetchColumn(0);
			
		//Admins add
		global $config;
		if($config['global_admins'])
		{
			if(!in_array($jid, $this->admins) && $role == "moderator")
				$this->admins[] = $jid;
		}
		
		if(time() - $this->wait > $this->wait_time)
		{
			if($status == 'available' && $old_status == 'unavailable')
			{
				$this->event->catchEvent("user_join_room", "room=$room,jid=$jid");
				// autokick
				$this->db->query("SELECT COUNT(*) FROM users_data WHERE jid = '" . $this->db->db->escapeString($jid) . "' AND name = 'kick';");
				if($this->db->fetchColumn() > 0)
					$this->jabber->kick($this->getName($this->getJID($jid)), $this->room, NULL);
			}
			else if($status == 'unavailable' && $old_status == 'available')
			{
				$this->event->catchEvent("user_left_room", "room=$room,jid=$jid");
			}
		}
		
		($hook = PichiPlugin::fetch_hook('pichicore_status_set')) ? eval($hook) : false;
		
		$this->log->log("Updating user status for $nick($jid) in $room = $status", PichiLog::LEVEL_DEBUG);
		$this->db->query("SELECT COUNT(*) FROM users WHERE jid = '" . $this->db->db->escapeString($jid) . "' AND room = '" . $this->db->db->escapeString($room) . "';");
		if($this->db->fetchColumn() > 0)
		{
			$this->db->query("UPDATE users SET nick = '".$this->db->db->escapeString($nick)."', time = '".time()."', status = '".$status."', role = '" . $this->db->db->escapeString($role) . "' WHERE jid = '".$this->db->db->escapeString($jid)."' AND room = '". $this->db->db->escapeString($room) ."';");
			($hook = PichiPlugin::fetch_hook('pichicore_status_update')) ? eval($hook) : false;
		}
		else
		{
			$this->db->query("INSERT INTO users (`jid`,`nick`,`role`,`room`,`time`,`status`) VALUES ('" . $this->db->db->escapeString($jid) . "','".$this->db->db->escapeString($nick)."','" . $this->db->db->escapeString($role) . "','". $this->db->db->escapeString($room) ."','".time()."','".$status."');");
			$this->db->query("SELECT COUNT(*) FROM users WHERE jid = '" . $this->db->db->escapeString($jid) . "';");
			if($this->db->fetchColumn() == 0)
				($hook = PichiPlugin::fetch_hook('pichicore_status_create')) ? eval($hook) : false;
		}
	}
    
        private function isCommand($command)
        {
                return (substr($command,0,1) == "!");
        }
    
	public function reciveMessage($message, $from, $type)
	{
		// Tests
		// No reaction while time off
		if(time() - $this->wait < $this->wait_time)
		{
			$this->log->log("Ignore Message: <$from> $message", PichiLog::LEVEL_DEBUG);
			return false;
		}
		
		if($message == NULL || $from == NULL || $type == NULL)
			return false;
		
		// Remember
		$this->last_message = $message;
		$this->last_from = $from;
		$this->last_type = $type;
		if($this->last_type == "groupchat")
			$this->last_room = $this->getJID($this->last_from);
		else
			$this->last_room = NULL;
	
		($hook = PichiPlugin::fetch_hook('pichicore_message_recive_begin')) ? eval($hook) : false;
		$this->log->log("Call message method", PichiLog::LEVEL_DEBUG);
		
		if(!$this->isCommand($message) && $this->options['log_enabled'] == 1)
			$this->db->query("INSERT INTO log (`from`,`time`,`type`,`message`) VALUES ('".$this->db->db->escapeString($this->last_from)."','".$this->db->db->escapeString(time())."','".$this->db->db->escapeString($this->last_type)."','".$this->db->db->escapeString($this->last_message)."');");
		
		//to lexems massges
		if(!$this->isIgnore() && !$this->isCommand($this->last_message) && $this->options['answer_remember'] == 1)
			$this->syntax->parseText($this->last_message);
		
		($hook = PichiPlugin::fetch_hook('pichicore_message_recive_complete')) ? eval($hook) : false;
		
		return true;

	}

	public function sendRandMessage()
	{
		if($this->options['rand_message'] > 10) //10 вменяемый лимит секунд
		{
			$this->log->log("Rand message method", PichiLog::LEVEL_DEBUG);
			$this->db->query("SELECT `nick` FROM users WHERE status = 'available';");
			$user = array();
			$n = 0;
			while($users = $this->db->fetch_array())
			{
				$n++;
				$user[] = $users['nick'];
			}
			$this->syntax->generate();
			switch(rand(1,2))
			{
				case 1:
					$this->jabber->message($this->room, $user[rand(0, $n-1)] . ": " . $this->syntax->returnText(), "groupchat");
					break;
				case 2:
					$this->jabber->message($this->room, $user[rand(0, $n-1)] . ", " . $this->syntax->returnText(), "groupchat");
					break;		
			}
		}
	}
	
	// устанавливает опцию для пичи
	// true - если установило
	// flase - нету опции
	public function setOption($option, $value)
	{
		$this->db->query("SELECT COUNT(*) FROM settings WHERE name = '" . $this->db->db->escapeString($option) . "';");
		if($this->db->fetchColumn() > 0)
		{
			$this->db->query("UPDATE settings SET value = '".$this->db->db->escapeString($value)."'  WHERE name = '".$this->db->db->escapeString($option)."';");
			$this->parseOptions();
			$this->log->log("Updated option $option = $value", PichiLog::LEVEL_DEBUG);
			return true;
		}
		else
		{
			$this->log->log("Can't set $option. There is no such option.", PichiLog::LEVEL_DEBUG);
			return false;
		}
	}
	
	// устанавливает информацию о jid
	// true - если установило
	// flase - нету опции
	public function setJIDinfo($jid, $name, $value)
	{
		$this->db->query("SELECT COUNT(*) FROM users_data WHERE jid = '" . $this->db->db->escapeString($jid) . "' AND name = '" . $this->db->db->escapeString($name) . "';");
		if($this->db->fetchColumn() > 0)
			$this->db->query("UPDATE users_data SET value = '".$this->db->db->escapeString($value)."'  WHERE jid = '" . $this->db->db->escapeString($jid) . "' AND name = '".$this->db->db->escapeString($name)."';");
		else
			$this->db->query("INSERT INTO users_data (`jid`,`name`,`value`) VALUES ('" . $this->db->db->escapeString($jid) . "','" . $this->db->db->escapeString($name) . "','" . $this->db->db->escapeString($value) . "');");
	}
	
	// а теперь получить инфу
	// return array
	public function getJIDinfo($jid, $name = NULL)
	{
		$array = FALSE; // изначально не масив =)
		$this->db->query("SELECT * FROM users_data WHERE jid = '" . $this->db->db->escapeString($jid) . "'" . (($name != NULL) ? " AND name = '" . $this->db->db->escapeString($name) . "'" : "") . ";");
		while($data = $this->db->fetch_array())
			$array[$data['name']] = $data['value'];
		return $array;
	}
	
	// ну и удалить
	// return bool
	public function delJIDinfo($jid, $name = NULL)
	{
		$array = FALSE; // изначально не масив =)
		$this->db->query("DELETE FROM users_data WHERE jid = '" . $this->db->db->escapeString($jid) . "'" . (($name != NULL) ? " AND name = '" . $this->db->db->escapeString($name) . "'" : "") . ";");
		return true;
	}

}

?>