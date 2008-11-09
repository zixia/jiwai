/* $Id: jidgin.h 8 2008-11-08 16:13:44Z whw $ */

#ifndef HAVE_JIDGIN_H
#define HAVE_JIDGIN_H

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include <sys/types.h>
#include <purple.h>
#include "setting.h"

#ifdef __cplusplus
extern "C" {
#endif

#define CUSTOM_USER_DIRECTORY  "/dev/null"
#define CUSTOM_PLUGIN_PATH     ""
#define PLUGIN_SAVE_PREF       "/purple/nullclient/plugins/saved"
#define UI_ID                  "nullclient"
#define QUEUE_PATH                "/var/cache/tmpfs/jiwai/queue/"

pJidginSetting jidgin_core_get_purple_settings();

PurpleAccount *jidgin_core_get_primary_account();

#endif  // HAVE_JIDGIN_H

