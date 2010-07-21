/*
    <one line to give the program's name and a brief idea of what it does.>
    Copyright (C) 2010  Alexey Kasyanchuk (mailto:deg@uruchie.org)

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

#include "sqlite.h"

sqlite::sqlite(std::string f):dbfile(f)
{
	int rc;
	rc = sqlite3_open(dbfile.c_str(), &db);
	if( rc )
	{
		std::cout << "Db file open failed" << std::endl;
		sqlite3_close(db);
		return;
	}
}

sqlite::~sqlite()
{
	finalize();
	sqlite3_close(db);
}

bool sqlite::query(std::string sql)
{  
	if(sql != last_query_string)
	{
		last_query_string = sql;
		last_query_status = sqlite3_prepare_v2(db, sql.c_str(), -1, &statement, 0);
	}
		
	if(last_query_status == SQLITE_OK)
		return true;
	else
		return false;
}

std::multimap<std::string, std::string> sqlite::fetchArray()
{
	std::multimap<std::string, std::string> row;
	int result;
	
	result = sqlite3_step(statement);
	if(result == SQLITE_ROW)
	{
		int cols = sqlite3_column_count(statement);
		for(int col = 0; col < cols; col++)
			row.insert(std::pair<std::string, std::string>((char*)sqlite3_column_name(statement, col), (char*)sqlite3_column_text(statement, col)));
		
		return row;
	}
}

std::string sqlite::fetchColumn(int num)
{
	std::string ret;
	int result = sqlite3_step(statement);
	if(result == SQLITE_ROW)
		return static_cast<std::string>((char*)sqlite3_column_text(statement, num));
	else
		return NULL;
}
  
const int sqlite::numColumns() const
{
	return sqlite3_column_count(statement);
}

const int sqlite::numRows() const
{
	return sqlite3_data_count(statement);
}

bool sqlite::reset()
{
	if (sqlite3_reset(statement) == SQLITE_OK)
		return true;
	else
		return false;
}

void sqlite::finalize()
{
	sqlite3_finalize(statement);
}
