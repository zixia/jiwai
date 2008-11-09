/* $Id: worker.c 12 2008-11-09 13:46:49Z whw $ */

#include <assert.h>
#include <stdio.h>
#include <string.h>
#include <time.h>
#include <unistd.h>

#include "reactor.h"
#include "log.h"
#include "worker.h"
#include "text.h"
#include "intercept.h"
#include "jidgin.h"

void *jidgin_worker_get_handle(void) {
  static int jidgin_worker_handle;
  return (void *)&jidgin_worker_handle;
}

void jidgin_worker_cb_signon(PurpleConnection *gc, gpointer signal)
{
	PurpleAccount *account = purple_connection_get_account(gc);
  if (IS_BUDDY_SIGN_ON(signal)) {
    jidgin_log(LOG_INFO, "[jidgin_worker_cb_signon]%s %s\n", account->username, account->protocol_id);
  } else {
    jidgin_log(LOG_INFO, "[jidgin_worker_cb_signoff]%s %s\n", account->username, account->protocol_id);
  }
}

gboolean jidgin_worker_send_im(PurpleAccount *account, PurpleConversation *conv, pJidginMsg pmsg) {
  char *reply = NULL;
  PurpleBuddy *buddy;

  if (!account) {
    account = jidgin_core_get_primary_account();
  }

  if (!conv) {
    buddy = purple_find_buddy(account, pmsg->from);
    if (!buddy) return FALSE;
    conv = purple_conversation_new(PURPLE_CONV_TYPE_IM, buddy->account, pmsg->from);
  }

  if (jidgin_intercept_exec(pmsg->msg, &reply)) {
    purple_conv_im_send(PURPLE_CONV_IM(conv), reply);
    return FALSE;
  }

  jidgin_log(LOG_INFO, "[jidgin_worker_send_im]%s %s\n",
      purple_account_get_username(account), pmsg->from);
  purple_conv_im_send(PURPLE_CONV_IM(conv), pmsg->msg);

  return TRUE;
}

gboolean jidgin_worker_cb_recv(PurpleAccount *account,
    char **sender, char **message,
    PurpleConversation *conv, 
    PurpleMessageFlags *flags)
{
  pJidginMsg pmsg = jidgin_msg_init(ROBOTMSG_TYPE_CHAT);
  char *msg_pre_filter = *message;
  char *msg_post_filter = jidgin_text_filter_rough(msg_pre_filter);
  *message = msg_post_filter;
  g_free(msg_pre_filter);

  pmsg->from    = g_strdup(*sender);
  pmsg->to      = g_strdup(account->username);
  pmsg->msg     = g_strdup(*message);
  pmsg->device  = g_strdup(account->protocol_id);

  if (jidgin_worker_send_im(account, conv, pmsg)) {
    jidgin_reactor_emit(pmsg);
  }

  jidgin_msg_destroy(pmsg);
  return TRUE;
}

void jidgin_worker_cb_sent(PurpleAccount *account,
    char *receiver, char *message)
{
  jidgin_log(LOG_DEBUG, "[jidgin_worker_cb_sent]%s\n", receiver);
}

void jidgin_worker_cb_buddy(PurpleBuddy *buddy, gpointer signal)
{
  return (IS_BUDDY_SIGN_ON(signal))
  ? jidgin_log(LOG_DEBUG, "[jidgin_worker_cb_buddy]%s %d\n", buddy->name, signal)
  : jidgin_log(LOG_DEBUG, "[jidgin_worker_cb_buddy]%s %d\n", buddy->name, signal);
}

void jidgin_worker_cb_status(PurpleBuddy *buddy, PurpleStatus *old_status, PurpleStatus *status) {
  const char *sig_new = purple_status_get_attr_string(status, "message");
  pJidginMsg pmsg;
  PurpleAccount *account;

  if (sig_new) {
    account = purple_buddy_get_account(buddy);
    pmsg = jidgin_msg_init(ROBOTMSG_TYPE_SIG);
    pmsg->from    = g_strdup(buddy->name);
    pmsg->to      = g_strdup(account->username);
    pmsg->msg     = g_strdup(sig_new);
    pmsg->device  = g_strdup(account->protocol_id);

    jidgin_reactor_emit(pmsg);
    jidgin_msg_destroy(pmsg);
    jidgin_log(LOG_INFO, "[jidgin_worker_cb_status]%s %s\n",
        buddy->name,
        purple_status_get_attr_string(status, "message"));
  }
}

void jidgin_worker_on_data(pJidginMsg data) {
  assert(NULL != data);
  pRobotMsg p = jidgin_robotmsg_init(data->device, data->from);

  gchar *address = g_strjoin(
      NULL,
      jidgin_robotmsg_escape_protocol_id(data->device),
      "://",
      data->from,
      NULL
      );
  jidgin_robotmsg_addheader(p, "address", address);
  jidgin_robotmsg_addheader(p, "serverAddress", data->to);
  if (data->type == ROBOTMSG_TYPE_SIG) {
    jidgin_robotmsg_addheader(p, "type", "SIGNATURE");
  }
  jidgin_robotmsg_addcontent(p, data->msg);

  jidgin_robotmsg_write(p);
  jidgin_robotmsg_destroy(p);
  g_free(address);
}

gpointer jidgin_worker_spawn(gpointer fd) {
  int inotify_read_fd = GPOINTER_TO_INT(fd);
  char inotify_buffer[INOTIFY_BUFFER_LEN];
  pRobotMsg rmsg;
  pJidginMsg pmsg;
  while (-1 != read(inotify_read_fd, inotify_buffer, INOTIFY_BUFFER_LEN)) {
    jidgin_log(LOG_DEBUG, "[jidgin_worker_spawn]%s\n", inotify_buffer);
    rmsg = jidgin_robotmsg_init_with_path(inotify_buffer);
    jidgin_robotmsg_parse(rmsg);

    pmsg = jidgin_msg_init(ROBOTMSG_TYPE_CHAT);
    pmsg->from    = rmsg->from;
    pmsg->to      = g_hash_table_lookup(rmsg->headers, "SERVERADDRESS");
    pmsg->msg     = rmsg->content;
    pmsg->device  = rmsg->device;

    jidgin_worker_send_im(NULL, NULL, pmsg);
    free(pmsg);
    jidgin_robotmsg_destroy(rmsg);
  }

  return GINT_TO_POINTER(inotify_read_fd);
}

