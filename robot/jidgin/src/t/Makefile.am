AUTOMAKE_OPTIONS = foreign
AM_CFLAGS = $(PURPLE_CFLAGS) ${GLIB2_CFLAGS}
bin_PROGRAMS = t_log t_daemon t_text
t_log_SOURCES = log.t.c ../log.c
t_log_LDADD = $(PURPLE_LIBS) ${GLIB2_LIBS}
t_daemon_SOURCES = daemon.t.c ../daemon.c
t_text_SOURCES = text.t.c ../text.c ../log.c
t_text_LDADD = ${GLIB2_LIBS}
