/*
    <one line to give the program's name and a brief idea of what it does.>
    Copyright (C) <2010>  <Alexey Kasyanchuk (deg@uruchie.org)>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

*/

#include "commandbase.h"
#include "pichicore.h"

commandbase::commandbase(pichicore* p): commandhandler(p)
{
	//base command array
	commands["version"] = &commandbase::command_version;
	commands["enable"] = &commandbase::command_enable;
	commands["disable"] = &commandbase::command_disable;
	commands["log"] = &commandbase::command_log;
}

void commandbase::fetchCommand(std::string command)
{
	commandhandler::fetchCommand(command);
	if(commands.find(last_command) != commands.end())
		(this->*(commands[last_command]))(last_args);
}


/*
	// Begin commands
	protected function command_help()
	{
		$help = "\n";
		$help .= "-------------------------\n";
		$help .= "        Pichi Bot        \n";
		$help .= "-------------------------\n";
		$help .= "--------------------------------------------\n";
		$help .= PichiLang::get('help_syntaxis_info') . "\n";
		$help .= "--------------------------------------------\n";
		
		$help .= "=====  " . PichiLang::get('help_main_commands') . "  =====\n";
		$help .= "!set ".PichiLang::get('help_command_usage_variable')."=".PichiLang::get('help_command_usage_value')." - ".PichiLang::get('help_command_description_set')."\n";
		$help .= "!gc [".PichiLang::get('help_command_usage_variable')."] - ".PichiLang::get('help_command_description_gc')."\n";
		$help .= "!log ".PichiLang::get('help_command_usage_param')." - ".PichiLang::get('help_command_description_log')."\n";
		$help .= "!users [".PichiLang::get('help_command_usage_nick')."|".PichiLang::get('help_command_usage_jid')."] - ".PichiLang::get('help_command_description_users')."\n";		
		$help .= "!msg [".PichiLang::get('help_command_usage_nick')."|".PichiLang::get('help_command_usage_jid')."|".PichiLang::get('help_command_usage_room')."] [type] message - ".PichiLang::get('help_command_description_msg')."\n";
		$help .= "!ping [".PichiLang::get('help_command_usage_nick')."|".PichiLang::get('help_command_usage_jid')."] - ".PichiLang::get('help_command_description_ping')."\n";
		$help .= "!join ".PichiLang::get('help_command_usage_room')." ".PichiLang::get('help_command_usage_nick')." [".PichiLang::get('help_command_usage_status')."] - ".PichiLang::get('help_command_description_join')."\n";
		$help .= "!left ".PichiLang::get('help_command_usage_room')." ".PichiLang::get('help_command_usage_nick')." [".PichiLang::get('help_command_usage_status')."] - ".PichiLang::get('help_command_description_left')."\n";
		$help .= "!greet ".PichiLang::get('help_command_usage_jid')." ".PichiLang::get('help_command_usage_room')." ".PichiLang::get('help_command_usage_message')." - ".PichiLang::get('help_command_description_greet')."\n";
		$help .= "!farewell ".PichiLang::get('help_command_usage_jid')." ".PichiLang::get('help_command_usage_room')." ".PichiLang::get('help_command_usage_message')." - ".PichiLang::get('help_command_description_farewell')."\n";
		$help .= "!idle ".PichiLang::get('help_command_usage_nick')." - ".PichiLang::get('help_command_description_idle')."\n";
		$help .= "!on - ".PichiLang::get('help_command_description_on')."\n";
		$help .= "!off - ".PichiLang::get('help_command_description_off')."\n";
		$help .= "!quit - ".PichiLang::get('help_command_description_quit')."\n";
		$help .= "!version - ".PichiLang::get('help_command_description_version')."\n";
		
		$help .= "=====  " . PichiLang::get('help_admin_commands') . "  =====\n";
		$help .= "!topic ".PichiLang::get('help_command_usage_param')." - ".PichiLang::get('help_command_description_topic')."\n";
		$help .= "!ban ".PichiLang::get('help_command_usage_jid')."|".PichiLang::get('help_command_usage_nick')." [".PichiLang::get('help_command_usage_time')."] [".PichiLang::get('help_command_usage_reason')."] - ".PichiLang::get('help_command_description_ban')."\n";
		$help .= "!unban ".PichiLang::get('help_command_usage_jid')." - ".PichiLang::get('help_command_description_jid')."\n";
		$help .= "!banlist - ".PichiLang::get('help_command_description_banlist')."\n";
		$help .= "!kick ".PichiLang::get('help_command_usage_nick')."|".PichiLang::get('help_command_usage_jid')." [".PichiLang::get('help_command_usage_time')."] [".PichiLang::get('help_command_usage_reason')."] - ".PichiLang::get('help_command_description_kick')."\n";
		$help .= "!unkick ".PichiLang::get('help_command_usage_jid')." - ".PichiLang::get('help_command_description_unkick')."\n";
		
		$help .= "=====  " . PichiLang::get('help_plugins') . "  =====\n";
		$help .= "!plugins - ".PichiLang::get('help_command_description_plugins')."\n";
		$help .= "!enable ".PichiLang::get('help_command_usage_param')." - ".PichiLang::get('help_command_description_enable')."\n";
		$help .= "!disable ".PichiLang::get('help_command_usage_param')." - ".PichiLang::get('help_command_description_disable')."\n";
		$help .= "!reload - ".PichiLang::get('help_command_description_reload')."\n";

		$help .= "=====  " . PichiLang::get('help_wiki_inline') . "  =====\n";
		$help .= "!dfn ".PichiLang::get('help_command_usage_param')."=".PichiLang::get('help_command_usage_value')." - ".PichiLang::get('help_command_description_dfn')."\n";
		$help .= "!wtf ".PichiLang::get('help_command_usage_param')." - ".PichiLang::get('help_command_description_wtf')."\n";
		$help .= "!wtfcount - ".PichiLang::get('help_command_description_wtfcount')."\n";
		$help .= "!wtfrand - ".PichiLang::get('help_command_description_wtfrand')."\n";
		$help .= "!wtfrev ".PichiLang::get('help_command_usage_param')." - ".PichiLang::get('help_command_description_wtfrev')."\n";
		$help .= "!wtfull ".PichiLang::get('help_command_usage_param')." - ".PichiLang::get('help_command_description_wtffull')."\n";
		$help .= "!wtfset ".PichiLang::get('help_command_usage_param')." - ".PichiLang::get('help_command_description_wtfset')."\n";

		$help .= "=====  " . PichiLang::get('help_lexems') . "  =====\n";
		$help .= "!top - ".PichiLang::get('help_command_description_top')."\n";
		$help .= "!count - ".PichiLang::get('help_command_description_count')."\n";
		$help .= "!talkers - ".PichiLang::get('help_command_description_talkers')."\n";
		$help .= "!nicks ".PichiLang::get('help_command_usage_param')." - ".PichiLang::get('help_command_description_nicks')."\n";

		$help .= "=====  " . PichiLang::get('help_other_commands') . "  =====\n";
		($hook = PichiPlugin::fetch_hook('commands_show_help')) ? eval($hook) : false;
		
		$this->sendAnswer($help);
	}
*/

void commandbase::command_version(std::string null)
{
	//global $config;
	pichi->sendAnswer("Pichi Bot v.C++");
	//($hook = PichiPlugin::fetch_hook('commands_show_version')) ? eval($hook) : false;
	//$this->sendAnswer("".PichiLang::get('command_version_plugins').":\n" . PichiPlugin::show_plugin_list());
}

void commandbase::command_enable(std::string arg)
{
	if(!pichi->isAccess())
		return;
	
	//PichiPlugin::enable(system::atoi(arg));
}

void commandbase::command_disable(std::string arg)
{
	if(!pichi->isAccess())
		return;
	
	//PichiPlugin::disable(system::atoi(arg));
}

/*
	protected function command_reload()
	{
		if(!$this->isAccess())
			return;
		
		PichiPlugin::reload();
	}
	
	protected function command_plugins()
	{
		$this->sendAnswer("".PichiLang::get('command_version_plugins').":\n" . PichiPlugin::show_plugin_list());
	}
*/

/*
	protected function command_kick()
	{
		if(!$this->isAccess())
			return;
		
		$w = $this->seperate($this->last_message, 3);
		$this->kick($w[1], $w[2], $w[3]);
	}
	
	protected function command_unkick()
	{
		if(!$this->isAccess())
			return;
		
		$w = $this->seperate($this->last_message);
		$this->unkick($w[1]);
	}
*/

/*

	protected function command_ban()
	{
		if(!$this->isAccess())
			return;
		
		$w = $this->seperate($this->last_message, 3);
		$this->ban($w[1], $w[2], $w[3]);
	}
	
	protected function command_unban()
	{
		if(!$this->isAccess())
			return;
		
		$w = $this->seperate($this->last_message, 2);
		$this->unban($w[1], $w[2]);
	}

*/

/*
	protected function command_banlist()
	{
		$this->db->query("SELECT `jid`,`value` FROM users_data WHERE name = 'ban';");
		$banlist = "";
		while($bans = $this->db->fetchArray())
		{
			$banlist .= $bans['jid'] . " " . date("d.m.y \в H:i:s", $bans['value']) . "\n";
		}
		$this->sendAnswer("".PichiLang::get('command_banlist_lock').":\n" . $banlist);
	}
*/

void commandbase::command_log(std::string arg)
{
	 	pichi->sql->query("SELECT * FROM log ORDER BY time;");
		int qu_i = pichi->sql->numRows();
		int n = (arg != "") ? (system::atoi(arg)) : 20;
		int i = 0;
		std::string log = "\n-----------------------------------------------------------------------\n";
		std::map<std::string, std::string> data;
		while(!(data = pichi->sql->fetchArray()).empty())
		{
			if(i < n && i < 50 && qu_i <= n)
			{
				log += "[" + system::timeToString(system::atot(data["time"]), "%H:%M:%S") + "]<" + pichi->getName(data["from"]) + "> " + data["message"] + "\n";
				i++;
			}
			qu_i--;
		}
		log += "-----------------------------------------------------------------------";
		//$this->log->log("Request log:\n$log", PichiLog::LEVEL_VERBOSE);
		pichi->sendAnswer(log); 
}
	