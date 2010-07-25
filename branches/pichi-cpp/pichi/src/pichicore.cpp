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
#include "core.h"

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

void pichicore::cleanUserInfo(void)
{
	sql->exec("UPDATE users SET status = 'unavailable';");
}

bool pichicore::isJID(std::string& jid)
{
	return (std::find(jid.begin(), jid.end(), '@') != jid.end());
}


std::string pichicore::getJID(std::string nick, std::string room, bool full_search)
{
	//$this->log->log("Get JID from $nick", PichiLog::LEVEL_VERBOSE);
	if(isJID(nick))
		return nick;
		
	if(room == std::string())
		room = getDefaultRoom(); // main room
	
	sql->query("SELECT `jid` FROM users WHERE nick = '" + sql->escapeString(nick) + "' AND room = '" + sql->escapeString(room) + "';");
	std::string jid = sql->fetchColumn(0);
	
	if(jid != std::string())
	{
		return jid;
	}
	else
	{
		if(full_search)
		{
			sql->query("SELECT `jid` FROM users_nick WHERE nick = '" + sql->escapeString(nick) + "' AND room = '" + sql->escapeString(room) + "' ORDER BY `time` ASC;");
			jid = sql->fetchColumn(0);
			if(jid != std::string())
				return jid;
		}
		return false;
	}
}

std::string pichicore::getDefaultRoom(void)
{
	std::list< std::pair<JID, MUCRoom*> >::iterator first_room;
	first_room = jabber->rooms.begin();
	return (*first_room).first.bare();
}

std::string pichicore::getName(std::string jid, std::string room)
{
	//$this->log->log("Get Nick from JID $jid", PichiLog::LEVEL_VERBOSE);
	if(!isJID(jid))
		return jid;
	
	std::vector< std::string > exp = system::explode("/", jid);
	if(exp.size() != 2)
	{
		if(room == std::string())
			room = getDefaultRoom(); // main room
		sql->query("SELECT `nick` FROM users WHERE jid = '" + sql->escapeString(jid) + "' AND room = '" + sql->escapeString(room) + "';");
		std::string name = sql->fetchColumn(0);
		return name;
	}
	else
	{
		std::vector< std::string >::iterator it = exp.end();
		it--;
		return (*it);
	}
}
