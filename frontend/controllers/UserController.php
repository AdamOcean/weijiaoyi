<?php

namespace frontend\controllers;

use Yii;
use frontend\models\User;
use frontend\models\UserAccount;
use frontend\models\UserWithdraw;
use frontend\models\UserCharge;
use common\helpers\FileHelper;
use frontend\models\Product;
use frontend\models\Order;
use frontend\models\ProductPrice;
use frontend\models\BankCard;
use frontend\models\Coupon;
use frontend\models\UserCoupon;
use frontend\models\DataAll;

class UserController extends \frontend\components\Controller
{
    public function beforeAction($action)
    {
        $actions = ['recharge', 'pay'];
        if (user()->isGuest && !in_array($this->action->id, $actions)) {
            $wx = session('wechat_userinfo');
            if (!empty($wx)) {
                $user = User::find()->where(['open_id' => $wx['openid']])->one();
                $user->login(false);
            } else {
                $code = get('code');
                if (empty($code)) {
                    $this->redirect(['/wechart.php']);
                    return false;
                } else {
                    User::registerUser($code);
                }
            }
        }
        if (!parent::beforeAction($action)) {
            return false;
        } else {
            return true;
        }
    }

    public function actionIndex()
    {
        $this->view->title = '我的个人中心';
        if (user()->isGuest) {
            return $this->redirect('/site/login');
        }
         //test(u()->id);
        $user = User::findModel(u()->id);
        $manager = '申请经纪人';
        //如果是经纪人
        if ($user->is_manager == User::IS_MANAGER_YES) {
            $manager = '我是经纪人';
        }
    
        return $this->render('index', compact('user', 'manager'));
    }

    public function actionShare()
    {
        $this->view->title = '我的分享';
        if (user()->isGuest) {
            return $this->redirect('/site/login');
        }
        //生成二维码
        require Yii::getAlias('@vendor/phpqrcode/phpqrcode.php');

        $url = 'http://' . $_SERVER['HTTP_HOST'] . url(['/site/shareUrl', 'id' => u()->id]); //二维码内容 
        $errorCorrectionLevel = 'L';//容错级别   
        $matrixPointSize = 6;//生成图片大小 
        $filePath = Yii::getAlias('@webroot/' . config('uploadPath') . '/images/');
        FileHelper::mkdir($filePath);
        $src = $filePath . 'code_' . u()->id . '.png';
        //生成二维码图片   
        \QRcode::png($url, $src, $errorCorrectionLevel, $matrixPointSize, 2);
        $img = config('uploadPath') . '/images/code_' . u()->id . '.png';  

        return $this->render('share', compact('img', 'url'));
    }

    public function actionWithDraw()
    {
        $this->view->title = '提现';

        $user = User::findModel(u()->id);
        $userAccount = UserAccount::find()->where(['user_id' => u()->id])->one();
        if (empty($userAccount)) {
            $userAccount = new UserAccount();
        }
        $userAccount->scenario = 'withDraw';
        $userWithdraw = new UserWithdraw();
        if ($userAccount->load(post()) || $userWithdraw->load(post())) {
            $userWithdraw->amount = post('UserWithdraw')['amount'];
            if (!is_numeric($userWithdraw->amount)) {
                return error('取现金额必须是数字');
            }

            if ($userWithdraw->amount <= 2) {
                return error('取现不能小于等于2元！');
            }

            if ($userWithdraw->amount < 0 || $userWithdraw->amount > ($user->account - $user->blocked_account)) {
                return error('取现金额不能超过您的可用余额！');
            }
            if ($user->account < 0 || $user->blocked_account < 0) {
                return error('您的账户暂时不能提现');
            }
            if ($userAccount->verifyCode != session('verifyCode')) {
                return error('短信验证码不正确');
            }

            $userAccount->user_id = $userWithdraw->user_id = u()->id;
            $userAccount->bank_user = $userAccount->realname;
            $userAccount->id_card = 'xx';
            if ($userAccount->validate()) {
                if ($userAccount->id) {
                    $userAccount->update();
                } else {
                    $userAccount->insert(false);
                }
                $userWithdraw->account_id = $userAccount->id;
                $userWithdraw->insert(false);
                //扣除取现金额
                $user->account -= $userWithdraw->amount;
                $user->save(false);
                session('verifyCode', null);
                return success('提现申请成功！');
            } else {
                return error($userAccount);
            }
        }
        return $this->render('withDraw', compact('userAccount', 'userWithdraw', 'user'));
    }

    public function actionTransDetail()
    {
        $this->view->title = '商品明细';

        $query = Order::find()->where(['order_state' => Order::ORDER_THROW, 'user_id' => u()->id])->with('product')->orderBy('order.updated_at DESC');

        $data = $query->paginate(PAGE_SIZE);
        $count = $query->totalCount;
        $pageCount = $count / PAGE_SIZE;
        if (!is_int($pageCount)) {
            $pageCount = (int)$pageCount + 1;
        }
        if (get('p')) {
            return success($this->renderPartial('_transDetail', compact('data')), $pageCount);
        }

        return $this->render('transDetail', compact('count', 'pageCount', 'data'));
    }

    public function actionOutMoney()
    {
        $this->view->title = '入金记录';

        $query = UserCharge::find()->where(['charge_state' => UserCharge::CHARGE_STATE_PASS, 'user_id' => u()->id])->orderBy('created_at DESC');
        $data = $query->paginate(PAGE_SIZE);
        $pageCount = $query->totalCount / PAGE_SIZE;
        if (!is_int($pageCount)) {
            $pageCount = (int)$pageCount + 1;
        }
        if (get('p') > 1) {
            return $this->renderPartial('_outMoney', compact('data'));
        }

        return $this->render('outMoney', compact('count', 'pageCount', 'data'));
    }
    /**
     * @authname 出金记录
     */
    public function actionInsideMoney()
    {
        $this->view->title = '出金记录';
        $query = UserWithdraw::find()->where(['user_id' => u()->id])->orderBy('created_at DESC');
        // 每页显示几条
        $data = $query->paginate();
        // 一共多少页
        $pageCount = $query->totalCount / PAGE_SIZE;
        if (!is_int($pageCount)) {
            $pageCount = (int)$pageCount + 1;
        }
        if (get('p') > 1) {
            return $this->renderPartial('_insideMoney', compact('data'));
        }
        return $this->render('insideMoney', compact('pageCount','data'));
    }


    public function actionSetting()
    {
        $this->view->title = '个人设置';

        return $this->render('setting');
    }

    public function actionManager()
    {
        $this->view->title = '申请经纪人';
        //如果是经纪人
        if (u()->is_manager == User::IS_MANAGER_YES) {
            $this->view->title = '我是经纪人';
            $idArr = User::getUserOfflineId();
            $data = User::getUserOfflineData($idArr);
            return $this->render('isManager', compact('data', 'idArr'));
        }
        $userAccount = UserAccount::find()->where(['user_id' => u()->id])->one();
        if (empty($userAccount)) {
            $userAccount = new UserAccount();
        }
        if ($userAccount->load(post())) {
            $userAccount->user_id = u()->id;
            $userAccount->bank_user = $userAccount->realname;
            if ($userAccount->validate()) {
                if ($userAccount->id) {
                    $userAccount->update();
                } else {
                    $userAccount->insert(false);
                }
                $user = User::findModel(u()->id);
                $user->apply_state = User::APPLY_STATE_WAIT;
                $user->update();
                return success('信息提交成功！');
            } else {
                return error($userAccount);
            }
        }
        return $this->render('manager', compact('userAccount'));
    }

    public function actionMyOffline()
    {
        $this->view->title = '名下用户记录';
        //如果是经纪人
        if (u()->is_manager != User::IS_MANAGER_YES) {
            return $this->redirect('/site/wrong');
        }
        //名下的用户
        $idArr = User::getUserOfflineId();
        $idArr = array_merge($idArr[0], $idArr[1]);
        $query = User::find()->where(['state' => User::STATE_VALID])->andWhere(['in', 'id', $idArr])->orderBy('created_at DESC');
        $data = $query->paginate(PAGE_SIZE);

        return $this->render('myOffline', compact('data'));
    }

    /**
     * @authname 绑定银行卡
     */
    public function actionBankCard()
    {
        $bankCard = BankCard::find()->where(['user_id' => u()->id])->one();
        if (empty($bankCard)) {
            $bankCard = new BankCard;
        }
        // test(u()->id);
        $bankCard->scenario = 'bank';
        $this->layout = 'empty';
        if($bankCard->load(post())) {
            if ($bankCard->validate()) {
                $bankCard->user_id = u()->id;
                if ($bankCard->id) {
                    $bankCard->update();
                } else {
                    $bankCard->insert(false);
                }
                $charge = UserCharge::epayBankCard($bankCard);
                if($charge) {
                    return success('绑定成功');
                }else {
                    return error('绑定失败，请确认您的信息是否正确');
                }
            } else {
                return error($bankCard);
            }         
        }
        return $this->render('bankCard', compact('bankCard'));
    }

    public function actionCharge()
    {     
        $this->view->title = '微信安全支付';
        $amount = 5000;
        //生成二维码
        require Yii::getAlias('@vendor/WxPayPubHelper/WxPayPubHelper.php');
        //保存充值记录
        $userCharge = UserCharge::find()->where(['charge_state' => UserCharge::CHARGE_STATE_WAIT, 'user_id' => u()->id])->andWhere(['>', 'created_at', date('Y-m-d 00:00:00')])->orderBy('created_at DESC')->one();
        if (empty($userCharge)) {
            $userCharge = new UserCharge();
            $userCharge->user_id = u()->id;
            $userCharge->trade_no = u()->id . date("YmdHis") . rand(1000, 9999);
            // $userCharge->amount = floatval($amount * (1 - config('charge_rate', '0.06')));
            $userCharge->amount = $amount;
            $userCharge->charge_type = 2;
            $userCharge->charge_state = UserCharge::CHARGE_STATE_WAIT;
            if (!$userCharge->save()) {
                return false;
            }
        } else {
            $userCharge->trade_no = u()->id . date("YmdHis");
            if (!$userCharge->save()) {
                return false;
            }
            $amount = $userCharge->amount;
        }
        //微信生成订单
        $jsApi = new \JsApi_pub();
        $openid = u()->open_id;
        $unifiedOrder = new \UnifiedOrder_pub();
        $unifiedOrder->setParameter("openid", $openid);//商品描述
        $unifiedOrder->setParameter("body", "华中商品充值");//商品描述
        $out_trade_no = $userCharge->trade_no;
        $unifiedOrder->setParameter("out_trade_no", $out_trade_no);//商户订单号
        $unifiedOrder->setParameter("total_fee", $amount * 100);//总金额
        $unifiedOrder->setParameter("notify_url", "http://" . $_SERVER['HTTP_HOST'] . "/site/ajax-update-status");//通知地址
        $unifiedOrder->setParameter("trade_type", "JSAPI");//商品类型
        $prepay_id = $unifiedOrder->getPrepayId();
        $jsApi->setPrepayId($prepay_id);
        $jsApiParameters = $jsApi->getParameters();

        return $this->render('recharge', compact('jsApiParameters', 'amount', 'userCharge'));
    }

    //请求更新订单钱数
    public function actionAjaxUpdateCharge()
    {
        $data = post('data');
        if (is_int($data['amount']) || $data['amount'] <= 0) {
            return error('金额参数非法！');
        }
        $userCharge = UserCharge::find()->where(['charge_state' => UserCharge::CHARGE_STATE_WAIT, 'user_id' => u()->id, 'id' => $data['id']])->one();
        if (!empty($userCharge)) {
            $userCharge->amount = floatval($data['amount']);
            $userCharge->trade_no = u()->id . date("YmdHis") . rand(1000, 9999);
            if ($userCharge->save()) {
                //微信生成订单
                require Yii::getAlias('@vendor/WxPayPubHelper/WxPayPubHelper.php');
                $jsApi = new \JsApi_pub();
                $openid = u()->open_id;
                $unifiedOrder = new \UnifiedOrder_pub();
                $unifiedOrder->setParameter("openid", $openid);//商品描述
                $unifiedOrder->setParameter("body", "华中商品充值");//商品描述
                $unifiedOrder->setParameter("out_trade_no", $userCharge->trade_no);//商户订单号
                $unifiedOrder->setParameter("total_fee", $userCharge->amount * 100);//总金额
                $unifiedOrder->setParameter("notify_url", "http://" . $_SERVER['HTTP_HOST'] . "/site/ajax-update-status");//通知地址
                $unifiedOrder->setParameter("trade_type", "JSAPI");//商品类型
                $prepay_id = $unifiedOrder->getPrepayId();
                $jsApi->setPrepayId($prepay_id);
                $jsApiParameters = $jsApi->getParameters();
                return success($jsApiParameters);
            }
        }
        return error('数据异常！');
    }

    public function actionTt()//测试充值
    {
        $data = post('data');
        $userCharge = UserCharge::find()->where(['charge_state' => UserCharge::CHARGE_STATE_WAIT, 'user_id' => u()->id, 'id' => $data['id']])->one();
        if (!empty($userCharge)) {
            // test($userCharge->amount);
            $userCharge->charge_state = 2;
            if ($userCharge->save()) {
                $user = User::findOne($userCharge->user_id);
                $user->account += $userCharge->amount;
                if ($user->save()) {
                    return success('成功');
                }
            } else {
                return error($userCharge);
            }
        }
        return error('失败！');
    }

    public function actionWrong()
    {
        $this->view->title = '错误';
        return $this->render('wrong');
    }

    public function actionRecharge()
    {     
        $this->view->title = '充值';
        $this->layout = 'empty';
        return $this->render('recharge');
    }

    public function actionWechatPay()
    {     
        $this->view->title = '微信充值';
        
        return $this->render('wechatPay');
    }

    public function actionPay()
    {
        $this->layout = 'empty';
        $this->view->title = '安全支付';
        $amount = YII_DEBUG ? 0.01 : post('amount', '0.01');


        switch (post('type', 2)) {
            case 1:
                $html = UserCharge::payWamfuwxsm($amount);//微信扫码支付
                if (!$html) {
                    return $this->redirect(['site/wrong']);
                }
                $html = json_decode($html,true);
                $html['pay_type'] = '1';
                return $this->render('gzh', compact('html'));
                break;

            case 2:
                $html = UserCharge::payWamfuzfb($amount);//支付宝
                if (!$html) {
                    return $this->redirect(['site/wrong']);
                }
                $html = json_decode($html,true);
                $html['pay_type'] = '2';
                return $this->render('gzh', compact('html'));
                break;

            
            default:
                
                $html = UserCharge::payWamfuhf($amount);//微信H5
                
                break;
        }




        
    }
    
     public function actionOnlinepay()
    {
     echo "opstate=0";  
        
    }
    // public function actionPay()
    // {

        // $this->layout = 'empty';
        // $this->view->title = '微信安全支付';
        // $amount = post('amount', '0.01');
        // $type = post('type');
        // if($type == 3) {
        //     // $amount = 1;
        //     // 银联支付
        //     $info = UserCharge::payExtend($amount, post('user_id'));
        //     if (!$info) {
        //         return $this->redirect('/site/wrong');
        //     }
        //     return $this->render('extendPay', compact('info'));
        // } else if($type == 2) {
        //     // 微信支付
        //     $info = UserCharge::payHxWxpay($amount, post('user_id'));
        //     if (!$info) {
        //         return $this->redirect('/site/wrong');
        //     }
        //     return $this->render('hxWeixin', compact('info'));
        // }
        // $this->layout = 'empty';
        // $this->view->title = config('web_name') . '_安全支付';
        // $amount = post('amount', '0.01');
        // // $amount = 0.01;
        // if ($amount < 10) {
        //     $this->redirect('/site/wrong');
        // }
        // //环迅支付
        // if (post('type', 3) == UserCharge::CHARGE_TYPE_HUAN) {
        //     $info = UserCharge::payExtend($amount);
        //     if (!$info) {
        //         return $this->redirect('/site/wrong');
        //     }
        //     return $this->render('extendPay', compact('info'));
        // } else {
        //     require Yii::getAlias('@vendor/WxPayPubHelper/WxPayPubHelper.php');
        //     //保存充值记录
        //     $userCharge = new UserCharge();
        //     $userCharge->user_id = u()->id;
        //     $userCharge->trade_no = u()->id . date("YmdHis") . rand(1000, 9999);
        //     $userCharge->amount = $amount;
        //     $userCharge->charge_type = UserCharge::CHARGE_TYPE_WECHAT;
        //     $userCharge->charge_state = UserCharge::CHARGE_STATE_WAIT;
        //     if (!$userCharge->save()) {
        //         return false;
        //     }
        //     //微信生成订单
        //     $jsApi = new \JsApi_pub();
        //     $openid = u()->open_id;
        //     $unifiedOrder = new \UnifiedOrder_pub();
        //     $unifiedOrder->setParameter("openid", $openid);//商品描述
        //     $unifiedOrder->setParameter("body", "我的账户充值");//商品描述
        //     $unifiedOrder->setParameter("out_trade_no", $userCharge->trade_no);//商户订单号
        //     $unifiedOrder->setParameter("total_fee", $amount * 100);//总金额
        //     $unifiedOrder->setParameter("notify_url", "http://" . $_SERVER['HTTP_HOST'] . "/site/ajax-update-status");//通知地址
        //     $unifiedOrder->setParameter("trade_type", "JSAPI");//商品类型
        //     $prepay_id = $unifiedOrder->getPrepayId();
        //     $jsApi->setPrepayId($prepay_id);
        //     $jsApiParameters = $jsApi->getParameters();

        //     return $this->render('pay', compact('jsApiParameters', 'amount', 'userCharge'));
        // }
    // }

    public function actionPayMoney()
    {
        $this->view->title = '支付';
        
        return $this->render('payMoney');
    }

    public function actionPassword()
    {
        $this->view->title = '修改密码';

        $model = User::findOne(u('id'));
        $model->scenario = 'password';

        if ($model->load($_POST)) {
            if ($model->validate()) {
                $model->password = $model->newPassword;
                if ($model->hashPassword()->update()) {
                    return $this->redirect(['index']);
                } else {
                    return error($model);
                }
            } else {
                return error($model);
            }
        }

        return $this->render('password', compact('model'));
    }

    public function actionChangePhone()
    {
        $this->view->title = '修改手机号';

        $model = User::findOne(u('id'));
        $model->scenario = 'changePhone';

        if ($model->load($_POST)) {
            if ($model->validate()) {
                $model->username = $model->mobile;
                if ($model->update()) {
                    return $this->redirect(['user/index']);
                } else {
                    return error($model);
                }
            } else {
                return error($model);
            }
        }
        $model->mobile = null;
        return $this->render('changePhone', compact('model'));
    }

    public function actionLogout()
    {
        user()->logout(false);

        return $this->redirect('/site/index');
    }
}

        