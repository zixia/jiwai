/* $Id: intercept.h 7 2008-11-08 14:58:17Z whw $ */

#ifndef HAVA_INTERCEPT_H
#define HAVA_INTERCEPT_H

#include <glib.h>

#ifdef __cplusplus
extern "C" {
#endif

#define JIDGIN_STR_EQUAL(x,y) (0 == strncasecmp((x), (y), strlen(x)))

#define INTERCEPT_CMD_HELP "help"
#define INTERCEPT_CMD_HELP_REPLY "改名:发送gm+空格+新名字,例如\"gm girl\"；发悄悄话给别人:发送D+空格+别人的名字+空格+悄悄话；关闭接收消息:发送guan"

#define INTERCEPT_CMD_TIPS "tips"
#define INTERCEPT_CMD_TIPS_REPLY "命令：ON、OFF、WHOIS帐号、NN帐号、FOLLOW帐号、LEAVE帐号、ADD帐号。"

#define INTERCEPT_CMD_UPTIME "uptime"

#define INTERCEPT_NONSENSE_QQ_IDLE "您好，我现在有事不在，一会儿再和您联系"
#define INTERCEPT_NONSENSE_QQ_IDLE2 "您好，我现在很忙，稍后再和您联系"

gboolean jidgin_intercept_prerouting(const char *, char **reply);

gboolean jidgin_intercept_postrouting(const char *, char **reply);

#endif  // HAVA_INTERCEPT_H

