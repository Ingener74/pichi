PROJECT(pichi)
LINK_LIBRARIES(gloox)
LINK_LIBRARIES(pthread)
LINK_LIBRARIES(sqlite3)
ADD_EXECUTABLE(pichi src/config.cpp src/system.cpp src/core.cpp src/sqlite.cpp src/pichicore.cpp main.cpp)

