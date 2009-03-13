#Customisable stuff here
LINUX32_COMPILER = gcc
LINUX64_COMPILER = gcc

LIBPURPLE_CFLAGS = `pkg-config --cflags purple`
GLIB_CFLAGS = `pkg-config --cflags glib-2.0`

FACEBOOK_SOURCES = \
	libfacebook.h \
	libfacebook.c \
	fb_blist.h \
	fb_blist.c \
	fb_connection.h \
	fb_connection.c \
	fb_info.h \
	fb_info.c \
	fb_managefriends.h \
	fb_managefriends.c \
	fb_messages.h \
	fb_messages.c \
	fb_notifications.h \
	fb_notifications.c \
	fb_search.h \
	fb_search.c

#Standard stuff here
.PHONY:	all clean install

all:	libfacebook.so

install:
	cp libfacebook.so /usr/lib64/purple-2/

clean:
	rm -f libfacebook.so libfacebook.dll libfacebook64.so libfacebookarm.so libfacebookppc.so pidgin-facebookchat.exe pidgin-facebookchat.deb pidgin-facebookchat.tar.bz2 pidgin-facebookchat-source.tar.bz2
	rm -rf pidgin-facebookchat

libfacebook.so:	${FACEBOOK_SOURCES}
	${LINUX32_COMPILER} ${LIBPURPLE_CFLAGS} -Wall ${GLIB_CFLAGS} -I. -g -O2 -pipe ${FACEBOOK_SOURCES} -o libfacebook.so -shared -fPIC -DPIC

