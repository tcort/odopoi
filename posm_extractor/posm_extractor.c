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
 * gcc posm_extractor.c -lexpat -Wall -Werror -ansi -g3
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


int parsing_node = 0;
char *lat = NULL;
char *lon = NULL;
char *amenity = NULL;
char *name = NULL;

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

void startElement(void *userData, const char *ename, const char **atts)
{
	if (parsing_node && !strcmp(ename, "tag")) {
		if (!amenity) {
			amenity = getTagValue("amenity", atts);
		}

		if (!name) {
			name = getTagValue("name", atts);
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
			double dlat = strtod(lat, NULL);
			double dlon = strtod(lon, NULL);
			if (LAT_MIN <= dlat && dlat <= LAT_MAX && LON_MIN <= dlon && dlon <= LON_MAX) {
/* escape strings and insert into db */
				fprintf(stdout, "INSERT INTO poi (lat, lon, name, descr, sym) VALUES (%s, %s, '%s', '', '%s');\n", lat, lon, name, amenity);
				fflush(stdout);
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

/* TODO: error checking on these calls */
	parser = XML_ParserCreate("UTF-8");
	XML_SetUserData(parser, &depth);
	XML_SetElementHandler(parser, startElement, endElement);

	fprintf(stdout, "SET NAMES 'utf8' COLLATE 'utf8_unicode_ci';\n");
	fprintf(stdout, "SET CHARACTER SET 'utf8';\n");
	fprintf(stdout, "SET collation_connection = 'utf8_general_ci';\n");
	fprintf(stdout, "DELETE FROM poi;\n");
	fflush(stdout);

/* TODO: make filename a cmd line arg. if '-' is used read from stdin */
	f = fopen("map.osm", "rb");
	if (f == NULL) {
		fprintf(stderr, "Could not open map.osm.bz2\n");
		XML_ParserFree(parser);
		return 1;
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

	fclose(f);

	XML_ParserFree(parser);

	return 0;
}
