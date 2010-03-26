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
 * gcc posm_extractor.c -lbz2 -lexpat -Wall -Werror -ansi
 */

#include <stdio.h>
#include <string.h>
#include <bzlib.h>
#include <expat.h>

#define BUFSIZE (8192)

void startElement(void *userData, const char *name, const char **atts)
{
	int i;

	printf("Start %s\n", name);
	for (i = 0; atts[i]; i++) {
		printf("\t%s\n", atts[i]);
	}
}

void endElement(void *userData, const char *name)
{
	printf("End %s\n", name);
}

int main(int argc, char *argv[], char *envp[])
{

	BZFILE *b;
	FILE *f;
	char buf[BUFSIZE];
	int bzerror;
	int verbosity;
	int small;
	int len;
	int done;
	int depth;
	XML_Parser parser;

	b = NULL;
	f = NULL;
	bzerror = BZ_OK;
	verbosity = 0;
	small = 0;
	len = 0;
	done = 0;
	depth = 0;
	parser = XML_ParserCreate(NULL);
/* TODO: error checks */
	XML_SetUserData(parser, &depth);
/* TODO: error checks */
	XML_SetElementHandler(parser, startElement, endElement);
/* TODO: error checks */

	f = fopen("map.osm.bz2", "rb");
	if (f == NULL) {
		fprintf(stderr, "Could not open map.osm.bz2\n");
		return 1;
	}

	b = BZ2_bzReadOpen(&bzerror, f, verbosity, small, NULL, 0);
	if (bzerror != BZ_OK) {
		BZ2_bzReadClose(&bzerror, b);
		fclose(f);
		fprintf(stderr, "Could not open bzip2 library for map.osm.bz2\n");
		return 1;
	}

	do {
		memset(buf, '\0', BUFSIZE);
		len = BZ2_bzRead(&bzerror, b, buf, BUFSIZE);
		if (bzerror != BZ_OK && bzerror != BZ_STREAM_END) {
			BZ2_bzReadClose(&bzerror, b);
			fclose(f);
			fprintf(stderr, "Read Error\n");
			return 1;
		}

		done = (bzerror == BZ_STREAM_END);

		if (!XML_Parse(parser, buf, len, done)) {
			BZ2_bzReadClose(&bzerror, b);
			fclose(f);
			fprintf(stderr, "%s at line %d\n", XML_ErrorString(XML_GetErrorCode(parser)), (int) XML_GetCurrentLineNumber(parser));
			return 1;
		}

	} while (!done);

	BZ2_bzReadClose(&bzerror, b);
	fclose(f);

	XML_ParserFree(parser);

	return 0;
}
