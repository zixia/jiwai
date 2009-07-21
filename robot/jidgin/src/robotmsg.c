/* $Id: robotmsg.c 12 2008-11-09 13:46:49Z whw $ */

#include "robotmsg.h"
#include "log.h"
#include "setting.h"
#include "jidgin.h"

#include <glib.h>
#include <stdlib.h>
#include <string.h>
#include <stdio.h>
#include <errno.h>
#include <assert.h>
#include <unistd.h>

static gchar *jidgin_robotmsg_path(pRobotMsg rmsg, ROBOTMSG_DIRECTION orient) {
  gchar *realname;
  gchar *realpath;
  gchar *robotmsg_path;
  gchar sec[G_ASCII_DTOSTR_BUF_SIZE + 1];
  gchar usec[G_ASCII_DTOSTR_BUF_SIZE + 1];
  pJidginSetting settings = jidgin_core_get_purple_settings();
  assert(NULL != rmsg);

  switch (orient) {
    case DIRECTION_MO:
      robotmsg_path = settings->mo_path;
      break;
    case DIRECTION_MT:
      robotmsg_path = settings->mt_path;
      break;
    default:
      jidgin_log(LOG_ERR, "[jidgin_robotmsg_path]%s\n", rmsg->from);
      return NULL;
  }

  g_ascii_dtostr(sec, G_ASCII_DTOSTR_BUF_SIZE, rmsg->sec);
  sec[G_ASCII_DTOSTR_BUF_SIZE] = '\0';

  g_ascii_dtostr(usec, G_ASCII_DTOSTR_BUF_SIZE, rmsg->usec);
  usec[G_ASCII_DTOSTR_BUF_SIZE] = '\0';

  realname = g_strjoin(
      ROBOTMSG_SEPARATOR,
      rmsg->device,
      "",
      rmsg->from,
      "",
      sec,
      usec,
      NULL
      );
  realpath = g_strjoin(G_DIR_SEPARATOR_S, robotmsg_path, realname, NULL);
  g_free(realname);
  return realpath;
}

#define PURPLE_PROTOCOL_PREFIX "prpl-"

char *jidgin_robotmsg_escape_protocol_id(char *id)
{
  int offset;
  offset = (g_str_has_prefix(id, PURPLE_PROTOCOL_PREFIX))
    ? strlen(PURPLE_PROTOCOL_PREFIX)
    : 0;
  return id + offset;
}

pRobotMsg jidgin_robotmsg_init(const char *device, const char *from, ROBOTMSG_DIRECTION orient) {
  pRobotMsg rmsg = (pRobotMsg)malloc(sizeof(robotMsg));
  struct timeval time;
  assert(NULL != rmsg);

  rmsg->device = g_strdup( jidgin_robotmsg_escape_protocol_id((char *)device) );
  rmsg->headers = g_hash_table_new_full(g_str_hash, g_str_equal, g_free, g_free);
  rmsg->from = g_strdup(from);
  rmsg->content = NULL;

  if (gettimeofday(&time, NULL)) {
    jidgin_log(LOG_ERR, "gettimeofday failed: %s\n", strerror(errno));
    abort();
  }
  rmsg->sec = time.tv_sec;
  rmsg->usec = time.tv_usec;

  rmsg->path = jidgin_robotmsg_path(rmsg, orient);
  rmsg->stream = fopen(rmsg->path, "w");

  if (NULL == rmsg->stream)
    jidgin_log(LOG_WARNING, "%s[%d]: %s\n", __FILE__, __LINE__, strerror(errno));

  return rmsg;
}

pRobotMsg jidgin_robotmsg_init_with_path(const char *path, ROBOTMSG_DIRECTION orient) {
  // path passed in shall be relative
  pRobotMsg rmsg = (pRobotMsg)calloc(1, sizeof(robotMsg));
  pJidginSetting settings = jidgin_core_get_purple_settings();
  gchar *robotmsg_path;
  assert(NULL != rmsg);

  switch (orient) {
    case DIRECTION_MO:
      robotmsg_path = settings->mo_path;
      break;
    case DIRECTION_MT:
      robotmsg_path = settings->mt_path;
      break;
    default:
      jidgin_log(LOG_ERR, "[jidgin_robotmsg_init_with_path]%s\n", path);
      return NULL;
  }

  if (g_str_has_prefix(path, robotmsg_path)) {
    rmsg->path = g_strdup(robotmsg_path);
    path += strlen(robotmsg_path);
  } else {
    rmsg->path = g_strjoin(G_DIR_SEPARATOR_S, robotmsg_path, path, NULL);
  }
  gchar **list = g_strsplit_set(path, ROBOTMSG_SEPARATOR, ROBOTMSG_FRAGMENTS);

  rmsg->device = g_strdup(list[0]);
  rmsg->stream = fopen(rmsg->path, "r");
  rmsg->sec = atol(list[4]);
  rmsg->usec = atol(list[5]);

  if (NULL == rmsg->stream) {
    jidgin_log(LOG_WARNING, "%s[%d]: %s\n", __FILE__, __LINE__, strerror(errno));
    jidgin_robotmsg_destroy(rmsg);
    return NULL;
  }

  rmsg->from = g_strdup(list[2]);

  g_strfreev(list);

  rmsg->headers = g_hash_table_new_full(g_str_hash, g_str_equal, g_free, g_free);
  rmsg->content = NULL;

  return rmsg;
}

void jidgin_robotmsg_addheader(pRobotMsg rmsg, char *key, char *value) {
  assert(NULL != rmsg && NULL != rmsg->headers);
  gchar *gkey = g_ascii_strup(key, strlen(key));
  gchar *gvalue = g_strdup(value);
  g_hash_table_insert(rmsg->headers, gkey, gvalue);
}

void jidgin_robotmsg_addcontent(pRobotMsg rmsg, char *content) {
  assert(NULL != rmsg);
  if (rmsg->content) {
    jidgin_log(LOG_WARNING, "%s[%d]: %s\n", __FILE__, __LINE__, "DIRTY content");
    g_free(rmsg->content);
  }

  rmsg->content = g_strdup(content);
}

static void jidgin_robotmsg_writehead(gpointer key, gpointer value, gpointer user_data) {
  pRobotMsg rmsg = (pRobotMsg)user_data;
  fprintf(rmsg->stream, "%s: %s\n", (char *)key, (char *)value);
}

void jidgin_robotmsg_write(pRobotMsg rmsg) {
  assert(NULL != rmsg && NULL != rmsg->stream);
  if (rmsg->stream) {
    fclose(rmsg->stream);
  }

  jidgin_log(LOG_INFO, "[jidgin_robotmsg_write]%s\n", rmsg->path);
  rmsg->stream = fopen(rmsg->path, "w");
  assert(NULL != rmsg->stream);
  fseek(rmsg->stream, 0L, 0);

  g_hash_table_foreach(rmsg->headers, jidgin_robotmsg_writehead, rmsg);
  fprintf(rmsg->stream, "\n%s", rmsg->content);
}

static char *jidgin_robotmsg_readline(FILE *fp, char *line) {
  return feof(fp)
    ? NULL
    : fgets(line, ROBOTMSG_HEADER_MAX, fp);
}

static int jidgin_robotmsg_readrest(FILE *fp, char *content) {
  return feof(fp)
    ? -1
    : fread(content, sizeof(char), ROBOTMSG_CONTENT_MAX, fp);
}

pRobotMsg jidgin_robotmsg_parse(pRobotMsg rmsg) {
  char *line    = (char *)calloc(ROBOTMSG_HEADER_MAX  + 1, sizeof(char));
  char *key     = (char *)calloc(ROBOTMSG_HEADER_MAX  + 1, sizeof(char));
  char *value   = (char *)calloc(ROBOTMSG_HEADER_MAX  + 1, sizeof(char));
  char *content = (char *)calloc(ROBOTMSG_CONTENT_MAX + 1, sizeof(char));

  assert(NULL != rmsg);
  fseek(rmsg->stream, 0L, 0);

  while (jidgin_robotmsg_readline(rmsg->stream, line)) {
    line[ROBOTMSG_HEADER_MAX] = '\0';
    if (line[0] == '\n') break;
    if (2 == sscanf(line, "%[^: ]: %[^\n]\n", key, value)) {
      key[ROBOTMSG_HEADER_MAX] = '\0';
      value[ROBOTMSG_HEADER_MAX] = '\0';
      jidgin_robotmsg_addheader(rmsg, key, value);
    }
  }

  jidgin_robotmsg_readrest(rmsg->stream, content);
  content[ROBOTMSG_CONTENT_MAX] = '\0';
  jidgin_robotmsg_addcontent(rmsg, content);

  free(content);
  free(value);
  free(key);
  free(line);
  return rmsg;
}

void jidgin_robotmsg_destroy(pRobotMsg rmsg) {
  assert(NULL != rmsg);

  jidgin_log(LOG_DEBUG, "[jidgin_robotmsg_destroy]%s\n", rmsg->from);
  if (rmsg->device) g_free(rmsg->device);
  if (rmsg->from) g_free(rmsg->from);
  if (rmsg->stream) fclose(rmsg->stream);
  if (rmsg->headers) g_hash_table_destroy(rmsg->headers);
  if (rmsg->content) g_free(rmsg->content);

  if (rmsg->path) {
    jidgin_log(LOG_DEBUG, "[jidgin_robotmsg_destroy][unlink]%s\n", rmsg->path);
    g_free(rmsg->path);
    remove(rmsg->path);
  }

  free(rmsg);
}

