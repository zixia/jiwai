/* $Id$ */

#ifndef HAVA_SERVER_H
#define HAVA_SERVER_H

#include "evhttp.h"

typedef void (*jidgin_server_callback)(struct evhttp_request *, void *);

typedef struct {
  struct evhttp *server;
  char *address;
  int port;
  char *uri_bot;
  char *uri_status;
} jidginServer, *pJidginServer;

typedef enum {
  URI_STATUS = 0,
  URI_BOT    = 1,
} jidginServerUri;

pJidginServer jidgin_server_init(struct event_base *);
gpointer jidgin_server_run(gpointer);
void jidgin_server_destroy(pJidginServer);
void jidgin_server_set_uri(pJidginServer, jidginServerUri, const char *);

#endif  // HAVA_SERVER_H


