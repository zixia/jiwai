/* $Id: intercept.c 7 2008-11-08 14:58:17Z whw $ */

#include <assert.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <strings.h>

#include "setting.h"
#include "intercept.h"
#include "log.h"
#include "jidgin.h"

gboolean jidgin_intercept_prerouting(const char *msg, char **reply) {
  if (0 == strncasecmp(INTERCEPT_CMD_HELP, msg, strlen(INTERCEPT_CMD_HELP))) {
    *reply = INTERCEPT_CMD_HELP_REPLY;
    return TRUE;
  }
  if (0 == strncasecmp(INTERCEPT_CMD_TIPS, msg, strlen(INTERCEPT_CMD_TIPS))) {
    *reply = INTERCEPT_CMD_TIPS_REPLY;
    return TRUE;
  }

  return FALSE;
}

gboolean jidgin_intercept_postrouting(const char *msg, char **reply) {
  int buffer_len = strlen(msg) + 1;
  pJidginSetting setting = jidgin_core_get_purple_settings();

  if ( !g_utf8_validate(msg, -1, NULL) ) {
    jidgin_log(LOG_INFO, "[jidgin_intercept_postrouting]not utf8\n");
    return FALSE;
  }

  if ( g_utf8_strlen(msg, -1) < setting->nchars ) {
    return FALSE;
  }

  *reply = (char *)calloc(buffer_len, sizeof(char));
  *reply = g_utf8_strncpy(*reply, msg, setting->nchars);
  return TRUE;
}

