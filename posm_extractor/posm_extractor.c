/*
 * Copyright (C) 2010 Thomas Cort <linuxgeek@gmail.com>
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted (subject to the limitations in the
 * disclaimer below) provided that the following conditions are met:
 *
 *  * Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 *
 *  * Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the
 *    distribution.
 *
 *  * Neither the name of Thomas Cort nor the names of its
 *    contributors may be used to endorse or promote products derived
 *    from this software without specific prior written permission.
 *
 * NO EXPRESS OR IMPLIED LICENSES TO ANY PARTY'S PATENT RIGHTS ARE
 * GRANTED BY THIS LICENSE.  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT
 * HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR
 * BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN
 * IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/*
 * gcc posm_extractor.c -lexpat -Wall -Werror -ansi -g3 -o posm_extractor
 */

#define _BSD_SOURCE

#include <stdio.h>
#include <string.h>
#include <expat.h>

#define BUFSIZE (4194304)
#define LAT_MIN ( 45.00)
#define LAT_MAX ( 47.00)
#define LON_MIN (-77.00)
#define LON_MAX (-74.00)

/* for all of Canada: lat 41.50 to 83.25 | lon -141.25 to -47.50 */

typedef struct tag {
	char *key;
	char *value;
	struct tag *next;
} tag;

int parsing_node = 0;
char *lat = NULL;
char *lon = NULL;
char *amenity = NULL;
char *name = NULL;
tag *tag_list = NULL;

char *escapeQuotes(const char *unsafe)
{
	char *safe;
	int ulen;
	int len;
	int i;
	int j;

	ulen = strlen(unsafe);
	len = (ulen * 2) + 1;

	safe = (char *) malloc(sizeof(char) * len);
	if (!safe) {
		fprintf(stderr, "malloc() failed\n");
		exit(1);
	}
	memset(safe, '\0', sizeof(char) * len);

	for (i = 0, j = 0; i < ulen && j < len; i++, j++) {
		safe[j] = unsafe[i];
		if (safe[j] == '\'') {
			j++;
			safe[j] = '\'';
		}
	}

	return safe;
}

char *getAttribute(const char *name, const char **atts)
{
	int i;
	char *attribute;

	attribute = NULL;

	for (i = 0; atts[i]; i = i + 2) {
		if (!strcmp(atts[i], name)) {
			attribute = strdup(atts[i + 1]);
			break;
		}
	}

	return attribute;
}

char *getTagValue(const char *key, const char **atts)
{
	int i;
	int j;
	char *value;

	value = NULL;

	/* locate key */
	for (i = 0; atts[i]; i = i + 2) {
		if (!strcmp(atts[i], "k") && !strcmp(atts[i + 1], key)) {
			for (j = 0; atts[j]; j = j + 2) {
				if (!strcmp(atts[j], "v")) {
					value = strdup(atts[j + 1]);
					break;
				}
			}
			break;
		}
	}

	return value;
}

char *join(char *a, char *b) {
	char *result;
	int lena;
	int lenb;

	lena = 0;
	lenb = 0;

	if (a) {
		lena = strlen(a);
	}

	if (b) {
		lenb = strlen(b);
	}

	result = (char *) malloc((sizeof(char) * (lena + lenb)) + 1);
	if (!result) {
		fprintf(stderr, "malloc() failed\n");
		exit(1);
	}
	memset(result, '\0', (sizeof(char) * (lena + lenb)) + 1);

	strcpy(result, a);
	strcat(result, b);

	return result;
}

void startElement(void *userData, const char *ename, const char **atts)
{
	if (parsing_node && !strcmp(ename, "tag")) {
		int matched = 0;

		if (!amenity) {
			amenity = getTagValue("amenity", atts);
			if (amenity) {
				matched = 1;
			}
		}

		if (!name) {
			name = getTagValue("name", atts);
			if (name) {
				matched = 1;
			}
		}

		if (!matched) {
			tag *t;

			t = (tag *) malloc(sizeof(tag));
			if (!t) {
				fprintf(stderr, "malloc() failed\n");
				exit(1);
			}
			memset(t, '\0', sizeof(tag));

			t->key = getAttribute("k", atts);
			t->value = getAttribute("v", atts);
			t->next = tag_list;
			tag_list = t;
		}

	} else if (!strcmp(ename, "node")) {
		parsing_node = 1;
		lat = getAttribute("lat", atts);
		lon = getAttribute("lon", atts);
	}
}

void endElement(void *userData, const char *ename)
{
	if (!strcmp(ename, "node")) {
		if (amenity && name && lat && lon) {
			char *tmp;
			char *a;
			char *n;
			char *la;
			char *lo;
			char *d;

			double dlat = strtod(lat, NULL);
			double dlon = strtod(lon, NULL);
			if (LAT_MIN <= dlat && dlat <= LAT_MAX && LON_MIN <= dlon && dlon <= LON_MAX) {
				d = strdup("");

				if (tag_list) {
					tag *t;

					if (d) {
						free(d);
					}

					d = strdup("<table class=\"poi\">");

					for (t = tag_list; t; t = t->next) {
						char *r;
						char *k;
						char *v;

						k = escapeQuotes(t->key);
						v = escapeQuotes(t->value);

						r = join("<tr><td class=\"k\">", k);
						tmp = r;
						r = join(r, "</td><td class=\"v\">");
						free(tmp);
						tmp = r;
						r = join(r, v);
						free(tmp);
						tmp = r;
						r = join(r, "</td></tr>");
						free(tmp);
						tmp = d;
						d = join(d, r);
						free(tmp);
						free(r);

						if (k) {
							free(k);
							k = NULL;
						}
						if (v) {
							free(v);
							v = NULL;
						}
					}

					tmp = d;
					d = join(d, "</table>");
					free(tmp);
				}

				a = escapeQuotes(amenity);
				n = escapeQuotes(name);
				la = escapeQuotes(lat);
				lo = escapeQuotes(lon);
				tmp = escapeQuotes(d);
				free(d);
				d = tmp;

				fprintf(stdout, "INSERT INTO poi (lat, lon, name, descr, sym) VALUES (%s, %s, '%s', '%s', '%s');\n", la, lo, n, d, a);
				fflush(stdout);

				if (a) {
					free(a);
					a = NULL;
				}

				if (n) {
					free(n);
					n = NULL;
				}

				if (la) {
					free(la);
					la = NULL;
				}

				if (lo) {
					free(lo);
					lo = NULL;
				}

				if (d) {
					free(d);
					d = NULL;
				}

			}
		}

		parsing_node = 0;

		free(lat);
		lat = NULL;

		free(lon);
		lon = NULL;

		free(amenity);
		amenity = NULL;

		free(name);
		name = NULL;

		if (tag_list) {
			tag *cur;
			tag *old;

			cur = tag_list;

			while (cur) {
				if (cur->key) {
					free(cur->key);
					cur->key = NULL;
				}
				if (cur->value) {
					free(cur->value);
					cur->value = NULL;
				}
				old = cur;
				cur = cur->next;
				free(old);
			}

			tag_list = NULL;
		}
	}
}

int main(int argc, char *argv[], char *envp[])
{

	FILE *f;
	char buf[BUFSIZE];
	int len;
	int done;
	int depth;
	XML_Parser parser;

	f = NULL;
	len = 0;
	done = 0;
	depth = 0;

	if (argc != 2) {
		fprintf(stderr, "To read from a file:\n\tposm_extractor [filename.osm]\nTo read from stdin:\n\tposm_extractor -\n");
		return -1;
	}

	parser = XML_ParserCreate("UTF-8");
	if (parser == NULL) {
		fprintf(stderr, "Could not initialize parser.\n");
	}

	XML_SetUserData(parser, &depth);
	XML_SetElementHandler(parser, startElement, endElement);

	fprintf(stdout, "SET NAMES 'utf8' COLLATE 'utf8_unicode_ci';\n");
	fprintf(stdout, "SET CHARACTER SET 'utf8';\n");
	fprintf(stdout, "SET collation_connection = 'utf8_general_ci';\n");
	fprintf(stdout, "DELETE FROM poi;\n");
	fflush(stdout);

	if (strlen(argv[1]) == 1 && argv[1][0] == '-') {
		f = stdin;
	} else {
		f = fopen(argv[1], "rb");
		if (f == NULL) {
			fprintf(stderr, "Could not open '%s'\n", argv[1]);
			XML_ParserFree(parser);
			return 1;
		}
	}

	do {
		len = fread(buf, sizeof(char), BUFSIZE, f);
		done = feof(f);

		if (!XML_Parse(parser, buf, len, done)) {
			fclose(f);
			fprintf(stderr, "Error (%d): %s at line %d\n", XML_GetErrorCode(parser), XML_ErrorString(XML_GetErrorCode(parser)), (int) XML_GetCurrentLineNumber(parser));
			XML_ParserFree(parser);
			return 1;
		}

	} while (!done);

	if (f != stdin) {
		fclose(f);
	}

	XML_ParserFree(parser);

	return 0;
}
