/* $Id: reactor.h 7 2008-11-08 14:58:17Z whw $ */

#ifndef HAVE_REACTOR_H
#define HAVE_REACTOR_H

#include <sys/inotify.h>
#include "robotmsg.h"

#define INOTIFY_EVENT_SIZE (sizeof(struct inotify_event))
#define INOTIFY_BUFFER_LEN ((INOTIFY_EVENT_SIZE + 16) * 1024)

#ifdef __cplusplus
extern "C" {
#endif

typedef struct  {
  char *from;
  char *to;
  char *msg;
  char *device;
  ROBOTMSG_TYPE type;
} jidginMsg, *pJidginMsg;

pJidginMsg jidgin_msg_init(unsigned short);

void jidgin_msg_destroy(pJidginMsg);

typedef void (*jidgin_callback)( pJidginMsg );

void jidgin_reactor_init(const char *);

void jidgin_reactor_attach( jidgin_callback );

void jidgin_reactor_detach( jidgin_callback );

void jidgin_reactor_destroy();

void jidgin_reactor_emit( pJidginMsg );

void jidgin_reactor_run(int);

#endif  // HAVE_REACTOR_H

