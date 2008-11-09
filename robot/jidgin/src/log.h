#ifndef HAVE_LOG_H
#define HAVE_LOG_H

#include <syslog.h>

#ifdef __cplusplus
extern "C" {
#endif

void jidgin_log_stream(const char *);

void jidgin_log_priority(int);

/* log device */
#define DEVICE_CONSOLE  1
#define DEVICE_FILE     2
#define DEVICE_SYSLOG   3

void jidgin_log_device(int);

void jidgin_log(int, const char *, ...);

#endif  // HAVE_LOG_H

