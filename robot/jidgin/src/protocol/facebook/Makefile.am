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
purple_LTLIBRARIES = libfacebook.la

libfacebook_la_SOURCES = fb_blist.c \
                         fb_connection.c \
                         fb_info.c \
                         fb_managefriends.c \
                         fb_messages.c \
                         fb_notifications.c \
                         fb_search.c \
                         fb_json.c \
                         fb_util.c \
                         fb_friendlist.c \
                         libfacebook.c

libfacebook_la_LDFLAGS = -module -avoid-version
libfacebook_la_LIBADD = $(PURPLE_LIBS) $(GLIB_LIBS) ${GJSON1_LIBS}

AM_CPPFLAGS = \
        -DDATADIR=\"$(PURPLE_DATADIR)\" \
	-DDISPLAY_VERSION='"0.98.4"' \
	-DPURPLE_PLUGINS \
	-W -Wall -Wno-unused-parameter \
	-std=gnu99 \
	$(PURPLE_CFLAGS) \
	$(GLIB_CFLAGS) \
	$(GJSON1_CFLAGS) \
	$(DEBUG_CFLAGS)
