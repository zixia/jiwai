/* $Id: reactor.c 8 2008-11-08 16:13:44Z whw $ */

#include "reactor.h"
#include "log.h"

#include <glib.h>
#include <unistd.h>
#include <assert.h>
#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <errno.h>

static GSList *jidgin_callback_list = NULL;

static char *jidgin_queue_path      = NULL;
static int jidgin_inotify_fd        = 0;
static int jidgin_inotify_wd        = 0;

pJidginMsg jidgin_msg_init(unsigned short type) {
  pJidginMsg pmsg = (pJidginMsg)malloc(sizeof(jidginMsg));
  assert(NULL != pmsg);
  pmsg->from    = NULL;
  pmsg->to      = NULL;
  pmsg->msg     = NULL;
  pmsg->device  = NULL;
  pmsg->type    = type;
  return pmsg;
}

void jidgin_msg_destroy(pJidginMsg pmsg) {
  assert(NULL != pmsg);
  if (pmsg->from)   g_free(pmsg->from);
  if (pmsg->to)     g_free(pmsg->to);
  if (pmsg->msg)    g_free(pmsg->msg);
  if (pmsg->device) g_free(pmsg->device);
  free(pmsg);
}

void jidgin_reactor_run(int pipe_fd) {
  int sofar = 0;
  int length;
  char buf[INOTIFY_BUFFER_LEN];
  struct inotify_event *event;
  assert( NULL != jidgin_queue_path );

  if (-1 == (jidgin_inotify_fd = inotify_init())) {
    jidgin_log(LOG_ERR, "[jidgin_reacotr_run][inotify_init]%s\n", strerror(errno));
    abort();
  }

  if (-1 == (jidgin_inotify_wd = inotify_add_watch(jidgin_inotify_fd, jidgin_queue_path, INOTIFY_EVENT_LISTEN))) {
    jidgin_log(LOG_ERR, "[jidgin_reactor_run][inotify_add_watch]%s\n", strerror(errno));
    abort();
  }
  for (;;) {
    if (-1 == (length = read(jidgin_inotify_fd, buf, INOTIFY_BUFFER_LEN))) {
      jidgin_log(LOG_ERR, "[jidgin_reactor_run][read]%s\n", strerror(errno));
      abort();
    }
    sofar = 0;
    while (sofar < length) {
      event = (struct inotify_event *)&buf[sofar];
      if (event->len) {
        write(pipe_fd, event->name, event->len * sizeof(char));
      }
      sofar += (INOTIFY_EVENT_SIZE + event->len);
    }
  }
}

static void jidgin_reactor_stop() {
  if (jidgin_inotify_fd && jidgin_inotify_wd) {
    inotify_rm_watch(jidgin_inotify_fd, jidgin_inotify_wd);
  }
}

void jidgin_reactor_init(const char *path) {
  jidgin_queue_path = g_strdup(path);
  jidgin_callback_list = g_slist_alloc();
}

void jidgin_reactor_attach( jidgin_callback onData ) {
  assert(NULL != jidgin_callback_list);
  jidgin_callback_list = g_slist_append( jidgin_callback_list, (gpointer)onData );
}

void jidgin_reactor_detach( jidgin_callback onData ) {
  if (NULL == jidgin_callback_list) return;
  jidgin_callback_list = g_slist_remove(jidgin_callback_list, onData);
}

void jidgin_reactor_destroy() {
  if (jidgin_callback_list)
    g_slist_free(jidgin_callback_list);

  if (jidgin_queue_path)
    g_free(jidgin_queue_path);

  jidgin_reactor_stop();
}

static void jidgin_reactor_calleach(gpointer func, gpointer pmsg) {
  assert(NULL != pmsg);
  if (func)
    (*(jidgin_callback)func)((pJidginMsg)pmsg);
}

void jidgin_reactor_emit( pJidginMsg data ) {
  if (jidgin_callback_list)
    g_slist_foreach(jidgin_callback_list, jidgin_reactor_calleach, data);
}

