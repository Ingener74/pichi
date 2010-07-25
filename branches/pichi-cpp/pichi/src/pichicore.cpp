/*
    <Pichi jabber bot>
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

#include "pichicore.h"

pichicore::pichicore()
{
	enabled = true;
	wait_time = 5;
}

pichicore::~pichicore()
{
	delete sql;
	delete jabber;
}

bool pichicore::isEnabled(void)
{
	return enabled;
}

void pichicore::setUserInfo(std::string jid, std::string nick, std::string state, std::string room, std::string role, std::string status)
{
	//if($role == NULL)
	//	$role = "participant"; //Default permission
  
	int level = 1; // defaul access level
	
	sql->query("SELECT `status` FROM users WHERE jid = '" + sql->escapeString(jid) + "' AND room = '" + sql->escapeString(room) + "';");
	std::string old_state = sql->fetchColumn(0);
	
	if(config["global_admins"] == "1")
		if(role == "moderator")
			level = 2;

	std::vector< std::string > admins, ignore;
	
	admins = system::explode(",", config["admins"]);
	if(admins.size() > 0)
		if(system::in_array(jid, admins))
			level = 3;
	
	ignore = system::explode(",", config["ignore"]);
	if(ignore.size() > 0)
		if(system::in_array(jid, ignore))
			level = 0;

	if(time(NULL) - wait > wait_time)
	{
		if(state == "available" && old_state == "unavailable")
		{
			//$this->event->catchEvent("user_join_room", "room=$room,jid=$jid");
			// autokick
			//	$this->db->query("SELECT COUNT(*) FROM users_data WHERE jid = '" . $this->db->db->escapeString($jid) . "' AND name = 'kick' AND groupid = '" . $this->db->db->escapeString($room) ."';");
			//	if($this->db->fetchColumn() > 0)
			//		$this->jabber->kick($this->getName($this->getJID($jid, $room), $room), $room, NULL);
			}
		else if(state == "unavailable" && old_state == "available")
		{
			//$this->event->catchEvent("user_left_room", "room=$room,jid=$jid");
		}
	}
	
	//($hook = PichiPlugin::fetch_hook('pichicore_status_set')) ? eval($hook) : false;
	//$this->log->log("Updating user status for $nick($jid) in $room = $status", PichiLog::LEVEL_DEBUG);
	
	sql->query("SELECT COUNT(*) FROM users WHERE jid = '" + sql->escapeString(jid) + "' AND room = '" + sql->escapeString(room) + "';");
	if(system::atoi(sql->fetchColumn(0)) > 0)
	{
		sql->exec("UPDATE users SET nick = '" + sql->escapeString(nick) + "', time = '" + system::stringTime(time(NULL)) + "', status = '" + state + "', role = '" + sql->escapeString(role) + "', level = '" + system::itoa(level) + "' WHERE jid = '" + sql->escapeString(jid) + "' AND room = '" + sql->escapeString(room) + "';");
		//($hook = PichiPlugin::fetch_hook('pichicore_status_update')) ? eval($hook) : false;
	}
	else
	{
		sql->exec("INSERT INTO users (`jid`,`nick`,`role`,`room`,`time`,`status`,`level`) VALUES ('" + sql->escapeString(jid) + "','" + sql->escapeString(nick) + "','" + sql->escapeString(role) + "','" + sql->escapeString(room) + "','" + system::stringTime(time(NULL)) + "','" + state + "', '" + system::itoa(level) + "');");
		sql->query("SELECT COUNT(*) FROM users WHERE jid = '" + sql->escapeString(jid) + "';");
		//if($this->db->fetchColumn() == 0)
		//		($hook = PichiPlugin::fetch_hook('pichicore_status_create')) ? eval($hook) : false;
	}
		
		sql->query("SELECT COUNT(*) FROM users_nick WHERE jid = '" + sql->escapeString(jid) + "' AND nick = '" + sql->escapeString(nick) + "' AND room = '" + sql->escapeString(room) + "';");
		if(system::atoi(sql->fetchColumn(0)) == 0)
			sql->exec("INSERT INTO users_nick (`jid`,`nick`,`room`,`time`) VALUES ('" + sql->escapeString(jid) + "','" + sql->escapeString(nick) + "','" + sql->escapeString(room) + "','" + system::stringTime(time(NULL)) + "');");
}

