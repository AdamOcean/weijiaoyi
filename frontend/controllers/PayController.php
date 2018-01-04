<?php

namespace frontend\controllers;

use Yii;
use common\helpers\Curl;
use frontend\models\User;
use frontend\models\UserCoupon;
use frontend\models\Product;
use frontend\models\Order;
use frontend\models\ProductPrice;
use frontend\models\DataAll;
use frontend\models\UserCharge;
use common\helpers\FileHelper;
use common\helpers\Json;

class PayController extends \frontend\components\Controller
{
    //支付界面
    public function actionIndex()
    {
        $this->view->title = '充值';
        $amount = 1;

        //保存充值记录
        $userCharge = new UserCharge();
        $userCharge->user_id = u()->id;
        $userCharge->trade_no = u()->id . date("YmdHis") . rand(1000, 9999);
        $userCharge->amount = $amount;
        //1支付宝2微信3银行卡
        $userCharge->charge_type = 2;
        //充值状态：1待付款，2成功，-1失败
        $userCharge->charge_state = 1;
        if (!$userCharge->save()) {
            return false;
        }

        return $this->render('pay', compact('amount'));
    } 

    //异步支付回调地址
    public function actionNotify()
    {
        //测试订单号
        $trade_no = 1; 
        $userCharge = UserCharge::find()->where('trade_no = :trade_no', [':trade_no' => $trade_no])->one();
        //有这笔订单
        if (!empty($userCharge)) {
            //充值状态：1待付款，2成功，-1失败
            if ($userCharge->charge_state == 1) {
                //找到这个用户
                $user = User::findOne($userCharge->user_id);
                //给用户加钱
                $user->account += $userCharge->amount;
                if ($user->save()) {
                    //更新充值状态---成功
                    $userCharge->charge_state = 2;
                }
            }
            //更新充值记录表
            $userCharge->update();
        }
    } 
}
