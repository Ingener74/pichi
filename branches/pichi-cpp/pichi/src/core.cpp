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

#include "core.h"

void core::botstart(void)
{
	client = new Client( jid, password );
	client->logInstance().registerLogHandler(LogLevelDebug, LogAreaAll, this);
	client->registerConnectionListener( this );
	client->registerMessageHandler( this );
	room = new MUCRoom(client, roomjid, this, NULL);
	client->connect();
}

core::core(std::string n, std::string p, std::string s) : name(n), password(p), server(s)
{
	nick = name;
	jid = name + "@" + server;
	roomname = "main";
	roomservice = "conference";
	roomjid = JID(roomname + "@" + roomservice + "." + server + "/" + nick);
	// ----------------
	botstart();
}

core::~core()
{
	delete client;
	delete room;
	delete pichi;
}

void core::onConnect()
{
	room->join();
}

void core::handleMessage( const Message& msg, MessageSession* session = 0 )
{
	std::cout << msg.body()  << std::endl;
}

void core::handleMUCMessage (MUCRoom *thisroom, const Message &msg, bool priv )
{
	std::cout << msg.body()  << std::endl;
}

void core::handleMUCParticipantPresence (MUCRoom *thisroom, const MUCRoomParticipant  participant, const Presence &presence)
{
	if(presence.presence() != Presence::Unavailable)
	{
		std::cout << "Онлайн: " << participant.jid->full() << std::endl;
	}
	else
	{
		std::cout << "Оффлайн: " << participant.jid->full() << std::endl;
	}
}

void core::handleLog (LogLevel level, LogArea area, const std::string &message)
{
	//std::cout << message << std::endl;
}