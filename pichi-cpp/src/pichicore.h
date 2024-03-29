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
#include "pichioptions.h"
#include "lastmessage.h"
#include "system.h"
#include <string>
#include <vector>
#include <time.h>
#include <gloox/jid.h>
#include "commandbase.h"
#include "languages.h"
#include "lexemes.h"
#include "pichievent.h"
//#include <boost/timer.hpp>

class core;

class pichicore : public pichiconfig, public lastmessage, public pichioptions
{
	public:
		sqlite *sql;
		core *jabber;
		commandbase *commander;
		languages *lang;
		lexemes *lex;
		PichiEvent *event;
		
		time_t wait;
		time_t wait_time;
		std::map<std::string, std::string> reciver;
		//boost::timer timer;
		
		void on(void);
		void off(void);
		
		bool isEnabled(void);
		void setUserInfo(std::string, std::string, std::string, std::string, std::string, std::string);
		void cleanUserInfo(void);
		bool reciveMessage(std::string message, std::string type, std::string from, std::string jid = "", std::string room = "", int level = 2);
		void sendAnswer(std::string message);
		
		bool isJID(std::string&);
		std::string getJID(std::string nick, std::string room = "", bool full_search = false);
		std::string getName(std::string jid, std::string room = "");
		std::string getDefaultRoom(void);
		bool isCommand(std::string& str);
		bool isAccess(int level = 2, std::string jid = "", std::string room = "", bool room_hook = false);
		bool setOption(std::string option, std::string value);
		bool isOnline(std::string user, std::string room = "");
		void ping(std::string jid);
		void pingRecive(std::string id);
		std::string getJIDlast(void);
		
		void setJIDinfo(std::string jid, std::string name, std::string value, int groupid = 0);
		std::map<std::string, std::string> getJIDinfo(std::string jid, std::string name = "", int groupid = 0);
		void delJIDinfo(std::string jid, std::string name = "", int groupid = 0);
		
		pichicore();
		~pichicore();
	protected:
		bool enabled;
	private:
};

#endif // PICHICORE_H
