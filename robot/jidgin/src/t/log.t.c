/* $Id: log.t.c 10 2008-11-08 16:24:02Z whw $ */

#include "../log.h"

#include <stdio.h>
#include <sys/types.h>
#include <unistd.h>

const char usage[] = "log";

int main(int argc, char **argv) {
  /**
  jidgin_log_device(DEVICE_CONSOLE);
  jidgin_log_priority(LOG_DEBUG);
  */

  if (1 == argc)
    fprintf(stderr, "%s %s\n", argv[0], usage);
  else 
    jidgin_log(LOG_WARNING, "%s[%d] %s\n", "t_log", getpid(), argv[1]);
  return 0;
}

