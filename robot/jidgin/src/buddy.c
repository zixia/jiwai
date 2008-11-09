/* $Id: buddy.c 12 2008-11-09 13:46:49Z whw $ */

#include <glib.h>
#include <purple.h>
#include <stdio.h>
#include <assert.h>
#include <unistd.h>

#include <sys/types.h>
#include <sys/stat.h>
#include <fcntl.h>
#include <sys/mman.h>

#include "buddy.h"
#include "log.h"
#include "jidgin.h"

void *jidgin_buddy_get_handle(void) {
  static int jidgin_buddy_handle;
  return (void *)&jidgin_buddy_handle;
}

void jidgin_buddy_cb_update(PurpleBuddy *buddy) {
  jidgin_log(LOG_DEBUG, "[jidgin_buddy_cb_update]%s\n", buddy->name);
  purple_account_add_buddy(jidgin_core_get_primary_account(), buddy);
}

void jidgin_buddy_traverse(gpointer key, gpointer value, gpointer user_data) {
  PurpleAccount *account = (PurpleAccount *)key;
  PurpleBuddy *buddy  = (PurpleBuddy *)value;
  jidgin_log(LOG_DEBUG, "[jidgin_buddy_traverse]%s => %s,%d\n",
      purple_account_get_username(account),
      buddy->name,
      PURPLE_BUDDY_IS_ONLINE(buddy));
}

GList *jidgin_buddy_lookup(const char *device, const char *address) {
  GList *serverAddress = NULL;
  PurpleBuddyList *blist = purple_get_blist();
  assert(NULL != blist);

  g_hash_table_foreach(blist->buddies, jidgin_buddy_traverse, NULL);
  return serverAddress;
}

