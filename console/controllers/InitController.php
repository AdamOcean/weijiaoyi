<?php

namespace console\controllers;

use console\models\GatherSina;
use console\models\GatherXinfu;
use console\models\GatherSinaStock;
use console\models\GatherYiyuan;
use common\helpers\System;

class InitController extends \common\components\ConsoleController
{
    public function actionUser()
    {
        echo 'Input User Info' . "\n";

        $username = $this->prompt('Input Username:');
        $password = $this->prompt('Input password:');
        
        $user = new \frontend\models\User;

        $user->username = $username;
        $user->password = $password;
        $user->setPassword();

        if (!$user->save()) {
            foreach ($user->getErrors() as $field => $errors) {
                array_walk($errors, function($error) {
                    echo "$error\n";
                });
            }
        }
    }

    public function actionHq()
    {
		$cnt=0;
        $path = System::isWindowsOs() ? '' : './';
        while (true) {
			$cnt++;
            echo exec('/www/wdlinux/phps/56/bin/php /www/web/www_xys8000_cn/public_html/yii init/gather2');
            echo exec('/www/wdlinux/phps/56/bin/php /www/web/www_xys8000_cn/public_html/yii init/gather');
            sleep(1);
			if($cnt>60)break;
        }
    }

    public function actionGather()
    {
        $gather = new GatherSina;
        $gather->run();
    }

    public function actionGather2()
    {
        $gather = new GatherXinfu;
        $gather->run();
    }

    public function actionGather3()
    {
        $gather = new GatherSinaStock;
        $gather->run();
    }

    public function actionGather4()
    {
        $gather = new GatherYiyuan;
        $gather->run();
    }
}
