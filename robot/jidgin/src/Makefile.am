SUBDIRS = protocol/fetion protocol/facebook
AUTOMAKE_OPTIONS = foreign
AM_CFLAGS = $(PURPLE_CFLAGS) ${GLIB2_CFLAGS} ${GTHREAD2_CFLAGS}
bin_PROGRAMS = jidgin
jidgin_SOURCES = jidgin.c \
                 reactor.c \
		 log.c \
		 robotmsg.c \
		 daemon.c \
		 worker.c \
		 buddy.c \
		 text.c \
		 setting.c \
		 intercept.c
		 jidgin.h \
                 reactor.h \
		 log.h \
		 robotmsg.h \
		 daemon.h \
		 worker.h \
		 buddy.h \
		 text.h \
		 setting.h \
		 intercept.h
jidgin_LDADD = $(PURPLE_LIBS) ${GLIB2_LIBS} ${GTHREAD2_LIBS}
EXTRA_DIST = 	 jidgin.h \
                 reactor.h \
		 log.h \
		 robotmsg.h \
		 daemon.h \
		 worker.h \
		 buddy.h \
		 text.h \
		 setting.h \
		 intercept.h
