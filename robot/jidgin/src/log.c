/* $Id: log.c 6 2008-11-05 19:21:40Z whw $ */

#include "log.h"
#include <stdio.h>
#include <stdarg.h>
#include <errno.h>
#include <string.h>

const char debug_stream[] = "/var/log/jidgin.log";

static FILE *jidgin_debug_stream = NULL;

static int jidgin_debug_priority = LOG_DEBUG;

static int jidgin_debug_device = DEVICE_CONSOLE;

void jidgin_log_stream(const char *path) {
  if (NULL != jidgin_debug_stream)
    fclose(jidgin_debug_stream);

  if (NULL == path)
    jidgin_debug_stream = fopen(debug_stream, "w+");
  else
    jidgin_debug_stream = fopen(path, "w+");

  if (NULL == jidgin_debug_stream)
    fprintf(stderr, "%s[%d]: %s\n", __FILE__, __LINE__, strerror(errno));
}

void jidgin_log_priority(int priority) {
  jidgin_debug_priority = priority;
}

void jidgin_log_device(int device) {
  jidgin_debug_device = device;
}

void jidgin_log(int priority, const char *format, ...) {
  va_list ap;
  va_start(ap, format);

  if (priority <= jidgin_debug_priority) { // higher the lower
    switch (jidgin_debug_device) {
      case DEVICE_CONSOLE:
        vfprintf(stderr, format, ap);
        break;
      case DEVICE_FILE:
        if (NULL == jidgin_debug_stream)
          jidgin_log_stream(NULL);
        if (NULL != jidgin_debug_stream) {
          vfprintf(jidgin_debug_stream, format, ap);
        } else {
          fprintf(stderr, "%s[%d]: %s\n", __FILE__, __LINE__, "log open failed");
        }
        break;
      case DEVICE_SYSLOG:
        vsyslog(priority, format, ap);
        break;
      default:
        // no suitable device
        break;
    }

    va_end(ap);
  }

  // ignore the log request
}

void jidgin_log_nil(const gchar *log_domain,
    GLogLevelFlags log_level,
    const gchar *message,
    gpointer user_data) {
}

