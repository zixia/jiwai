/* $Id: worker.c 12 2008-11-09 13:46:49Z whw $ */

#include <assert.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>
#include <unistd.h>
#include <errno.h>

#include "reactor.h"
#include "log.h"
#include "worker.h"
#include "text.h"
#include "intercept.h"
#include "jidgin.h"

static char jidgin_worker_uptime[201];
static unsigned short retry_count = 5;

void *jidgin_worker_get_handle(void) {
  static int jidgin_worker_handle;
  return (void *)&jidgin_worker_handle;
}

static gboolean jidgin_worker_get_uptime_str() {
  time_t t;
  struct tm *tmp;

  t = time(NULL);
  tmp = localtime(&t);
  if (tmp == NULL) {
    jidgin_log(LOG_ERR, "[jidgin_worker_get_uptime_str]%s\n", strerror(errno));
    return FALSE;
  }

  if (strftime(jidgin_worker_uptime, sizeof(char) * 200, "%F %T", tmp) == 0) {
    jidgin_log(LOG_ERR, "[jidgin_worker_get_uptime_str]%s\n", strerror(errno));
    return FALSE;
  }

  return TRUE;
}

static void jidgin_worker_cb_retry(PurpleAccount *account) {
  if (retry_count) {
    sleep(10);
    purple_account_connect(account);
  } else {
    jidgin_worker_get_uptime_str();
    jidgin_log(LOG_CRIT, "[%s][jidgin down]%s\n",
        jidgin_worker_uptime,
        purple_account_get_username(account));
    abort();
  }

  --retry_count;
}

void jidgin_worker_cb_signon(PurpleConnection *gc, gpointer signal)
{
  PurpleAccount *account = purple_connection_get_account(gc);

  if (purple_account_is_disconnected(account)) {
    jidgin_log(LOG_ERR, "[purple_account_is_disconnected]%s %d\n",
        purple_account_get_username(account), retry_count);
    jidgin_worker_cb_retry(account);
  } else {
    jidgin_worker_get_uptime_str();
    jidgin_log(LOG_INFO, "[purple_account_is_connected]%s\n",
        purple_account_get_username(account));
  }
}

gboolean jidgin_worker_send_im(PurpleAccount *account, PurpleConversation *conv, pJidginMsg pmsg) {
  PurpleBuddy *buddy;
  char *reply;
  pJidginSetting setting = jidgin_core_get_purple_settings();

  if (!account) {
    account = jidgin_core_get_primary_account();
  }

  if (purple_account_is_disconnected(account)) {
    jidgin_log(LOG_INFO, "[jidgin_worker_send_im]%s disconnected\n",
        purple_account_get_username(account));
    jidgin_worker_cb_retry(account);
    return FALSE;
  }

  if (!conv) {
    buddy = purple_find_buddy(account, pmsg->from);
    if (!buddy) {
      if (setting && setting->is_force_mt)
        buddy = purple_buddy_new(account, pmsg->from, pmsg->from);
      else
        return FALSE;
    }
    conv = purple_conversation_new(PURPLE_CONV_TYPE_IM, buddy->account, pmsg->from);
  }

  jidgin_log(LOG_INFO, "[jidgin_worker_send_im]%s %s\n",
      purple_account_get_username(account), pmsg->from);

  if (jidgin_intercept_postrouting(pmsg->msg, &reply)) {
    g_free(pmsg->msg);
    pmsg->msg = reply;
  }
  purple_conv_im_send(PURPLE_CONV_IM(conv), pmsg->msg);

  return TRUE;
}

gboolean jidgin_worker_cb_recv(PurpleAccount *account,
    char **sender, char **message,
    PurpleConversation *conv, 
    PurpleMessageFlags *flags)
{
  pJidginMsg pmsg = jidgin_msg_init(ROBOTMSG_TYPE_CHAT);
  char *reply = NULL;
  char *msg_pre_filter = *message;
  char *msg_post_filter = jidgin_text_filter_rough(msg_pre_filter);
  *message = msg_post_filter;
  g_free(msg_pre_filter);

  pmsg->from    = g_strdup(*sender);
  pmsg->to      = g_strdup(account->username);
  pmsg->device  = g_strdup(account->protocol_id);

  if (jidgin_intercept_prerouting(*message, &reply)) {
    if (reply) {
      pmsg->msg = g_strdup(reply);
      jidgin_worker_send_im(account, conv, pmsg);
    }
    jidgin_msg_destroy(pmsg);
    return FALSE;
  }

  pmsg->msg     = g_strdup(*message);
  jidgin_reactor_emit(pmsg);
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
  return (IS_ACCOUNT_SIGN_ON(signal))
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
  pRobotMsg p = jidgin_robotmsg_init(data->device, data->from, DIRECTION_MO);

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
    jidgin_robotmsg_addheader(p, "msgtype", "SIG");
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
    rmsg = jidgin_robotmsg_init_with_path(inotify_buffer, DIRECTION_MT);
    if (!rmsg) continue;
    jidgin_robotmsg_parse(rmsg);

    pmsg = jidgin_msg_init(ROBOTMSG_TYPE_CHAT);
    pmsg->from    = rmsg->from;
    pmsg->to      = g_hash_table_lookup(rmsg->headers, "SERVERADDRESS");
    pmsg->msg     = g_strdup(rmsg->content);
    pmsg->device  = rmsg->device;

    jidgin_worker_send_im(NULL, NULL, pmsg);

    g_free(pmsg->msg);
    free(pmsg);
    jidgin_robotmsg_destroy(rmsg);
  }

  return GINT_TO_POINTER(inotify_read_fd);
}

char *jidgin_worker_get_uptime() {
  return jidgin_worker_uptime;
}

