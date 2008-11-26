/* $Id: worker.h 7 2008-11-08 14:58:17Z whw $ */

#ifndef HAVA_WORKER_H
#define HAVA_WORKER_H

#include <purple.h>
#include "reactor.h"

#ifdef __cplusplus
extern "C" {
#endif

typedef enum {
  SIGN_ON   = 0,
  SIGN_OFF  = 1,
} WORKER_SIGNAL;

#define IS_ACCOUNT_SIGN_ON(e) ((e) == SIGN_ON)

void *jidgin_worker_get_handle(void);

void jidgin_worker_cb_signon(PurpleConnection *, gpointer);

gboolean jidgin_worker_send_im(PurpleAccount *, PurpleConversation *, pJidginMsg);

gboolean jidgin_worker_cb_recv(PurpleAccount *, char **, char **,
    PurpleConversation *, 
    PurpleMessageFlags *);

void jidgin_worker_cb_sent(PurpleAccount *, char *, char *);

void jidgin_worker_cb_buddy(PurpleBuddy *, gpointer);

void jidgin_worker_cb_status(PurpleBuddy *, PurpleStatus *, PurpleStatus *);

void jidgin_worker_on_data(pJidginMsg);

gpointer jidgin_worker_spawn(gpointer);

char *jidgin_worker_get_uptime();

#endif  // HAVA_WORKER_H

