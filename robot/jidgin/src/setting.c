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
    if (g_str_equal(*pivot, SECTION_PURPLE)) continue;
    /* found an account */
    pJidginAccount account = (pJidginAccount)malloc(sizeof(jidginAccount));
    account->username = g_key_file_get_string(config, *pivot, "username", &error);
    account->password = g_key_file_get_string(config, *pivot, "password", &error);
    account->protocol = g_key_file_get_string(config, *pivot, "protocol", &error);
    account->nickname = g_key_file_get_string(config, *pivot, "nickname", &error);
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

  return;
}

