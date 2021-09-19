<?php

//
// ─── MAKEPHP CLASS ────────────────────────────────────────────────────────────────
//


class MakePHP
{
    const MAKE_FILENAME = 'make.php';
    const ACCESSOR = 'makephp';

    public static $instance;

    public $root;
    public $functionMap = [];
    public $callLog = [];
    public $arguments = [];

    public $lastCalledFunction = null;

    public $header = [
        '┌┬┐┌─┐┬┌─┌─┐ ┌─┐┬ ┬┌─┐',
        '│││├─┤├┴┐├┤  ├─┘├─┤├─┘',
        '┴ ┴┴ ┴┴ ┴└─┘o┴  ┴ ┴┴ '
    ];
    
    public function __construct(array $argv)
    {
        $this->loadArguments($argv);
        self::$instance = &$this;
    }

    public function loadArguments(array $argv)
    {
        $arguments = $argv;
        array_shift($arguments);
        $this->arguments = $arguments;
    }

    public function loadMakeFunctions()
    {
        $path = $this->root . '/' . self::MAKE_FILENAME;

        /* Add namespace to make file */
        $content = file($path);
        $pattern = array('/^(namespace) (.*?);/', '/(<\?php)/', );
        $replace = array('', '$1 namespace Make;');

        file_put_contents($this->root . '/.pmake.tmp', implode('', preg_replace($pattern, $replace, $content)));

        $initialList = get_defined_functions(true)['user'];

        require_once $this->root . '/.pmake.tmp';

        $this->functionMap = array_values(array_diff(get_defined_functions(true)['user'], $initialList));

        unlink($this->root . '/.pmake.tmp');
    }

    public function called(string $function)
    {
        return in_array($function, $this->callLog, true);
    }

    public function functionExists(string $function)
    {
        return function_exists('make\\' . $function);
    }

    public function call(string $function)
    {
        $functionWithNamespace = 'make\\' . $function;

        if (!$this->functionExists($function)) {
            throw new Exception('Function "' . $function . '" not defined in ' . self::MAKE_FILENAME . '!', 1);
        }

        echo "\n> running \033[36;1m{$function}()\033[0m" . PHP_EOL;

        $this->lastCalledFunction = $function;
        $this->callLog[] = $function;
        
        return call_user_func($functionWithNamespace);
    }
    
    public function getFirstFunction()
    {
        $function = $this->functionMap[0] ?? null;
        return explode('\\', $function ?? '')[1] ?? null;
    }

    public static function createProjectFile()
    {
        $path = getcwd() . '/' . self::MAKE_FILENAME;

        if (file_exists($path)) {
            /* Read the user Input and add the question */
            print("\033[1;34m?\033[0m A " . self::MAKE_FILENAME .  " file already exists in this directory.\n");
            $replace = readline("  Do you want to replace it? (Y/N) : ");
            $replace = empty($replace) ? 'y' : $replace;
            
            if (strtolower($replace) !== 'y') {
                die("\033[31mAborting...\033[0m" . PHP_EOL);
            }

            echo PHP_EOL;
        }
 
        file_put_contents($path, file_get_contents('https://raw.githubusercontent.com/cornejong/makephp/main/example.make.php'));
        echo "\033[32m✓ Successfully created " . self::MAKE_FILENAME .  " file!\033[0m \033[2m\n  (path: {$path})\033[0m;" . PHP_EOL;
    }

    public function requires($functions, string $term = 'requires')
    {
        if (!is_array($functions) && !is_string($functions)) {
            throw new Exception('Invalid argument type "' . gettype($functions) . '" for requires(). Only strings and string arrays allowed.', 1);
        }

        if (is_string($functions)) {
            $functions = [$functions];
        }

        foreach ($functions as $function) {
            if (!$this->functionExists($function)) {
                throw new Exception("Function '{$function}' does not exists in make.php!", 1);
            }

            if ($this->called($function)) {
                return;
            }

            $invoker = $this->lastCalledFunction ?? 'UNKNOWN';

            echo "\033[33m> \033[36m{$invoker}()\033[0m: {$term} \033[36m{$function}()\033[0m" . PHP_EOL;

            $this->call($function);
        }

        echo "\n\033[36m> {$invoker}()\033[0m: continues..." . PHP_EOL;
    }

    public function selfInstall()
    {
        if (\file_exists('/usr/local/bin/' . self::ACCESSOR)) {
            $override = readline('? Do you want to override the existing ' . self::ACCESSOR . ' executable? (Y/N) : ');
            if (strtolower($override) === 'n') {
                echo "Aborting..\n";
                exit(0);
            }
            print(PHP_EOL);

            $command = 'rm -f -v /usr/local/bin/' . self::ACCESSOR;
            print($command . PHP_EOL);
            system($command, $status);
            $status === 0
                ? print(PHP_EOL . '✓ Successfully removed old version of ' . self::ACCESSOR . '!' . PHP_EOL)
                : print(PHP_EOL . '✖ Failed to removed old version of ' . self::ACCESSOR . '!' . PHP_EOL) && exit(1);

            print(PHP_EOL);
        }

        $command = 'cp -v "' . \Phar::running(false) . '" /usr/local/bin';
        print($command . PHP_EOL);
        system($command, $status);
        
        $status === 0
            ? print(PHP_EOL . '✓ Successfully installed ' . self::ACCESSOR . '!' . PHP_EOL)
            : print(PHP_EOL . '✖ Failed to install ' . self::ACCESSOR . '!' . PHP_EOL);

        return $status;
    }

    public function handle()
    {        
        if (($this->arguments[0] ?? '') === '--self-install') {
            return $this->selfInstall();
        }

        if (in_array($this->arguments[0] ?? '', ['--init', '-i', '--initialize'])) {
            return $this->createProjectFile();
        }

        $this->root = Locator::find(self::MAKE_FILENAME);

        if (!$this->root) {
            throw new Exception(self::MAKE_FILENAME .  " file not found!\nFirst create a " . self::MAKE_FILENAME .  " file in your project root directory.\nOr run $ '\033[36;1m" . self::ACCESSOR .  " --init\033[0m' to create it in the current working directory.", 1);
        }

        $this->loadMakeFunctions();

        if (count($this->arguments) === 0) {
            $function = $this->getFirstFunction();
            if ($function === null) {
                throw new Exception('No functions defined in ' . self::MAKE_FILENAME .  '!', 1);
            }
        } else {
            $function = $this->arguments[0];
        }

        if (!$this->functionExists($function)) {
            throw new Exception('Function "' . $function . '" not defined in ' . self::MAKE_FILENAME .  '!', 1);
        }

        $output = $this->call($function);

        if($output) {
            run($output);
        }

        echo "\n\033[32m✓ Successfully finished!\033[0m" . PHP_EOL;
    }
}
