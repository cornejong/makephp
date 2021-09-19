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
	/* These functions will be called before the */
	/* execution of the rest of the function */
    requires([
		'setup',
		'build',
		'test',
	]);

	/* You van also just run terminal commands */
	run('git gc --auto');
	/* Or a list of them */
	run([
        'composer install --no-dev',
        'echo $PATH'
    ]);

	/* Or if you don't have to do anything after that */
    return 'git gc --auto';
	/* Or again, a list of commands */
	return [
        'composer install --no-dev',
        'echo $PATH'
    ];
}

function prepareTest() {
    # code ...
}

function test() {
    # code ...
}

function setup() {
    # code ...
}

function build() {
	# code ...
}


?>