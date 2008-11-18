/* $Id: setting.c 8 2008-11-08 16:13:44Z whw $ */

#include <assert.h>
#include <stdlib.h>

#include "setting.h"
#include "log.h"

GKeyFile *jidgin_setting_init(const char *ini) {
  GKeyFile *config = g_key_file_new();
  GError *error = NULL;
  if (g_key_file_load_from_file(config, ini, G_KEY_FILE_NONE, &error)) {
    return config;
  }

  jidgin_log(LOG_ERR, "jidgin_setting_init read config: %s\n", error->message);
  return NULL;
}

void jidgin_setting_destroy(pJidginSetting s) {
  if (s) {
    if (s->custom_user_directory) free(s->custom_user_directory);
    if (s->custom_plugin_path) free(s->custom_plugin_path);
    if (s->plugin_save_pref) free(s->plugin_save_pref);
    if (s->ui_id) free(s->ui_id);
    if (s->queue_path) free(s->queue_path);
    if (s->mo_path) free(s->mo_path);
    if (s->mt_path) free(s->mt_path);
    if (s->chroot_dir) free(s->chroot_dir);
    free(s);
  }
}

pJidginAccount jidgin_setting_get_primary_account(GKeyFile *config) {
  pJidginAccount account = NULL;
  GError *error;
  assert(config);
  if (g_key_file_has_group(config, SECTION_PRIMARY)) {
    account = (pJidginAccount)malloc(sizeof(jidginAccount));
    account->username = g_key_file_get_string(config, SECTION_PRIMARY, "username", &error);
    account->password = g_key_file_get_string(config, SECTION_PRIMARY, "password", &error);
    account->protocol = g_key_file_get_string(config, SECTION_PRIMARY, "protocol", &error);
    account->nickname = g_key_file_get_string(config, SECTION_PRIMARY, "nickname", &error);
  }

  return account;
}

void jidgin_setting_account_destroy(pJidginAccount account) {
  if (account) {
    if (account->username) g_free(account->username);
    if (account->password) g_free(account->password);
    if (account->protocol) g_free(account->protocol);
    if (account->nickname) g_free(account->nickname);
  }
}

GSList *jidgin_setting_get_accounts(GKeyFile *config) {
  GSList *accounts = g_slist_alloc();
  gchar **groups;
  gchar **pivot;
  GError *error;
  assert(config);

  groups = g_key_file_get_groups(config, NULL);
  pivot = groups;

  for (; *pivot; pivot++) {
    if (g_str_equal(*pivot, SECTION_PURPLE)
        || g_str_equal(*pivot, SECTION_PRIMARY)) {
      continue;
    }

    /* found an account */
    pJidginAccount account = (pJidginAccount)malloc(sizeof(jidginAccount));
    account->username = g_key_file_get_string(config, *pivot, "username", &error);
    account->password = g_key_file_get_string(config, *pivot, "password", &error);
    account->protocol = g_key_file_get_string(config, *pivot, "protocol", &error);
    account->protocol = g_key_file_get_string(config, *pivot, "protocol", &error);
    accounts = g_slist_append(accounts, account);
  }

  g_strfreev(groups);
  return accounts;
}

void jidgin_setting_get_main(GKeyFile *config, pJidginSetting setting) {
  GError *error;
  assert(config && setting);

  if (!g_key_file_has_group(config, SECTION_PURPLE)) {
    jidgin_log(LOG_ERR, "jidgin_setting_set_main: no section of %s\n", SECTION_PURPLE);
    return;
  }

  if (g_key_file_has_key(config, SECTION_PURPLE, "custom_user_directory", &error))
    setting->custom_user_directory = g_key_file_get_string(config, SECTION_PURPLE, "custom_user_directory", &error);
  if (g_key_file_has_key(config, SECTION_PURPLE, "custom_plugin_path", &error))
    setting->custom_plugin_path = g_key_file_get_string(config, SECTION_PURPLE, "custom_plugin_path", &error);
  if (g_key_file_has_key(config, SECTION_PURPLE, "plugin_save_pref", &error))
    setting->plugin_save_pref = g_key_file_get_string(config, SECTION_PURPLE, "plugin_save_pref", &error);
  if (g_key_file_has_key(config, SECTION_PURPLE, "ui_id", &error))
    setting->ui_id = g_key_file_get_string(config, SECTION_PURPLE, "ui_id", &error);
  if (g_key_file_has_key(config, SECTION_PURPLE, "queue_path", &error))
    setting->queue_path = g_key_file_get_string(config, SECTION_PURPLE, "queue_path", &error);
  if (g_key_file_has_key(config, SECTION_PURPLE, "debug", &error))
    setting->debug = g_key_file_get_integer(config, SECTION_PURPLE, "debug", &error);
  if (g_key_file_has_key(config, SECTION_PURPLE, "chroot_dir", &error))
    setting->chroot_dir = g_key_file_get_string(config, SECTION_PURPLE, "chroot_dir", &error);
  if (g_key_file_has_key(config, SECTION_PURPLE, "is_daemon", &error))
    setting->is_daemon = g_key_file_get_boolean(config, SECTION_PURPLE, "is_daemon", &error);
  if (g_key_file_has_key(config, SECTION_PURPLE, "is_debug", &error))
    setting->is_debug = g_key_file_get_boolean(config, SECTION_PURPLE, "is_debug", &error);
  if (g_key_file_has_key(config, SECTION_PURPLE, "is_force_mt", &error))
    setting->is_force_mt = g_key_file_get_boolean(config, SECTION_PURPLE, "is_force_mt", &error);
  if (g_key_file_has_key(config, SECTION_PURPLE, "nchars", &error))
    setting->nchars = g_key_file_get_integer(config, SECTION_PURPLE, "nchars", &error);

  if (setting->queue_path) {
    setting->mo_path = g_strjoin(G_DIR_SEPARATOR_S, setting->queue_path, "mo", NULL);
    setting->mt_path = g_strjoin(G_DIR_SEPARATOR_S, setting->queue_path, "mt", NULL);
  }

  return;
}

