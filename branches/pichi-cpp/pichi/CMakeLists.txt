PROJECT(pichi)
LINK_LIBRARIES(gloox)
LINK_LIBRARIES(pthread)
LINK_LIBRARIES(sqlite3)
LINK_LIBRARIES(tinyxml)
ADD_EXECUTABLE(pichi src/pichiconfig.cpp src/system.cpp src/core.cpp src/sqlite.cpp src/pichicore.cpp main.cpp)

