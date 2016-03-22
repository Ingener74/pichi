## Main features: ##
  * SQLite database
  * Lua plugin system (0.7.x feature)
  * Сonfiguration via xml files and via db.
  * l10n (English, Russian, Indonesian (thanks bazar))
  * Access Levels (+privileges list)
  * Log
  * Simple built-in wiki with revisions.
  * Collects statistics of the words and generates responses
  * Users statistics and settings
  * Such services: Google Translate, Google Search, Urlshort (ur.ly), wikipedia, Last.fm.
  * And much more...
### Additional commands: ###
  * Last.fm music information.
  * Google translate.
  * Google search.
  * Wikipedia aricle text.
  * Short url's through ul.ly service.

## Packages (Last stable) ##
  * ![http://uruchie.org/~DEg/images/sysicons/windows.png](http://uruchie.org/~DEg/images/sysicons/windows.png) [Windows](http://pichi.googlecode.com/files/pichi-0.7.0b2-win32.zip)
  * ![http://uruchie.org/~DEg/images/sysicons/linux.png](http://uruchie.org/~DEg/images/sysicons/linux.png) [Linux (Source)](http://pichi.googlecode.com/files/pichi-0.7.0b2.tar.gz)
  * ![http://uruchie.org/~DEg/images/sysicons/fedora.png](http://uruchie.org/~DEg/images/sysicons/fedora.png) Fedora 16([i686](http://pichi.googlecode.com/files/pichi-0.7.0-0.5.b3.fc16.i686.rpm)/[x86\_64](http://pichi.googlecode.com/files/pichi-0.7.0-0.5.b3.fc16.x86_64.rpm))
  * ![http://uruchie.org/~DEg/images/sysicons/redhat.png](http://uruchie.org/~DEg/images/sysicons/redhat.png) [Red Hat (SRPM)](http://pichi.googlecode.com/files/pichi-0.7.0-0.5.b3.fc16.R.src.rpm)
  * ![http://uruchie.org/~DEg/images/sysicons/arch.png](http://uruchie.org/~DEg/images/sysicons/arch.png) [Arch AUR](https://aur.archlinux.org/packages.php?ID=44035) (**yaourt -S pichi**)


## Howto build ##
Get Source:
```
git clone git://github.com/eNoise/pichi.git
```
Abstract build requirements:
  * **cmake**
  * **gcc-c++ >= 4.6.0 / gcc-g++ >= 4.6.0 / mingw-gcc-g++** >= 4.6.0
  * **boost >= 1.41.0, boost-regex >= 1.41.0, boost-program\_options >= 1.41.0**
  * **libsqlite3**
  * **pthread**
  * **tinyxml (optional)**
  * **gloox (optional)**
  * **libcurl**
  * **lua (optional)**
In Ubuntu/debian:
  * **cmake**
  * **libboost-dev >= 1.41.0, libboost-regex-dev >= 1.41.0, libboost-program-options-dev >= 1.41.0**
  * **libsqlite3-dev**
  * **libtinyxml-dev (optional)**
  * **libgloox-dev (optional)**
  * **libcurl3-dev**

### Building (Linux): ###
```
mkdir build
cd build
cmake -DCMAKE_BUILD_TYPE=Release -DWITH_LUA=ON -DLOCAL_GLOOX=ON -DLOCAL_TINYXML=ON -DCMAKE_INSTALL_PREFIX='Directory to install' ../
make
make test
make install
```
  * -DWITH\_LUA=ON - Enables/Disable lua plugins.
  * -DWITH\_TESTS=ON - Enables/Disable tests after build.

  * -DLOCAL\_GLOOX=ON - Enables/Disable built-in gloox library.
  * -DLOCAL\_TINYXML=ON - Enables/Disable built-in tinyxml library.
  * -DLOCAL\_LUA=ON - Enables/Disable built-in lua library.
### Windows ###
In "Downloads" list.

## Links ##
  * http://jabber.uruchie.org - Homepage.
  * **http://wiki.jrudevels.org/Pichi - List of avalible commands.**
  * https://github.com/eNoise/pichi - Git source code.
  * http://bugs.uruchie.org/project/10 - Pichi Bugtracker (Russian)

## Demo ##
  * **room:** main@conference.jabber.uruchie.org
  * **jid:** pichi@jabber.uruchie.org
And have fun ;)