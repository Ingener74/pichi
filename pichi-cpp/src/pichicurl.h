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

#ifndef PICHICURL_H
#define PICHICURL_H

#include <curl/curl.h>
#include <string>

class pichicurl
{
  public:
    pichicurl();
    ~pichicurl();
    std::string readurl(std::string u);
    std::string read(void);
    void setUrl(std::string u);
    void setReferer(std::string u);
    std::string urlencode(std::string u);
    std::string urldecode(std::string u);
  private:
	char errorBuffer[CURL_ERROR_SIZE];
	std::string buffer;
	CURL *curl;  
	CURLcode result;
	std::string url;
	static int writer(char *data, size_t size, size_t nmemb,  
                  std::string *buffer);
};

#endif // PICHICURL_H
