/* $Id: intercept.c 7 2008-11-08 14:58:17Z whw $ */

#include <assert.h>
#include <stdio.h>
#include "intercept.h"

gboolean jidgin_intercept_exec(const char *msg, char **reply) {
  if (g_str_has_prefix(msg, INTERCEPT_CMD_HELP)) {
    *reply = INTERCEPT_CMD_HELP_REPLY;
    return TRUE;
  }
  if (g_str_has_prefix(msg, INTERCEPT_CMD_TIPS)) {
    *reply = INTERCEPT_CMD_TIPS_REPLY;
    return TRUE;
  }

  return FALSE;
}

