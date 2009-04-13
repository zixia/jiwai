/* $Id$ */

#include <assert.h>
#include <sys/queue.h>
#include <stdlib.h>
#include <glib.h>
#include "server.h"
#include "reactor.h"
#include "worker.h"
#include "log.h"

pJidginServer jidgin_server_init(struct event_base *base) {
  pJidginServer jserver = (pJidginServer)calloc(sizeof(jidginServer), 1);
  assert(jserver);
  jserver->server = evhttp_new(base);
  assert(jserver->server);
  return jserver;
}

gpointer jidgin_server_run(gpointer gserver) {
  pJidginServer jserver = (pJidginServer)gserver;
  assert(jserver && jserver->server);
  jidgin_log(LOG_INFO, "[jidgin_server_run]%s:%d\n", jserver->address, jserver->port);
  evhttp_bind_socket(jserver->server, jserver->address, jserver->port);
  event_dispatch();
  return 0;
}

void jidgin_server_destroy(pJidginServer jserver) {
  assert(jserver && jserver->server);
  evhttp_free(jserver->server);
  if (jserver->uri_bot) free(jserver->uri_bot);
  if (jserver->uri_status) free(jserver->uri_status);
  if (jserver->address) free(jserver->address);
  free(jserver);
}

void jidgin_server_on_bot(struct evhttp_request *req, void *data) {
  struct evkeyvalq q;
  struct evbuffer *buf = evbuffer_new();
  assert(buf);

  char *uri = evhttp_decode_uri(req->uri);
  jidgin_log(LOG_DEBUG, "[jidgin_server_on_bot]%s\n", uri);
  evhttp_parse_query(uri, &q);

  /* send im - reverse from and to */
  const char *from  = evhttp_find_header(&q, "to");
  const char *to    = evhttp_find_header(&q, "from");
  const char *msg   = evhttp_find_header(&q, "msg");
  const char *device= evhttp_find_header(&q, "device");

  pJidginMsg pmsg = jidgin_msg_init(ROBOTMSG_TYPE_CHAT);
  if (pmsg && from && to && msg && device) {
    pmsg->from    = g_strdup(from);
    pmsg->to      = g_strdup(to);
    pmsg->msg     = g_strdup(msg);
    pmsg->device  = g_ascii_strdown(device, -1);
    jidgin_log(LOG_INFO, "[jidign_server_on_bot]send_im: %s\n", to);
    jidgin_worker_send_im(NULL, NULL, pmsg);
    jidgin_msg_destroy(pmsg);
    evhttp_send_reply(req, HTTP_OK, "OK", buf);
  } else {
    evhttp_send_reply(req, HTTP_BADREQUEST, "Bad Request", buf);
    jidgin_log(LOG_WARNING, "[jidign_server_on_bot]malformed packet: %s\n", uri);
  }

  evbuffer_free(buf);
  free(uri);

  /* free evkyevalq */
  struct evkeyval* kv;
  TAILQ_FOREACH(kv, &q, next)
  {
    free(kv->key);
    free(kv->value);
    TAILQ_REMOVE(&q, kv, next);
  }
}

void jidgin_server_on_status(struct evhttp_request *req, void *data) {
}

void jidgin_server_set_uri(pJidginServer jserver, jidginServerUri type, const char * uri) {
  assert(jserver && uri);
  switch (type) {
    case URI_STATUS:
      jserver->uri_status = g_strdup(uri);
      evhttp_del_cb(jserver->server, uri);
      evhttp_set_cb(jserver->server, uri, jidgin_server_on_status, 0);
      break;
    case URI_BOT:
      jserver->uri_bot = g_strdup(uri);
      evhttp_del_cb(jserver->server, uri);
      evhttp_set_cb(jserver->server, uri, jidgin_server_on_bot, 0);
      break;
    default:
      break;
  }
}

