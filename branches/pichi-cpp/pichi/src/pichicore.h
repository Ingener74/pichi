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

#ifndef PICHICORE_H
#define PICHICORE_H

#include "sqlite.h"
#include "pichiconfig.h"
#include "lastmessage.h"
#include "system.h"
#include <string>
#include <vector>
#include <time.h>

class core;

class pichicore : public pichiconfig, lastmessage
{
	public:
		sqlite *sql;
		core *jabber;
		
		time_t wait;
		time_t wait_time;
		
		bool isEnabled(void);
		void setUserInfo(std::string, std::string, std::string, std::string, std::string, std::string);
		void cleanUserInfo(void);
		bool reciveMessage(std::string message, std::string type, std::string from, std::string jid = "", std::string room = "", int level = 2);
		
		bool isJID(std::string&);
		std::string getJID(std::string nick, std::string room = "", bool full_search = false);
		std::string getName(std::string jid, std::string room = "");
		std::string getDefaultRoom(void);
		
		pichicore();
		~pichicore();
	protected:
		bool enabled;
		
		bool isAccess(int level = 2, std::string jid = "", std::string room = "", bool room_hook = false);
	private:
};

#endif // PICHICORE_H
