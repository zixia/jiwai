/*
 * jidgin
 * $Id: jidgin.c 11 2008-11-08 17:17:21Z whw $
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

static void purple_glib_io_destroy(gpointer data)
{
  g_free(data);
}

static gboolean purple_glib_io_invoke(GIOChannel *source, GIOCondition condition, gpointer data)
{
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

static guint glib_input_add(gint fd, PurpleInputCondition condition, PurpleInputFunction function,
    gpointer data)
{
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

  static void
connect_to_signals(void)
{
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
  purple_signal_connect(blist_handle, "buddy-added",
      buddy_handle, PURPLE_CALLBACK(jidgin_buddy_cb_update),
      NULL);
  /*
     purple_signal_connect(blist_handle, "buddy-removed",
     buddy_handle, PURPLE_CALLBACK(jidgin_buddy_cb_update),
     NULL);
     */

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
  /*
     purple_signal_connect(conv_handle, "receiving-chat-msg",
     worker_handle, PURPLE_CALLBACK(jidgin_worker_cb_recv),
     NULL);
     purple_signal_connect(conv_handle, "sent-im-msg",
     worker_handle, PURPLE_CALLBACK(jidgin_worker_cb_sent),
     NULL);
     purple_signal_connect(conv_handle, "sent-chat-msg",
     worker_handle, PURPLE_CALLBACK(jidgin_worker_cb_sent),
     NULL);
     */
}

/*** Conversation uiops ***/
static PurpleConversationUiOps jidgin_conv_uiops = 
{
  NULL,                      /* create_conversation  */
  NULL,                      /* destroy_conversation */
  NULL,                      /* write_chat           */
  NULL,                      /* write_im             */
  NULL,                      /* write_conv           */
  NULL,                      /* chat_add_users       */
  NULL,                      /* chat_rename_user     */
  NULL,                      /* chat_remove_users    */
  NULL,                      /* chat_update_user     */
  NULL,                      /* present              */
  NULL,                      /* has_focus            */
  NULL,                      /* custom_smiley_add    */
  NULL,                      /* custom_smiley_write  */
  NULL,                      /* custom_smiley_close  */
  NULL,                      /* send_confirm         */
  NULL,
  NULL,
  NULL,
  NULL
};

  static void
jidgin_ui_init(void)
{
  /**
   * This should initialize the UI components for all the modules. Here we
   * just initialize the UI for conversations.
   */
  purple_conversations_set_ui_ops(&jidgin_conv_uiops);
}

static PurpleCoreUiOps jidgin_core_uiops = 
{
  NULL,
  NULL,
  jidgin_ui_init,
  NULL,

  /* padding */
  NULL,
  NULL,
  NULL,
  NULL
};

jidginSetting purple_settings;

pJidginSetting jidgin_core_get_purple_settings() {
  return &purple_settings;
}

  static void
init_settings(void)
{
  purple_settings.custom_user_directory = CUSTOM_USER_DIRECTORY;
  purple_settings.custom_plugin_path = CUSTOM_PLUGIN_PATH;
  purple_settings.plugin_save_pref = PLUGIN_SAVE_PREF;
  purple_settings.ui_id = UI_ID;
  purple_settings.is_daemon = FALSE;
  purple_settings.is_debug  = FALSE;
  purple_settings.chroot_dir = "/";
  purple_settings.queue_path = QUEUE_PATH;
}

  static void
init_libpurple(void)
{
  /* Set a custom user directory (optional) */
  purple_util_set_user_dir(purple_settings.custom_user_directory);

#if HAVE_DEBUG
  purple_debug_set_enabled(TRUE);
#else
  purple_debug_set_enabled(FALSE);
#endif

  /* Set the core-uiops, which is used to
   * 	- initialize the ui specific preferences.
   * 	- initialize the debug ui.
   * 	- initialize the ui components for all the modules.
   * 	- uninitialize the ui components for all the modules when the core terminates.
   */
  purple_core_set_ui_ops(&jidgin_core_uiops);

  /* Set the uiops for the eventloop. If your client is glib-based, you can safely
   * copy this verbatim. */
  purple_eventloop_set_ui_ops(&glib_eventloops);

  /* Set path to search for plugins. The core (libpurple) takes care of loading the
   * core-plugins, which includes the protocol-plugins. So it is not essential to add
   * any path here, but it might be desired, especially for ui-specific plugins. */
  purple_plugins_add_search_path(purple_settings.custom_plugin_path);

  /* Now that all the essential stuff has been set, let's try to init the core. It's
   * necessary to provide a non-NULL name for the current ui to the core. This name
   * is used by stuff that depends on this ui, for example the ui-specific plugins. */
  if (!purple_core_init(purple_settings.ui_id)) {
    /* Initializing the core failed. Terminate. */
    jidgin_log(LOG_ERR, "%s",
        "libpurple initialization failed. Dumping core.\n"
        "Please report this!\n");
    abort();
  }

  /* Create and load the buddylist. */
  purple_set_blist(purple_blist_new());
  purple_blist_load();

  /* Load the preferences. */
  purple_prefs_load();

  /* Load the desired plugins. The client should save the list of loaded plugins in
   * the preferences using purple_plugins_save_loaded(PLUGIN_SAVE_PREF) */
  purple_plugins_load_saved(purple_settings.plugin_save_pref);

  /* Load the pounces. */
  purple_pounces_load();
}

static PurpleAccount *account = NULL;

PurpleAccount *jidgin_core_get_primary_account() {
  return account;
}

int main(int argc, char *argv[])
{
  GList *iter;
  int argv0size = strlen(argv[0]);
  gchar *process_name;
  int i, c;
  const char *prpl;
  GMainLoop *loop = g_main_loop_new(NULL, FALSE);
  GHashTable *protocol_table = g_hash_table_new_full(g_str_hash, g_str_equal, g_free, NULL);
  //PurpleAccount *account;
  PurpleSavedStatus *status;
  GKeyFile *config = NULL;
  pJidginAccount primary;

  pid_t pid;
  int wait_status;
  int inotify_pipe_fd[2];

  /* libpurple's built-in DNS resolution forks processes to perform
   * blocking lookups without blocking the main process.  It does not
   * handle SIGCHLD itself, so if the UI does not you quickly get an army
   * of zombie subprocesses marching around.
   */
  signal(SIGCHLD, SIG_IGN);

  init_settings();

  while ( -1 != (c = getopt(argc, argv, "c:")) ) {
    switch (c) {
      case 'c': // config file
        config = jidgin_setting_init(optarg);
        jidgin_setting_get_main(config, &purple_settings);
        break;
      default:
        jidgin_log(LOG_ERR, "Illegal argument \"%c\"\n", c);
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
  jidgin_reactor_attach( jidgin_worker_on_data );
  connect_to_signals();

  g_main_loop_run(loop);

  jidgin_reactor_destroy();
  jidgin_setting_account_destroy(primary);
  return 0;
}

