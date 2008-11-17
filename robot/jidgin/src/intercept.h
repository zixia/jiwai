/* $Id: intercept.h 7 2008-11-08 14:58:17Z whw $ */

#ifndef HAVA_INTERCEPT_H
#define HAVA_INTERCEPT_H

#include <glib.h>

#ifdef __cplusplus
extern "C" {
#endif

#define INTERCEPT_CMD_HELP "help"
#define INTERCEPT_CMD_HELP_REPLY "改名:发送gm+空格+新名字,例如\"gm girl\"；发悄悄话给别人:发送D+空格+别人的名字+空格+悄悄话；关闭接收消息:发送guan"

#define INTERCEPT_CMD_TIPS "tips"
#define INTERCEPT_CMD_TIPS_REPLY "命令：ON、OFF、WHOIS帐号、NN帐号、FOLLOW帐号、LEAVE帐号、ADD帐号。"

gboolean jidgin_intercept_exec(const char *, char **reply);

#endif  // HAVA_INTERCEPT_H

