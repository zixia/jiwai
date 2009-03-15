# Copyright (C) 2008 Alexander Logvinov <ports@logvinov.com>
#  
# This file is free software; as a special exception the author gives
# unlimited permission to copy and/or distribute it, with or without 
# modifications, as long as this notice is preserved.
# 
# This program is distributed in the hope that it will be useful, but
# WITHOUT ANY WARRANTY, to the extent permitted by law; without even the
# implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

purpledir = $(PURPLE_LIBDIR)/purple-2
purple_LTLIBRARIES = libfetion.la

libfetion_la_SOURCES = fetion.c \
	f_buddy.c  \
	f_chat.c  \
	f_gchat.c \
	f_zone.c \
	f_group.c  \
	f_login.c  \
	f_sysmsg.c \
	f_portrait.c \
	f_user.c  \
	f_util.c  \
	sipmsg.c

libfetion_la_LDFLAGS = -module -avoid-version
libfetion_la_LIBADD = $(PURPLE_LIBS) $(GLIB_LIBS)

AM_CPPFLAGS = \
        -DDATADIR=\"$(PURPLE_DATADIR)\" \
	-DDISPLAY_VERSION='"0.98.4"' \
	-DPURPLE_PLUGINS \
	-W -Wall -Wno-unused-parameter \
	-std=gnu99 \
	$(PURPLE_CFLAGS) \
	$(GLIB_CFLAGS) \
	$(DEBUG_CFLAGS)
