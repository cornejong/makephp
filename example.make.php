<?php

/**
 * This is a makephp example file.
 * You'd place this file in your project's root directory
 * 
 * If you call 'makephp' without an argument the app will call
 * the first function in this file.
 * 
 * otherwise the specified function will be called
 */

function main()
{
	/* Define the requirements for this function */
	/* Either a single function name (as a string) */
	/* Or an array of function names */
    requires([
		'setup',
		'build',
		'test',
	]);
}

function prepareTest()
{
	# code...
}

function test()
{
    requires('prepareTest');
    # code ...
}

function setup() {
	# code ...
}

function build() {
	# code ...
}


?>