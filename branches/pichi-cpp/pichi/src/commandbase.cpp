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
}

void commandbase::command_version(std::string null)
{
	//global $config;
	pichi->sendAnswer("Pichi Bot v.C++");
	//($hook = PichiPlugin::fetch_hook('commands_show_version')) ? eval($hook) : false;
	//$this->sendAnswer("".PichiLang::get('command_version_plugins').":\n" . PichiPlugin::show_plugin_list());
}
