#ifndef HAVE_ROBOTMSG_H
#define HAVE_ROBOTMSG_H

#include <sys/time.h>
#include <glib.h>
#include <stdio.h>
#include <time.h>

#ifdef __cplusplus
extern "C" {
#endif

#define ROBOTMSG_QUEUE_PATH  "/tmp/"
#define ROBOTMSG_SEPARATOR "_"
#define ROBOTMSG_FRAGMENTS 6

/* Resitrictions */
#define ROBOTMSG_HEADER_MAX  1024
#define ROBOTMSG_CONTENT_MAX 1024 * 1024

typedef enum {
  DIRECTION_MO  = 0,
  DIRECTION_MT  = 1,
} ROBOTMSG_DIRECTION;

typedef enum {
  ROBOTMSG_TYPE_CHAT = 0,
  ROBOTMSG_TYPE_SIG  = 1,
} ROBOTMSG_TYPE;

typedef struct {
  char *device;
  char *path;
  char *from;
  FILE *stream;
  GHashTable *headers;
  char *content;
  ROBOTMSG_TYPE type;
  time_t sec;
  suseconds_t usec;
} robotMsg, *pRobotMsg;

char *jidgin_robotmsg_escape_protocol_id(char *id);

pRobotMsg jidgin_robotmsg_init(const char *, const char *);

pRobotMsg jidgin_robotmsg_init_with_path(const char *);

void jidgin_robotmsg_addheader(pRobotMsg, char *, char *);

void jidgin_robotmsg_addcontent(pRobotMsg, char *);

void jidgin_robotmsg_write(pRobotMsg);

pRobotMsg jidgin_robotmsg_parse(pRobotMsg);

void jidgin_robotmsg_destroy(pRobotMsg);

#endif  // HAVE_ROBOTMSG_H

