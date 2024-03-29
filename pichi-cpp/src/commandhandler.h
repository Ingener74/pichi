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

#ifndef COMMANDHANDLER_H
#define COMMANDHANDLER_H

#include <string>
#include <iostream>
#include "system.h"
#include <algorithm>

class pichicore;

class commandhandler
{
  public:
    commandhandler(pichicore* p);
    ~commandhandler();
    void fetchCommand(std::string command);
    void operator () (std::string command);
  protected:
    pichicore* pichi;
    std::string last_command;
    std::string last_args;
    std::vector< std::string > seperate(std::string str, int level = 1);
};

#endif // COMMANDHANDLER_H
