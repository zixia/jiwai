SUBDIRS = protocol/fetion protocol/facebook libevent
AUTOMAKE_OPTIONS = foreign
AM_CFLAGS = $(PURPLE_CFLAGS) ${GLIB2_CFLAGS} ${GTHREAD2_CFLAGS}
LIBEVENT_ROOT = libevent
INCLUDES = -I${LIBEVENT_ROOT}/
LDFLAGS+= -lrt
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
                 intercept.c \
                 server.c \
                 jidgin.h \
                 reactor.h \
                 log.h \
                 robotmsg.h \
                 daemon.h \
                 worker.h \
                 buddy.h \
                 text.h \
                 setting.h \
                 intercept.h \
                 server.h
jidgin_LDADD =   $(PURPLE_LIBS) \
                 ${GLIB2_LIBS} \
                 ${GTHREAD2_LIBS} \
                 ${LIBEVENT_ROOT}/.libs/libevent.a
EXTRA_DIST = 	 jidgin.h \
                 reactor.h \
                 log.h \
                 robotmsg.h \
                 daemon.h \
                 worker.h \
                 buddy.h \
                 text.h \
                 setting.h \
                 intercept.h \
                 server.h
