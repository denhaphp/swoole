<?php
declare (strict_types = 1);

namespace denha\swoole;

use denha\App as mainApp;

class App extends mainApp
{
    public function start($configPath = ''){
        parent::loadEnv(App::getFramePath());
        parent::loadConfig($configPath);
        parent::loadHelper();
        return $this;
    }   
}