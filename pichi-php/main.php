<?php

### Some settings ###
$config['db_version'] = 19; // Work only parram
$config['min_version'] = 8; // Min version of config
$config['pichi_version'] = "0.5.1"; //Pichi version

### Begin basic settings end checks ###
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
       @define(RUN_OS, "Windows");
else
       @define(RUN_OS, "Nix");

if(function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get"))
	@date_default_timezone_set(@date_default_timezone_get()); //disable timezone errors

include("parse_xml_config.php"); // init config
require_once("XMPP/XMPP.php");
require_once("pichi_functional.php");
require_once("Log_pichi.php");
require_once("System/Daemon.php");
require_once("plugins.php");
require_once("languages.php");
include("console_commands.php"); // parse command line

PichiPlugin::init(); // init plugin system
($hook = PichiPlugin::fetch_hook('main_init_plugin_system')) ? eval($hook) : false;

PichiLang::init();
PichiLang::load($config['language']);
($hook = PichiPlugin::fetch_hook('main_init_translations')) ? eval($hook) : false;

if($config['debug'] && $config['debug_level'] == 5)
	$config['xmpp_log'] = TRUE;
else
	$config['xmpp_log'] = FALSE;

if(!$config['debug'] && $config['debug_level'] > 2)
	$config['debug_level'] = 2;
if($config['debug_level'] > 4)
	$config['debug_level'] = 4;
if($config['debug_level'] < 3)
	error_reporting(E_ALL & ~E_NOTICE); //disable notices

$log = new PichiLog(true, $config['debug_level']);

function php_extension_load($ext)
{
	if (!extension_loaded($ext))
	{
		if (RUN_OS == "Windows")
		{
			if(!dl("php_$ext.dll"))
			{
				$log->log(PichiLang::get('error_cant_load_extension', array($ext)),PichiLog::LEVEL_ERROR);
				exit();
			}
		}
		else
		{
			if(!dl("$ext.so"))
			{
				$log->log(PichiLang::get('error_cant_load_extension', array($ext)),PichiLog::LEVEL_ERROR);
				exit();
			}
		}
	}
}

php_extension_load("sqlite3");
php_extension_load("mbstring");
php_extension_load("openssl");
php_extension_load("xml");

if($config['version'] < $config['min_version'])
{
	$log->log(PichiLang::get('error_config_old'),PichiLog::LEVEL_ERROR);
	$log->log(PichiLang::get('error_config_old_version', array($config['version'], $config['min_version'])), PichiLog::LEVEL_ERROR);
	exit();
}

if(RUN_OS == "Nix")
{
	echo Console_Color::convert("%b");
	echo Console_Color::convert("----------------------------------\n");
	echo Console_Color::convert("      Pichi bot v.{$config['pichi_version']}\n");
	echo Console_Color::convert("----------------------------------%n\n");
}
else
{
	echo "----------------------------------\n";
	echo "      Pichi bot v.{$config['pichi_version']}\n";
	echo "----------------------------------\n";
}

### Begin ###
$log->log("Start Pichi",PichiLog::LEVEL_INFO);
$jabber = new XMPPHP_XMPP($config['server'], $config['port'], $config['user'], $config['password'], $config['resource'], $config['server'], $config['xmpp_log'], $loglevel = (($config['debug']) ? XMPPHP_Log::LEVEL_VERBOSE : XMPPHP_Log::LEVEL_INFO));
try
{
	$log->log(PichiLang::get('info_start_connecting'),PichiLog::LEVEL_VERBOSE);
	$jabber->connect();
}

catch(XMPPHP_Exception $e)
{
	$log->log(PichiLang::get('error_connect_failed', array($e)),PichiLog::LEVEL_ERROR);
	exit();
}

// connect
$log->log(PichiLang::get('info_connect_success'), PichiLog::LEVEL_VERBOSE);
($hook = PichiPlugin::fetch_hook('main_jabber_connected')) ? eval($hook) : false;

if(file_exists($config['db_file']))
{
	$log->log(PichiLang::get('info_database_exists'),PichiLog::LEVEL_DEBUG);
	$db_exist = TRUE;
}
else
{
	$log->log(PichiLang::get('info_no_database'),PichiLog::LEVEL_DEBUG);
	$db_exist = FALSE; 
}
$log->log("Pichi Init",PichiLog::LEVEL_VERBOSE);
$pichi = new Pichi($jabber);
$pichi->_log($log);
$pichi->room = $config['room'];
$pichi->user = $config['user'];
$pichi->server = $config['server'];
$pichi->room_service = $config['room_service'];
$pichi->room_user = $config['room_user'];
$config['ignore'][] = $config['user'] . "@" . $config['server'];
$pichi->wait_time = $config['wait_time'];
// Settings XMPPHP
$jabber->autoSubscribe(); // Auto subscribe
$log->log("done!",PichiLog::LEVEL_VERBOSE);

if(!$db_exist)
{
	$log->log("Creating database structure",PichiLog::LEVEL_DEBUG);
	$pichi->_db()->query("CREATE TABLE log (`from` TEXT, `time` TEXT, `type` TEXT, `message` TEXT);");
	$pichi->_db()->query("CREATE TABLE lexems (`lexeme` TEXT, `count` INT);");
	$pichi->_db()->query("CREATE TABLE wiki (`name` TEXT, `revision` INT, `value` TEXT);");
	$pichi->_db()->query("CREATE TABLE settings (`name` TEXT, `value` TEXT, `description` TEXT);");
	$pichi->_db()->query("CREATE TABLE users (`jid` TEXT, `nick` TEXT, `role` TEXT, `room` TEXT, `time` TEXT, `status` TEXT, `level` INT);");
	$pichi->_db()->query("CREATE TABLE users_data (`jid` TEXT, `name` TEXT, `value` TEXT, `groupid` TEXT);");
	$pichi->_db()->query("CREATE TABLE users_nick (`jid` TEXT, `nick` TEXT, `room` TEXT, `time` TEXT);");
	$pichi->_db()->query("CREATE TABLE stats (`name` TEXT, `value` TEXT);");
	$pichi->_db()->query("CREATE TABLE actions (`action` TEXT, `coincidence` TEXT, `do` TEXT, `option` TEXT, `value` TEXT);");
	$pichi->_db()->query("CREATE TABLE db_version (`version` TEXT, `value` TEXT);");
  
	$pichi->_db()->query("INSERT INTO db_version (`version`) VALUES ('" . $config['db_version'] . "');");
	$pichi->_db()->query("INSERT INTO settings (`name`, `value`, `description`) VALUES ('answer_mode','1','Режим ответа на сообщения. [0 - выключить; 1 - включить][По умолчанию: 1]');"); // Отвечать после сообщений пользователей
	$pichi->_db()->query("INSERT INTO settings (`name`, `value`, `description`) VALUES ('answer_random','0','Не всегда ответь при получении сообщения. [0 - всегда отвечать; >100 фактически всегда молчать][По умолчанию: 0]');"); // отвечать не всегда (0 - всегда)
	$pichi->_db()->query("INSERT INTO settings (`name`, `value`, `description`) VALUES ('answer_remember','1','Разбивать на связки слов. [0 - выключить; 1 - включить][По умолчанию: 1]');"); // запоминать и разбивать на лексемы
	$pichi->_db()->query("INSERT INTO settings (`name`, `value`, `description`) VALUES ('answer_word_limit','10','Максимальное количество связок слов в расмотрении. Влияет на алгоритм построения фраз, так же как и на нагрузку. [Рекомендуется >3 и <50][По умолчанию: 10]');"); // limit для запросов в лексемах
	$pichi->_db()->query("INSERT INTO settings (`name`, `value`, `description`) VALUES ('log_enabled','1','Вести лог? [0 - выключить; 1 - включить][По умолчанию: 1]');"); // вести лог ?
	$pichi->_db()->query("INSERT INTO settings (`name`, `value`, `description`) VALUES ('treatment_coincidence','3','Вероятность вставки обращений. [1 - всегда; >100 фактически никогда][По умолчанию: 3]');"); // вставлять обращение, совпадения (3 из 1)
	$pichi->_db()->query("INSERT INTO settings (`name`, `value`, `description`) VALUES ('rand_message','0','Переодически отправлять случайные фразы в главный чат. [0 - выключить; 1 - включить][По умолчанию: 0]');"); // случайны ответ когда скучно
	$pichi->_db()->query("INSERT INTO settings (`name`, `value`, `description`) VALUES ('msg_limit','500','Максимальное количество символов, допустимое в главном чате (в противном случае пишет в личку) [По умолчанию: 500]');"); // лимит символов, после чего отправляет ответ в личку
	$pichi->_db()->query("INSERT INTO settings (`name`, `value`, `description`) VALUES ('msg_max_limit','0','Верхний предел для сообщения, после которого сообщение разобьет на несколько [По умолчанию: 0]');"); // вверхний предел
	
	($hook = PichiPlugin::fetch_hook('main_creating_db')) ? eval($hook) : false;
	
	$log->log("done",PichiLog::LEVEL_DEBUG);
}

$pichi->_db()->query("SELECT * FROM db_version;");
$current_db_version = $pichi->_db()->fetchColumn(0);
if($current_db_version < $config['db_version'])
{
	$log->log(PichiLang::get('error_database_old'),PichiLog::LEVEL_ERROR);
	$log->log(PichiLang::get('error_database_old_need', array($current_db_version, $config['db_version'])), PichiLog::LEVEL_ERROR);
	exit();
}

$pichi->parseOptions(); // загнать все опции
$log->log(PichiLang::get('info_timer_sync'),PichiLog::LEVEL_VERBOSE);
$time_message = $time_ping = time(); // сбрасываем
$log->log(PichiLang::get('info_session_begin'),PichiLog::LEVEL_VERBOSE);
while(!$jabber->isDisconnected()) {
	$payloads = $jabber->processUntil(array('message', 'presence', 'end_stream', 'session_start', 'ping'), 1);
	//wait for proccess
	if(count($payloads) == 2)
	{
		$data = $payloads[1];
		switch($payloads[0]) 
		{
			case 'message':
				$time_message = time();
				$log->log("Recive MESSAGE Handler From $data[body]($data[type]):\nMessage: $data[body]",PichiLog::LEVEL_DEBUG);
				$pichi->reciveMessage($data['body'], $data['from'], $data['type']);
				break;
			case 'ping':
				$pichi->recivePing($data['id']);
				break;
			case 'presence':
				$log->log("Recive PRESENCE Handler from: {$data['from']} [{$data['show']}] {$data['status']}",PichiLog::LEVEL_DEBUG);
				if($data['type'] == 'available' || $data['type'] == 'unavailable')
				{
					unset($jid, $role, $room, $nick);
					// ------------------------------------------------
					
					// From room data
					foreach($data['xml']->subs as $x)
					{
						if($x->name == 'x' && $x->subs[0]->attrs['jid'])
						{
							$jid = $pichi->getJID($x->subs[0]->attrs['jid']);
							$temp = explode('/', $data['from']);
							if($pichi->isRoom($temp[0]))
							{
								$room = $temp[0];
								$nick = $temp[1];
							}
						}
						if($x->name == 'x' && $x->subs[0]->attrs['role'])
							$role = $x->subs[0]->attrs['role'];
					}
					
					if(!$jid)
					{
						//From real jid
						$temp = explode('/', $data['from']);
						if(!$pichi->isRoom($temp[0]))
							$jid = $temp[0];
					}
					
					if(!$jid)
					{
						// From history
						$nick = $pichi->getName($data['from']);
						$jid = $pichi->getJID($nick);
					}
					
					// ------------------------------------------------
					// begin
					if(!$jid)
					{
						$log->log(PichiLang::get('warn_cant_recive_jid'),PichiLog::LEVEL_WARNING);
						break;
					}
					
					$pichi->setUserInfo($jid, $nick, $role, $room, $data['type']);

				}
				break;
			case 'session_start':
				$log->log("Recive SESSION_START Handler",PichiLog::LEVEL_DEBUG);
				$pichi->_db()->query("UPDATE users SET status = 'unavailable';");
				$jabber->getRoster();
				$jabber->presence($config['status']);
				foreach($pichi->room as $room)
					$pichi->joinRoom($room, $config['room_user'], "BotWorld!");
				$pichi->wait = $time_session = time();
				($hook = PichiPlugin::fetch_hook('main_session_start')) ? eval($hook) : false;
				break;
			case 'end_stream':
				$pichi->wait = time();
				break;
		}
	}
    
	// вставляем случайное сообщение если скучно
	if(time() - $time_message > $pichi->options['rand_message'] && $pichi->options['rand_message'] > 10) // 10 минимум секунд
	{
		$log->log(PichiLang::get('info_send_random_message'),PichiLog::LEVEL_DEBUG);
		$pichi->sendRandMessage();
	}
	
	($hook = PichiPlugin::fetch_hook('main_handle_cycle')) ? eval($hook) : false;
	$pichi->cycle(); // циклический метод
}

?>