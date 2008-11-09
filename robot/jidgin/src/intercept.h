/* $Id: intercept.h 7 2008-11-08 14:58:17Z whw $ */

#ifndef HAVA_INTERCEPT_H
#define HAVA_INTERCEPT_H

#include <glib.h>

#ifdef __cplusplus
extern "C" {
#endif

#define INTERCEPT_CMD_HELP "help"
#define INTERCEPT_CMD_HELP_REPLY "help command"

#define INTERCEPT_CMD_TIPS "tips"
#define INTERCEPT_CMD_TIPS_REPLY "tips command"

gboolean jidgin_intercept_exec(const char *, char **reply);

#endif  // HAVA_INTERCEPT_H

