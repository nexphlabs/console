<?php

namespace Nexph\Console;

class OptimizeCommand extends Command
{
    protected string $name = 'optimize';
    protected string $description = 'Optimize Nexph runtime';

    public function execute(array $args = []): int
    {
        $this->parseArgs($args);
        $this->output('Compiling routes...');
        $this->compileRoutes();

        $this->output('Compiling container...');
        $this->compileContainer();

        $this->output('Compiling config...');
        $this->compileConfig();

        $this->output('Compiling middleware...');
        $this->compileMiddleware();

        $this->output('Compiling schedule...');
        $this->compileSchedule();

        $this->output('Generating preload...');
        $this->generatePreload();

        $this->output('Optimization complete');
        return 0;
    }

    private function compileRoutes(): void
    {
        $dir = 'storage/nexph/compiled';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents("$dir/routes.php", "<?php\nreturn [];\n");
    }

    private function compileContainer(): void
    {
        file_put_contents('storage/nexph/compiled/container.php', "<?php\nreturn [];\n");
    }

    private function compileConfig(): void
    {
        file_put_contents('storage/nexph/compiled/config.php', "<?php\nreturn [];\n");
    }

    private function compileMiddleware(): void
    {
        file_put_contents('storage/nexph/compiled/middleware.php', "<?php\nreturn [];\n");
    }

    private function compileSchedule(): void
    {
        file_put_contents('storage/nexph/compiled/schedule.php', "<?php\nreturn [];\n");
    }

    private function generatePreload(): void
    {
        $classes = [
            \Nexph\Server\HttpServer::class,
            \Nexph\Server\Router::class,
            \Nexph\Runtime\Runtime::class,
            \Nexph\Lifecycle\Lifecycle::class,
        ];
        $body = "<?php\n";
        foreach ($classes as $class) {
            $body .= "class_exists(" . var_export($class, true) . ");\n";
        }
        file_put_contents('storage/nexph/compiled/preload.php', $body);
    }
}
