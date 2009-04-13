/*
 * jidgin
 * $Id$
 */

#include <sys/types.h>
#include <sys/wait.h>

#include <glib.h>
#include <stdio.h>
#include <signal.h>
#include <string.h>
#include <unistd.h>
#include <assert.h>
#include <time.h>
#include <errno.h>

#include "jidgin.h"
#include "log.h"
#include "reactor.h"
#include "daemon.h"
#include "buddy.h"
#include "worker.h"
#include "server.h"

/**
 * The following eventloop functions are used in both pidgin and purple-text. If your
 * application uses glib mainloop, you can safely use this verbatim.
 */
#define PURPLE_GLIB_READ_COND  (G_IO_IN | G_IO_HUP | G_IO_ERR)
#define PURPLE_GLIB_WRITE_COND (G_IO_OUT | G_IO_HUP | G_IO_ERR | G_IO_NVAL)

typedef struct _PurpleGLibIOClosure {
  PurpleInputFunction function;
  guint result;
  gpointer data;
} PurpleGLibIOClosure;

static void purple_glib_io_destroy(gpointer data) {
  g_free(data);
}

static gboolean purple_glib_io_invoke(GIOChannel *source,
    GIOCondition condition, gpointer data) {
  PurpleGLibIOClosure *closure = data;
  PurpleInputCondition purple_cond = 0;

  if (condition & PURPLE_GLIB_READ_COND)
    purple_cond |= PURPLE_INPUT_READ;
  if (condition & PURPLE_GLIB_WRITE_COND)
    purple_cond |= PURPLE_INPUT_WRITE;

  closure->function(closure->data, g_io_channel_unix_get_fd(source),
      purple_cond);

  return TRUE;
}

static guint glib_input_add(gint fd,
    PurpleInputCondition condition, PurpleInputFunction function, gpointer data) {
  PurpleGLibIOClosure *closure = g_new0(PurpleGLibIOClosure, 1);
  GIOChannel *channel;
  GIOCondition cond = 0;

  closure->function = function;
  closure->data = data;

  if (condition & PURPLE_INPUT_READ)
    cond |= PURPLE_GLIB_READ_COND;
  if (condition & PURPLE_INPUT_WRITE)
    cond |= PURPLE_GLIB_WRITE_COND;

  channel = g_io_channel_unix_new(fd);
  closure->result = g_io_add_watch_full(channel, G_PRIORITY_DEFAULT, cond,
      purple_glib_io_invoke, closure, purple_glib_io_destroy);

  g_io_channel_unref(channel);
  return closure->result;
}

static PurpleEventLoopUiOps glib_eventloops =
{
  g_timeout_add,
  g_source_remove,
  glib_input_add,
  g_source_remove,
  NULL,
#if GLIB_CHECK_VERSION(2,14,0)
  g_timeout_add_seconds,
#else
  NULL,
#endif

  /* padding */
  NULL,
  NULL,
  NULL
};
/*** End of the eventloop functions. ***/

static void connect_to_signals(void) {
  void *conn_handle = purple_connections_get_handle();
  void *conv_handle = purple_conversations_get_handle();
  void *blist_handle = purple_blist_get_handle();

  void *worker_handle = jidgin_worker_get_handle();
  void *buddy_handle = jidgin_buddy_get_handle();

  /* people up and people down */
  purple_signal_connect(blist_handle, "buddy-signed-on", 
      worker_handle, PURPLE_CALLBACK(jidgin_worker_cb_buddy),
      GINT_TO_POINTER(SIGN_ON));
  purple_signal_connect(blist_handle, "buddy-signed-off", 
      worker_handle, PURPLE_CALLBACK(jidgin_worker_cb_buddy),
      GINT_TO_POINTER(SIGN_OFF));
  purple_signal_connect(blist_handle, "buddy-status-changed",
      worker_handle, PURPLE_CALLBACK(jidgin_worker_cb_status),
      NULL);

  /* people love and people go */
  purple_signal_connect(conn_handle, "account-authorization-requested",
      buddy_handle, PURPLE_CALLBACK(jidgin_buddy_cb_request),
      NULL);

  /* service up and service down */
  purple_signal_connect(conn_handle, "signed-on",
      worker_handle, PURPLE_CALLBACK(jidgin_worker_cb_signon),
      GINT_TO_POINTER(SIGN_ON));
  purple_signal_connect(conn_handle, "signed-off",
      worker_handle, PURPLE_CALLBACK(jidgin_worker_cb_signon),
      GINT_TO_POINTER(SIGN_OFF));

  /* message come and message go */
  purple_signal_connect(conv_handle, "receiving-im-msg",
      worker_handle, PURPLE_CALLBACK(jidgin_worker_cb_recv),
      NULL);
}

jidginSetting purple_settings;

pJidginSetting jidgin_core_get_purple_settings() {
  return &purple_settings;
}

static void init_settings(void) {
  purple_settings.custom_user_directory = CUSTOM_USER_DIRECTORY;
  purple_settings.custom_plugin_path = CUSTOM_PLUGIN_PATH;
  purple_settings.plugin_save_pref = PLUGIN_SAVE_PREF;
  purple_settings.ui_id = UI_ID;
  purple_settings.is_daemon = FALSE;
  purple_settings.is_debug  = FALSE;
  purple_settings.chroot_dir = "/";
  purple_settings.queue_path = QUEUE_PATH;
  purple_settings.srv_addr = NULL;
  purple_settings.srv_port = 10080;
}

static void init_libpurple(void) {
  /* Set a custom user directory (optional) */
  purple_util_set_user_dir(purple_settings.custom_user_directory);

#if HAVE_DEBUG
  purple_debug_set_enabled(TRUE);
#else
  purple_debug_set_enabled(FALSE);
#endif

  purple_eventloop_set_ui_ops(&glib_eventloops);
  purple_plugins_add_search_path(purple_settings.custom_plugin_path);

  if (!purple_core_init(purple_settings.ui_id)) {
    /* Initializing the core failed. Terminate. */
    jidgin_log(LOG_ERR, "%s",
        "libpurple initialization failed. Dumping core.\n"
        "Please report this!\n");
    abort();
  }

  purple_set_blist(purple_blist_new());
  purple_blist_load();
  purple_prefs_load();
  purple_plugins_load_saved(purple_settings.plugin_save_pref);
}

static PurpleAccount *account = NULL;

PurpleAccount *jidgin_core_get_primary_account() {
  return account;
}

static void jidgin_core_print_version() {
  fprintf(stdout, "jidgin $Id$ [http://jiwai.de/]\n");
}

static void jidgin_core_print_help() {
  fprintf(stdout, "jidgin $Id$ [http://jiwai.de/]\n\
      OPTIONS: \n\
      -f: config file \n\
      -d: enable debug level log \n\
      -v: print version\n\
      -h: this help\n\
      EXAMPLES: \n\
      jidgin -v -f config.qq \n\
      jidgin -f config.qq -d \n");
}

int main(int argc, char *argv[]) {
  int argv0size = strlen(argv[0]);
  gchar *process_name;
  int i, c;
  const char *prpl;

  GList *iter;
  GMainLoop *loop = g_main_loop_new(NULL, FALSE);
  GHashTable *protocol_table = g_hash_table_new_full(g_str_hash, g_str_equal, g_free, NULL);
  GKeyFile *config = NULL;
  gboolean purple_debug_verbose = FALSE;

  PurpleSavedStatus *status;
  pJidginAccount primary;

  pid_t pid;
  int wait_status;
  int inotify_pipe_fd[2];

  pJidginServer httpd;

  signal(SIGCHLD, SIG_IGN);
  init_settings();

  while ( -1 != (c = getopt(argc, argv, "f:dvh")) ) {
    switch (c) {
      case 'f': // config file
        config = jidgin_setting_init(optarg);
        jidgin_setting_get_main(config, &purple_settings);
        break;
      case 'd':
        purple_debug_verbose = TRUE;
        break;
      case 'v':
        jidgin_core_print_version();
        return 1;
      default:
        jidgin_core_print_help();
        return 1;
    }
  }

  assert(config);
  primary = jidgin_setting_get_primary_account(config);
  assert(primary);

  /* fake the process name */
  process_name = g_strjoin("_", primary->protocol, primary->username, NULL);
  strncpy(argv[0], process_name, argv0size);
  g_free(process_name);

  if (purple_settings.is_debug) {
    jidgin_log_priority(LOG_DEBUG);
  } else {
    jidgin_log_priority(LOG_INFO);
  }

  if (purple_settings.is_daemon) {
    daemonize(purple_settings.chroot_dir);
    jidgin_log_device(DEVICE_SYSLOG);
  }

  init_libpurple();
  purple_debug_set_enabled(purple_debug_verbose);

  iter = purple_plugins_get_protocols();
  for (i = 0; iter; iter = iter->next) {
    PurplePlugin *plugin = iter->data;
    PurplePluginInfo *info = plugin->info;
    if (info && info->name) {
      g_hash_table_insert(protocol_table,
          g_ascii_strdown(info->name, strlen(info->name)),
          info->id
          );
    }
  }

  prpl = g_hash_table_lookup(protocol_table, primary->protocol);
  g_hash_table_destroy(protocol_table);

  /* Create the account */
  account = purple_account_new(primary->username, prpl);
  purple_account_set_password(account, primary->password);

  if (primary->nickname) {
    purple_account_set_alias(account, primary->nickname);
    purple_account_set_status(account, "available",
        TRUE, "message", primary->nickname, NULL);
  }

  /* It's necessary to enable the account first. */
  purple_account_set_enabled(account, purple_settings.ui_id, TRUE);

  /* Now, to connect the account(s), create a status and activate it. */
  status = purple_savedstatus_new(NULL, PURPLE_STATUS_AVAILABLE);
  purple_savedstatus_activate(status);

  /* reactor and worker */
  jidgin_reactor_init(purple_settings.mt_path);

  if (pipe(inotify_pipe_fd)) {
    jidgin_log(LOG_ERR, "[jidgin_core]pipe error: %s\n", strerror(errno));
    return -1;
  }

  /* fork the inotify process */
  pid = fork();
  if (pid < 0) {
    jidgin_log(LOG_ERR, "[jidgin_core]fork error: %s\n", strerror(errno));
    return -1;
  }
  if (pid == 0) {  // child
    strncpy(argv[0], "inotify", argv0size);
    close(inotify_pipe_fd[0]);
    jidgin_reactor_run(inotify_pipe_fd[1]);
    return 0;
  }

  // parent
  waitpid(-1, &wait_status, WNOHANG);
  close(inotify_pipe_fd[1]);
  if ( !g_thread_supported() ) g_thread_init(NULL);
  if ( !g_thread_create(jidgin_worker_spawn, GINT_TO_POINTER(inotify_pipe_fd[0]), FALSE, NULL) ) {
    jidgin_log(LOG_ERR, "[jidgin_core]thread error\n");
    abort();
  }

  /* evhttp */
  struct event_base *base=event_init();
  httpd = jidgin_server_init(base);
  httpd->address = g_strdup(purple_settings.srv_addr);
  httpd->port = purple_settings.srv_port;
  jidgin_server_set_uri(httpd, URI_STATUS, "/status/");
  jidgin_server_set_uri(httpd, URI_BOT, "/bot/");
  if (!g_thread_create(jidgin_server_run, (gpointer)httpd, FALSE, NULL)) {
    jidgin_log(LOG_ERR, "[jidgin_core]thread error\n");
    abort();
  }

  jidgin_reactor_attach( jidgin_worker_on_data );
  connect_to_signals();

  g_log_set_handler (NULL,
      G_LOG_LEVEL_WARNING | G_LOG_FLAG_FATAL | G_LOG_LEVEL_CRITICAL ,
      jidgin_log_nil, NULL);

  g_main_loop_run(loop);

  jidgin_server_destroy(httpd);
  jidgin_reactor_destroy();
  jidgin_setting_account_destroy(primary);
  return 0;
}

