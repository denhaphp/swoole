<?php

use denha\swoole\App;
use denha\Config;
use denha\Exception\SwooleException;

use Swoole\Process;
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Server;
use Swoole\Coroutine\Server\Connection;
use Swoole\WebSocket\CloseFrame;
use Swoole\Http\Request;
use Swoole\Http\Response;
use function Swoole\Coroutine\run;

$denha = new class() {
    

    public function run()
    {   

        $paths = [
            $_SERVER['PWD'] ?? getcwd(),
            dirname(__DIR__),
            dirname(__DIR__, 4), // 在非工作路径，使用绝对路径启动
        ];

        foreach ($paths as $path)
        {
            $fileName = $path . '/vendor/autoload.php';
            if (is_file($fileName))
            {
                break;
            }
        }

        if (!is_file($fileName))
        {
            echo 'No file vendor/autoload.php', \PHP_EOL;
            exit(255);
        }

        require $fileName;

    
        run(function () {

            $app = (new App())->start('config.php');
            $configs = Config::includes('service.php');
            App::outFrameworkName();

            foreach($configs as  $item){
                $server = new Server($item['host'], $item['port'], false);
                $server->set([
                   'document_root'=>PUBLIC_PATH,
                   'enable_static_handler'=>true,
                ]);

                $server->handle('/', function ($request, $response) use ($app) {

                    try{

                        if($request->server['path_info'] === '/favicon.ico'){
                            return ;
                        }

                        $text = $app->mark($request->server['path_info'])->fetch()->getView();
                        $response->end($text);
                        
                    }catch(SwooleException $exception){

                        $message = $exception->getMessage();

                        echo $exception;

                        $response->end("<pre>$exception</pre>");
                    }
                 
                });
                $server->start();
            }

        });

    }
};

$denha->run();
