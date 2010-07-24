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

#include "system.h"

bool system::fileExists(std::string file)
{
	struct stat stFileInfo;
	int intStat;

	intStat = stat(file.c_str(),&stFileInfo);
	if(intStat == 0) {
		return true;
	} else {
		return false;
	} 
}

std::string system::timeToString(time_t t, std::string format)
{
	struct tm * ptm = localtime(&t);
	char buf[100];
	strftime (buf, 100, format.c_str(),  ptm);
	std::string result(buf);
	delete ptm;
	return result;
}