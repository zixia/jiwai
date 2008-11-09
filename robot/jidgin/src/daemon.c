/* $Id: daemon.c 6 2008-11-05 19:21:40Z whw $ */

#include <fcntl.h>
#include <stdlib.h>
#include <unistd.h>
#include <signal.h>
#include <syslog.h>
#include <sys/resource.h>
#include <sys/types.h>
#include <sys/stat.h>

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#ifndef PACKAGE
#define PACKAGE "jidgin"
#endif

#include "daemon.h"

void daemonize(const char *dir) {
  int i, fd0, fd1, fd2;
  pid_t pid;
  struct sigaction sa;
  struct rlimit rl;
  const char *chroot;

  chroot = (dir) ? dir : "/";

  /*
   * Clear file creation mask.
   */
  umask(0);

  /*
   * Get maximum number of file descriptors.
   */
  if (getrlimit(RLIMIT_NOFILE, &rl) < 0)
    exit(-1);

  /*
   * Become a session leader to lose controlling TTY.
   */
  if ((pid = fork()) < 0)
    exit(-1);
  else if (pid != 0)  /* parent */
    exit(0);
  setsid();

  /*
   * Ensure future opens won't allocate controlling TTYs.
   */
  sa.sa_handler = SIG_IGN;
  sigemptyset(&sa.sa_mask);
  sa.sa_flags = 0;
  if (sigaction(SIGHUP, &sa, NULL) < 0)
    exit(-1);
  if ((pid = fork()) < 0)
    exit(-1);
  else if (pid != 0) /* parent */
    exit(0);

  /*
   * Change the current working directory to the root so
   * we won't prevent file systems from being unmounted.
   */
  if (chdir(chroot) < 0)
    exit(-1);

  /*
   * Close all open file descriptors.
   */
  if (rl.rlim_max == RLIM_INFINITY)
    rl.rlim_max = 1024;
  for (i = 0; i < rl.rlim_max; i++)
    close(i);

  /*
   * Attach file descriptors 0, 1, and 2 to /dev/null.
   */
  fd0 = open("/dev/null", O_RDWR);
  fd1 = dup(0);
  fd2 = dup(0);

  openlog(PACKAGE, LOG_CONS | LOG_PID, LOG_DAEMON);
}

