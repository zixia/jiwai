/* $Id: text.c 8 2008-11-08 16:13:44Z whw $ */

#include <string.h>
#include <assert.h>

#include "text.h"
#include "log.h"

TextTags jidgin_text_init() {
  TextTags jidgin_text_tags = g_hash_table_new_full(g_str_hash,
      g_str_equal,
      g_free,
      NULL);

  return jidgin_text_tags;
}

void jidgin_text_destroy(TextTags tags) {
  if (tags) {
    g_hash_table_destroy(tags);
  }
}

void jidgin_text_add_tag(TextTags tags, char *tag) {
  assert( tags );
  g_hash_table_insert(tags, g_strdup(tag), NULL);
}

void jidgin_text_remove_tag(TextTags tags, char *tag) {
  assert( tags );
  g_hash_table_remove(tags, tag);
}

static gchar *jidgin_text_filter_func(const gchar *tag, gchar **orig) {
  return g_strdup(*orig);
}

static void jidgin_text_filter_traverse(gpointer key, gpointer value, gpointer user_data) {
  gchar **msg = (gchar **)user_data;
  gchar *tag =(gchar *)key;
  gchar *new = jidgin_text_filter_func(tag, msg);
  g_free(*msg);
  *msg = new;
}

gchar *jidgin_text_filter(TextTags tags, char *msg) {
  gchar *dup;
  if (!tags) return msg;
  dup = g_strdup(msg);
  g_hash_table_foreach(tags, jidgin_text_filter_traverse, &dup);
  return dup;
}

gchar *jidgin_text_filter_rough(const char *msg) {
  int sofar = 0;
  char tag_start = '<';
  char tag_end = '>';
  int within_tag = 0;
  gchar *hack = (gchar *)g_malloc(sizeof(gchar) * (strlen(msg) + 1));

  for (; *msg; msg++) {
    if (*msg == tag_start) {
      ++within_tag;
      continue;
    }
    if (*msg == tag_end && within_tag > 0) {
      --within_tag;
      continue;
    }

    if (!within_tag) {
      *(hack + sofar) = *msg;
      ++sofar;
    }
  }

  hack[sofar] = '\0';
  return hack;
}

