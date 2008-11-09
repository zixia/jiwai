/* $Id: text.h 7 2008-11-08 14:58:17Z whw $ */

#ifndef HAVE_TEXT_H
#define HAVE_TEXT_H

#include <glib.h>

#ifdef __cplusplus
extern "C" {
#endif

typedef GHashTable *TextTags;

TextTags jidgin_text_init();

void jidgin_text_destroy(TextTags);

void jidgin_text_add_tag(TextTags, char *);

void jidgin_text_remove_tag(TextTags, char *);

gchar *jidgin_text_filter(TextTags, char *);

gchar *jidgin_text_filter_rough(const char *);

#endif  // HAVE_TEXT_H

