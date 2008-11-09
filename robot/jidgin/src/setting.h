/* $Id: setting.h 8 2008-11-08 16:13:44Z whw $ */

#ifndef HAVE_SETTING_H
#define HAVE_SETTING_H

#include <glib.h>

#ifdef __cplusplus
extern "C" {
#endif

#define SECTION_PURPLE "purple"
#define SECTION_PRIMARY "primary"

/* account info */
typedef struct {
  char *username;
  char *password;
  char *protocol;
  char *nickname;
} jidginAccount, *pJidginAccount;

/* libpurple setting */
typedef struct {
  char *custom_user_directory;
  char *custom_plugin_path;
  char *plugin_save_pref;
  char *ui_id;
  char *queue_path;
  unsigned short debug;
  char *chroot_dir;
  gboolean is_daemon;
  gboolean is_debug;
} jidginSetting, *pJidginSetting;

GKeyFile *jidgin_setting_init(const char *);

GSList *jidgin_setting_get_accounts(GKeyFile *);

pJidginAccount jidgin_setting_get_primary_account(GKeyFile *);

void jidgin_setting_account_destroy(pJidginAccount);

void jidgin_setting_get_main(GKeyFile *, pJidginSetting);

#endif  // HAVE_SETTING_H

