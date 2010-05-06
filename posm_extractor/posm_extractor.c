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

#define _BSD_SOURCE

#include <errno.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <strings.h>
#include <unistd.h>
#include <expat.h>

#undef DO_FORK

#define FREE(x)				\
	do {				\
		if (x != NULL) {	\
			free(x);	\
		}			\
	} while (0)

#define BUFSIZE (4194304)
#define LAT_MIN ( 41.50)
#define LAT_MAX ( 83.25)
#define LON_MIN (-141.25)
#define LON_MAX (-47.50)

/* for ottawa/gatineau: lat 45 to 47 | lon -77 to -74 */
/* for all of Canada: lat 41.50 to 83.25 | lon -141.25 to -47.50 */

typedef struct tag {
	char *key;
	char *value;
	struct tag *next;
} tag;

int parsing_node = 0;
char *id = NULL;
char *version = NULL;
char *timestamp = NULL;
char *lat = NULL;
char *lon = NULL;

tag *tag_list = NULL;

char *escapeQuotes(const char *unsafe)
{
	char *safe;
	int ulen;
	int len;
	int i;
	int j;

	if (unsafe == NULL) {
		return NULL;
	}

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
		} else if (safe[j] == '\\') {
			j++;
			safe[j] = '\\';
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

void startElement(void *userData, const char *ename, const char **atts)
{
	if (parsing_node && !strcmp(ename, "tag")) {
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
	} else if (!strcmp(ename, "node")) {
		parsing_node = 1;
		id = getAttribute("id", atts);
		version = getAttribute("version", atts);
		timestamp = getAttribute("timestamp", atts);
		lat = getAttribute("lat", atts);
		lon = getAttribute("lon", atts);
	}
}

int isPOI() {
	int found_name = 0;
	int found_amenity = 0;
	int found_shop = 0;
	int found_tourism = 0;
	int found_busstop = 0;
	struct tag *cur;

	if (tag_list == NULL) {
		return 0;
	}

	for (cur = tag_list; cur; cur = cur->next) {
		/* TODO: add bus stop check */

		if (!strcmp(cur->key, "name")) {
			found_name = 1;
		} else if (!strcmp(cur->key, "amenity")) {
			found_amenity = 1;
		} else if (!strcmp(cur->key, "shop")) {
			found_amenity = 1;
		} else if (!strcmp(cur->key, "tourism")) {
			found_amenity = 1;
		} else if (!strcmp(cur->key, "highway") && !strcmp(cur->value, "bus_stop")) {
			found_busstop = 1;
		}

		if (found_busstop || (found_name && (found_amenity || found_shop || found_tourism))) {
			return 1;
		}
	}

	return 0;
}

void endElement(void *userData, const char *ename)
{
	if (!strcmp(ename, "node")) {
		struct tag *cur;
		int i;
		char *_id = escapeQuotes(id);
		char *_version = escapeQuotes(version);
		char *_timestamp = escapeQuotes(timestamp);
		char *_lat = escapeQuotes(lat);
		char *_lon = escapeQuotes(lon);

		if (isPOI()) {

			fprintf(stdout, "INSERT IGNORE INTO node (id, version, timestamp, lat, lon) VALUES ('%s', '%s', '%s', '%s', '%s');\n", _id, _version, _timestamp, _lat, _lon);
			for (cur = tag_list; cur; cur = cur->next) {
				char *_k = escapeQuotes(cur->key);
				char *_v = escapeQuotes(cur->value);
				fprintf(stdout, "INSERT IGNORE INTO tag (node_id, k, v) VALUES ('%s', '%s', '%s');\n", _id, _k, _v);
				FREE(_k);
				FREE(_v);
			}
			fflush(stdout);

		}

		parsing_node = 0;

		FREE(id);
		FREE(version);
		FREE(timestamp);
		FREE(lat);
		FREE(lon);

		FREE(_id);
		FREE(_version);
		FREE(_timestamp);
		FREE(_lat);
		FREE(_lon);

		if (tag_list) {
			tag *cur;
			tag *old;

			cur = tag_list;

			while (cur) {
				FREE(cur->key);
				FREE(cur->value);

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
	int i;
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

	if (argc < 2) {
		fprintf(stderr, "To read from a file:\n\tposm_extractor filename.osm [filename-2.osm ...]\nTo read from stdin:\n\tposm_extractor -\n");
		return -1;
	}

	fprintf(stdout, "SET NAMES 'utf8' COLLATE 'utf8_unicode_ci';\n");
	fprintf(stdout, "SET CHARACTER SET 'utf8';\n");
	fprintf(stdout, "SET collation_connection = 'utf8_general_ci';\n");
	fprintf(stdout, "DELETE FROM node;\n");
	fprintf(stdout, "DELETE FROM tag;\n");
	fflush(stdout);

	for (i = 1; i < argc; i++) {

#ifdef DO_FORK
		pid_t pid = fork();

		if (pid == 0) {
#endif

			parser = XML_ParserCreate("UTF-8");
			if (parser == NULL) {
				fprintf(stderr, "Could not initialize parser.\n");
			}

			XML_SetUserData(parser, &depth);
			XML_SetElementHandler(parser, startElement, endElement);

			if (strlen(argv[i]) == 1 && argv[i][0] == '-') {
				f = stdin;
			} else {
				f = fopen(argv[i], "rb");
				if (f == NULL) {
					fprintf(stderr, "Could not open '%s'\n", argv[i]);
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

#ifdef DO_FORK
			return 0;
		} else if (pid == -1) {
			perror("fork");
			return -1;
		}
#endif
	}

#ifdef DO_FORK
	while (1) {
		int status = 0;
		pid_t pid = wait(&status);
		if (pid == -1 && errno == ECHILD) {
			break;
		}
	}
#endif

	return 0;
}
