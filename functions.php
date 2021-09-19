<?php

//
// ─── FUNCTIONS ──────────────────────────────────────────────────────────────────
//

function makephp()
{
    return MakePHP::$instance;
}

function requires($functions)
{
    return makephp()->requires($functions);
}

function dependsOn(string $function)
{
    return makephp()->requires($functions);
}

function depends_on(string $function)
{
    return makephp()->requires($functions);
}

function _first(string $function)
{
    return makephp()->requires($functions);
}

function run($cmd)
{
    if (!is_string($cmd) && !is_array($cmd)) {
        throw new Exception("Invalid Argument: Only strings or string arrays accepted!", 1);
    }
    
    if(is_string($cmd)) {
        $cmd = [$cmd];
    }

    foreach ($cmd as $command) {
        runSingle($command) . (count($cmd) > 1 ? PHP_EOL : '');
    }
}

function runSingle(string $cmd) 
{
    echo "$ ./run build debug\n\n";
    passthru($cmd);

}

set_exception_handler(function ($th) {
    echo "\033[31m✘ Error:\033[0m " . $th->getMessage() . PHP_EOL;
});
