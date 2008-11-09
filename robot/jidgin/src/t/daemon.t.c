/* $Id: daemon.t.c 10 2008-11-08 16:24:02Z whw $ */

#include "../daemon.h"
#include <unistd.h>

int main() {
  daemonize(NULL);
  sleep(1024 * 1024);
  return 0;
}

