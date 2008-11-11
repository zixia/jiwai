/* $Id: text.t.c 10 2008-11-08 16:24:02Z whw $ */

//#include <pcre.h>
#include <stdio.h>
#include <string.h>
#include <assert.h>

#include "../text.h"
#include "../log.h"

char *test = "hello world<FONT><FONT COLOR=\"red\"><FONT size=14pt>foo bar</FONT></FONT></FONT>, will you";

int main(int argc, char **argv) {
  TextTags tags = jidgin_text_init();
  gchar *msg;

  jidgin_text_add_tag(tags, "<FONT>");
  jidgin_text_add_tag(tags, "</FONT>");
  jidgin_text_add_tag(tags, "<blockquote>");

  msg = jidgin_text_filter(tags, test);
  jidgin_log(LOG_DEBUG, "filtered text: %s\n", msg);
  g_free(msg);

  jidgin_text_destroy(tags);

  msg = jidgin_text_filter_rough(test);
  jidgin_log(LOG_DEBUG, "filtered text: %s\n", msg);
  g_free(msg);
  return 0;
}

/*
const char test[] = "hello world<FONT><FONT COLOR=\"red\"><FONT size=14pt>foo bar</FONT></FONT></FONT>, will you";
const char pattern1[] = "s/<font.*>//ig";
const char pattern2[] = "s/<\/font.*>//ig";
*/

/*
const char test[] = "hello world";
const char pattern1[] = "hello\\s+(\\w+)";

int main() {
  pcre *re;
  int rc;
  const char *error;
  int erroffset;
  int ovector[4];
  re = pcre_compile(pattern1,
      PCRE_CASELESS | PCRE_MULTILINE,
      &error,
      &erroffset,
      NULL);

  if (NULL == re) {
    fprintf(stderr, "compile error: %s\n", error[erroffset]);
  }

  rc = pcre_exec(re,
      NULL,
      test,
      strlen(test),
      0,
      0,
      ovector,
      4);

  fprintf(stderr, "pattern: %s\n", pattern1);
  fprintf(stderr, "return[%d] ovector: %d:%d:%d:%d\n", rc, ovector[0], ovector[1], ovector[2], ovector[3]);

  return 0;
}
*/

