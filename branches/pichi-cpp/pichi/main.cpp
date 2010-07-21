#include <gloox/client.h>
#include <gloox/messagehandler.h>
#include <gloox/message.h>
#include <gloox/mucroom.h>
#include <gloox/mucroomhandler.h>
#include <gloox/loghandler.h>
#include <gloox/connectionlistener.h>
#include <iostream>

#include "src/pichicore.h"

using namespace gloox;

class ForumBot : public MessageHandler, MUCRoomHandler, LogHandler, ConnectionListener
{
	private:
		std::string name;
		std::string nick;
		std::string password;
		std::string server;
		std::string port;
		std::string roomname;
                std::string roomservice;
		JID jid;
		JID roomjid;
	protected:
		Client* client;
		MUCRoom* room;
		pichicore* pichi;
		
		void botstart(void)
		{
			client = new Client( jid, password );
                        client->logInstance().registerLogHandler(LogLevelDebug, LogAreaAll, this);
                        client->registerConnectionListener( this );
                        client->registerMessageHandler( this );
                        room = new MUCRoom(client, roomjid, this, NULL);
                        client->connect();
		}
	public:
                ForumBot(std::string n, std::string p, std::string s) : name(n), password(p), server(s)
		{
                        nick = name;
			jid = name + "@" + server;
                        roomname = "main";
                        roomservice = "conference";
                        roomjid = JID(roomname + "@" + roomservice + "." + server + "/" + nick);
                        // ----------------
			botstart();
		}
		
                void onConnect()
                 {
			room->join();
                 }
                void onDisconnect  (ConnectionError e){}
                bool onTLSConnect  (const CertInfo  &info){return true;}

                void handleMessage( const Message& msg, MessageSession* session = 0 )
                {
                    std::cout << msg.body()  << std::endl;
                }
                
                void handleMUCMessage (MUCRoom *thisroom, const Message &msg, bool priv )
                {
                    std::cout << msg.body()  << std::endl;
                }

                void handleMUCParticipantPresence (MUCRoom *thisroom, const MUCRoomParticipant  participant, const Presence  &presence){}
                bool handleMUCRoomCreation  (MUCRoom  *thisroom){return true; }
                void handleMUCSubject (MUCRoom *thisroom, const std::string &nick, const std::string &subject){}
                void handleMUCInviteDecline (MUCRoom *thisroom, const JID &invitee, const std::string &reason){}
                void handleMUCError (MUCRoom *thisroom, StanzaError error){}
                void handleMUCInfo (MUCRoom *thisroom, int features, const std::string &name, const DataForm *infoForm){}
                void handleMUCItems (MUCRoom *thisroom, const Disco::ItemList &items){}

                void handleLog (LogLevel level, LogArea area, const std::string &message)
                {
                    std::cout << message << std::endl;
                }

                ~ForumBot()
                {
                	delete client;
                	delete room;
			delete pichi;
                }

};

int main(void)
{
    ForumBot bot("pichi", "iampichi", "jabber.uruchie.org");
    return 0;
}
