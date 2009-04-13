#                                               -*- Autoconf -*-
# Process this file with autoconf to produce a configure script.

AC_PREREQ(2.59)
AC_INIT(jidgin, 0.1, glinus@jiwai.com)
AC_CONFIG_SRCDIR([src/jidgin.c])
AC_CONFIG_HEADER([config.h])

AM_INIT_AUTOMAKE

# Checks for programs.
AC_PROG_CC

# Checks for libraries.

# Checks for header files.
AC_HEADER_STDC
AC_CHECK_HEADERS([string.h unistd.h syslog.h errno.h assert.h])
AC_CHECK_HEADERS([stdlib.h stdarg.h stdbool.h time.h])
AC_CHECK_HEADERS([fcntl.h signal.h sys/resource.h sys/inotify.h])

# Checks for typedefs, structures, and compiler characteristics.
AC_C_CONST

# pkg-config
PKG_CHECK_MODULES(PURPLE, purple)
PKG_CHECK_MODULES(GLIB2, glib-2.0)
PKG_CHECK_MODULES(GTHREAD2, gthread-2.0)

## switches and options
AC_ARG_ENABLE(debug,
  [AS_HELP_STRING([--enable-debug],[turn debug informations on])])
if test "x$enable_debug" == "xyes"
then
    org_cflags=$CFLAGS
    CFLAGS="-ggdb $org_cflags"
    AC_DEFINE([HAVE_DEBUG], [1], [turn debug informations on])
fi

CFLAGS="-Wall ${CFLAGS}"

# Checks for library functions.
AC_TYPE_SIGNAL
AC_CHECK_FUNCS([getopt])
AC_CHECK_FUNCS([syslog])
AC_CHECK_FUNCS([malloc])
AC_CHECK_FUNCS([fork])
AC_CHECK_FUNCS([getrlimit])
AC_CHECK_FUNCS([inotify_init])
AC_CONFIG_SUBDIRS([src/protocol/fetion src/protocol/facebook src/libevent])
AC_OUTPUT([Makefile src/Makefile])
