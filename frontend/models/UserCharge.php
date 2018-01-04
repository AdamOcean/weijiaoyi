<?php

namespace frontend\models;

use Yii;

class UserCharge extends \common\models\UserCharge
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            // [['field1', 'field2'], 'required', 'message' => '{attribute} is required'],
        ]);
    }

    public function scenarios()
    {
        return array_merge(parent::scenarios(), [
            // 'scenario' => ['field1', 'field2'],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            // 'field1' => 'description1',
            // 'field2' => 'description2',
        ]);
    }

    //易支付银行卡绑定
    public static function epayBankCard($bankCard)
    {
        // test($bankCard->bank_name);
        $data['ORDER_ID'] = u()->id . date("YmdHis");
        $data['ORDER_TIME'] = date("YmdHis");
        $data['USER_TYPE'] = '02';
        $data['USER_ID'] = EXCHANGE_ID;
        $data['SIGN_TYPE'] = '03';
        $data['BUS_CODE'] = '1011';
        $data['CHECK_TYPE'] = '01';
        $data['ACCT_NO'] = $bankCard->bank_card;  // 卡号
        $data['PHONE_NO'] = $bankCard->bank_mobile; //  手机号
        $data['ID_NO'] = $bankCard->id_card;

        $string = '';
        foreach($data as $key => $v) {
            $string .= "{$key}={$v}&";
        }
        $signSource = $string . EXCHANGE_MDKEY;
        // tes($signSource);
        $mdStr = strtoupper(md5($signSource)); //加密算法第一步大写
        $data['SIGN'] = strtoupper(substr(md5($mdStr . EXCHANGE_MDKEY), 8, 16)); //16位的md5
        $data['NAME'] = $bankCard->bank_user; // 姓名
        $value = '';
        foreach($data as $key => $v) {
            $value .= "{$key}={$v}&";
        }
        $value = substr($value, 0, strlen($value)-1);
        // tes($data, $value);
        // $url = 'http://163.177.40.37:8888/NPS-API/controller/pay';
        $url = 'http://npspay.yiyoupay.net/NPS-API/controller/pay';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $value);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        // test($result);
        $str = "<RESP_CODE>0000</RESP_CODE>";
        if(strpos($result,$str)) {
            return true;
        }else {
            return false;
        }
    }

    //云托付
    public static function payYtfchange($amount, $pay_type = "1004")
    {
        //保存充值记录
        $userCharge = new UserCharge();
        $userCharge->user_id = u()->id;
        $userCharge->trade_no = u()->id . date("YmdHis") . rand(1000, 9999);
        $userCharge->amount = $amount;
        $userCharge->charge_type = UserCharge::CHARGE_TYPE_BANKWECHART;
        if($pay_type == '992') {
            $userCharge->charge_type = UserCharge::CHARGE_TYPE_ALIPAY;
            // $amount = 1;
        }
        $userCharge->charge_state = UserCharge::CHARGE_STATE_WAIT;
        if (!$userCharge->save()) {
            return false;
        }
        $url = 'http://pay.yuntuofu.cc/Bank/';
        $data['parter'] = EXCHANGE_ID;
        $data['type'] = $pay_type;
        $data['value'] = $amount;
        $data['orderid'] = $userCharge->trade_no;
        $data['callbackurl'] = url(['site/tynotify'], true);;
        $string = '';
        foreach($data as $key => $v) {
            $string .= "{$key}={$v}&";
        }
        $data['url'] = trim($string, '&') . EXCHANGE_MDKEY;
        $sign = md5($data['url']); 
        $data['sign'] = $sign;
        $data['hrefbackurl'] = url(['site/index'], true);
        return $data;
    }

    //第三方支付 银联支付
    public static function payExtend($amount, $user_id)
    {
        //保存充值记录
        $UserCharge = new UserCharge();
        $UserCharge->user_id = $user_id;
        $UserCharge->trade_no = $user_id . date("YmdHis");
        $UserCharge->amount = $amount;
        $UserCharge->charge_type = UserCharge::CHARGE_TYPE_HUAN;
        $UserCharge->charge_state = UserCharge::CHARGE_STATE_WAIT;
    
        if (!$UserCharge->save()) {
            return false;
        }
        if (0 && System::isMobile()) {
            $url = 'https://mobilegw.ips.com.cn/psfp-mgw/paymenth5.do';
        } else {
            $url = 'https://newpay.ips.com.cn/psfp-entry/gateway/payment.do';
        }
        $MerCode = HX_ID;
        $Account = HX_TID;
        $mercert = HX_MERCERT;
        $MerBillNo = $UserCharge->trade_no;
        $Amount = YII_DEBUG ? '0.01' : $UserCharge->amount . '.00';
        $Date = date('Ymd');
        $GatewayType = '01'; //借记卡：01，信用卡02，IPS账户支付03
        $Merchanturl = WEB_DOMAIN;
        $ServerUrl = WEB_DOMAIN . '/site/notify';// 支付成功回调
        $GoodsName = config('web_name') . '_用户充值';
        $MsgId = 'm'. $MerBillNo;
        $ReqDate = date('Ymdhis');

        $ips = '<Ips><GateWayReq>';
        $body = "<body><MerBillNo>{$MerBillNo}</MerBillNo><Amount>{$Amount}</Amount><Date>{$Date}</Date><CurrencyType>156</CurrencyType ><GatewayType>{$GatewayType}</GatewayType><Lang>GB</Lang><Merchanturl>{$Merchanturl}</Merchanturl><FailUrl></FailUrl><Attach></Attach><OrderEncodeType>5</OrderEncodeType><RetEncodeType>17</RetEncodeType><RetType>1</RetType><ServerUrl>{$ServerUrl}</ServerUrl><BillEXP>1</BillEXP><GoodsName>{$GoodsName}</GoodsName><IsCredit>0</IsCredit><BankCode></BankCode><ProductType>0</ProductType></body>";
        $Signature = md5($body . $MerCode . $mercert);
        $head = "<head><Version>v1.0.0</Version><MerCode>{$MerCode}</MerCode><MerName></MerName><Account>{$Account}</Account><MsgId>{$MsgId}</MsgId><ReqDate>{$ReqDate}</ReqDate><Signature>{$Signature}</Signature></head>";
        $ips .= $head;
        $ips .= $body;
        $ips .= '</GateWayReq></Ips>';
        return ['url' => $url, 'content' => $ips];
        // return $this->render('pay', compact('webAction', 'ips'));
    }
    // 微信支付
    public static function payHxWxpay($amount, $userId)
    {

        //保存充值记录
        $userCharge = new UserCharge(); 
        $userCharge->user_id = $userId;
        $userCharge->trade_no = $userId . date("YmdHis") . rand(1000, 9999);
        $userCharge->amount = $amount;
        $userCharge->charge_type = UserCharge::CHARGE_TYPE_HUAN;
        $userCharge->charge_state = UserCharge::CHARGE_STATE_WAIT;
        if (!$userCharge->save()) {
            return false;
        }  
        $pVersion = 'v1.0.0';//版本号
        $pMerCode = HX_ID;
        $pAccount = HX_TID;
        $pMerCert = HX_MERCERT;
        $pMerName = 'pay';//商户名
        $pMsgId = "msg" . rand(1000, 9999);//消息编号
        $pReqDate = date("Ymdhis");//商户请求时间
        $pMerBillNo = $userCharge->trade_no;//商户订单号
        $pGoodsName = "recharge";//商品名称
        $pGoodsCount = "";
        $pOrdAmt = $userCharge->amount;//订单金额 
        // $pOrdAmt = 0.01;
        $pOrdTime =date("Y-m-d H:i:s");

        $pMerchantUrl = WEB_DOMAIN;
        $pServerUrl = WEB_DOMAIN . '/site/hx-weixin';
        // $pServerUrl = 'http://pay.szsqldjhkjb.top/site/notify';// 支付成功回调
        $pBillEXP="";
        $pReachBy="";
        $pReachAddress="";
        $pCurrencyType="156";
        $pAttach = '用户充值';
        $pRetEncodeType="17";

        $strbodyxml= "<body>"
              ."<MerBillno>".$pMerBillNo."</MerBillno>"
              ."<GoodsInfo>"
              ."<GoodsName>".$pGoodsName."</GoodsName>"
              ."<GoodsCount >".$pGoodsCount."</GoodsCount>"
              ."</GoodsInfo>"
              ."<OrdAmt>".$pOrdAmt."</OrdAmt>"
              ."<OrdTime>".$pOrdTime."</OrdTime>"
              ."<MerchantUrl>".$pMerchantUrl."</MerchantUrl>"
              ."<ServerUrl>".$pServerUrl."</ServerUrl>"
              ."<BillEXP>".$pBillEXP."</BillEXP>"
              ."<ReachBy>".$pReachBy."</ReachBy>"
              ."<ReachAddress>".$pReachAddress."</ReachAddress>"
              ."<CurrencyType>".$pCurrencyType."</CurrencyType>"
              ."<Attach>".$pAttach."</Attach>"
              ."<RetEncodeType>".$pRetEncodeType."</RetEncodeType>"
              ."</body>";
        $Sign = $strbodyxml . $pMerCode . $pMerCert;//签名明文

        $pSignature = md5($strbodyxml.$pMerCode.$pMerCert);//数字签名 
        //请求报文的消息头
        $strheaderxml= "<head>"
               ."<Version>".$pVersion."</Version>"
               ."<MerCode>".$pMerCode."</MerCode>"
               ."<MerName>".$pMerName."</MerName>"
               ."<Account>".$pAccount."</Account>"
               ."<MsgId>".$pMsgId."</MsgId>"
               ."<ReqDate>".$pReqDate."</ReqDate>"
               ."<Signature>".$pSignature."</Signature>"
            ."</head>";

        //提交给网关的报文
        $strsubmitxml =  "<Ips>"
            ."<WxPayReq>"
            .$strheaderxml
            .$strbodyxml
          ."</WxPayReq>"
          ."</Ips>";
          
        $payLinks= '<form style="text-align:center;" action="https://thumbpay.e-years.com/psfp-webscan/onlinePay.do" target="_self" style="margin:0px;padding:0px" method="post" name="ips" >';
        $payLinks  .= "<input type='hidden' name='wxPayReq' value='$strsubmitxml' />";
        $payLinks .= "<input class='btn' type='submit' value='确认支付'></form><script>document.ips2.submit();</script>";
        return ['userCharge' => $userCharge, 'payLinks' => $payLinks];
    }

    //中云第三方支付 ShaoBeiZfb
    public static function payExchange($amount, $acquirer_type = 'WXZF', $tongdao = 'WftWx')
    {
        //保存充值记录
        $userCharge = new UserCharge();
        $userCharge->user_id = u()->id;
        $userCharge->trade_no = u()->id . date("YmdHis") . rand(1000, 9999);
        $userCharge->amount = $amount;
        $userCharge->charge_state = self::CHARGE_STATE_WAIT;
        if ($acquirer_type == 'alipay') {
            $userCharge->charge_type = self::CHARGE_TYPE_ALIPAY;
        }
        if (!$userCharge->save()) {
            return false;
        }
        // test(u()->id);
        // 微信、支付宝交易
        $url = 'http://zy.cnzypay.com/Pay_Index.html';

        $data['pay_memberid'] = ZYPAY_ID; //商户id
        $data['pay_orderid'] = $userCharge->trade_no;
        $data['pay_amount'] = $amount;
        $data['pay_applydate'] = self::$time; //请求时间
        $data['pay_bankcode'] = $acquirer_type; //银行编号
        $data['pay_notifyurl'] = url(['site/notify'], true); //异步回调地址  融智付异步商户url
        $data['pay_callbackurl'] = url(['site/index'], true); //页面返回地址
        // 商户id、应用id、商户订单号、订单金额、加密key
        $string = '';
        ksort($data);
        reset($data);
        foreach($data as $key => $v) {
            $string .= "{$key}=>{$v}&";
        }
        $string .= "key=" . ZYPAY_KEY;
        $data['tongdao'] = $tongdao;
        $data['pay_md5sign'] = strtoupper(md5($string));
        if ($tongdao == 'Gopaywap') {
            $str = '<form id="Form1" name="Form1" method="post" action="' . $url . '">';
            foreach ($data as $key => $val) {
                $str = $str . '<input type="hidden" name="' . $key . '" value="' . $val . '">';
            }
            $str = $str . '<input type="hidden" value="提交">';
            $str = $str . '</form>';
            $str = $str . '<script>';
            $str = $str . 'document.Form1.submit();';
            $str = $str . '</script>';
            return $str;
        }
        $result = httpRequest($url, $data);
        preg_match('/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i', $result, $match);
        if (isset($match[2])) {
            return 'http://zy.cnzypay.com/' . $match[2];
        }
        return false;
    }   
	
	    //j云支付
    public static function payRxchange($amount, $pay_type = "1004")
    {
        //保存充值记录
        $userCharge = new UserCharge();
        $userCharge->user_id = u()->id;
        $userCharge->trade_no = u()->id . date("YmdHis") . rand(1000, 9999);
        $userCharge->amount = $amount;
        $userCharge->charge_type = UserCharge::CHARGE_TYPE_BANKWECHART;
        if($pay_type == '30004') {
            $userCharge->charge_type = UserCharge::CHARGE_TYPE_ALIPAY;
            // $amount = 1;
        }
        $userCharge->charge_state = UserCharge::CHARGE_STATE_WAIT;
        if (!$userCharge->save()) {
            return false;
        }
        $data['parter'] = EXCHANGE_ID;
        $data['bank'] = (int)$pay_type;
        $data['value'] = $amount;
        $data['orderid'] = $userCharge->trade_no;
        $data['callbackurl'] = url(['site/tynotify'], true);
        $string = '';
        foreach($data as $key => $v) {
            $string .= "{$key}={$v}&";
        }
        $data['url'] = trim($string, '&') . EXCHANGE_MDKEY;
        $sign = md5($data['url']); 
		$data['urll'] = 'http://api.ecoopay.com/Bank/index.aspx';
        $data['sign'] = $sign;
        $data['hrefbackurl'] = url(['site/index'], true);
        return $data;
    } 

    public static function payWwchange($amount, $pay_type = "1004")
    {
        //保存充值记录
        $userCharge = new UserCharge();
        $userCharge->user_id = u()->id;
        $userCharge->trade_no = u()->id . date("YmdHis") . rand(1000, 9999);
        $userCharge->amount = $amount;
        $userCharge->charge_type = UserCharge::CHARGE_TYPE_BANKWECHART;
        $userCharge->charge_state = UserCharge::CHARGE_STATE_WAIT;
        
        if (!$userCharge->save()) {
            return false;
        }

        $submiturl = 'http://pay.woonet.com.cn/interface/chargebank.aspx';  //网关地址
        
        $key = WW_KEY;//用户key
        $parter = WW_ID;//用户ID

        $orderid =  $userCharge->trade_no;//用户订单号（必须唯一）
        $value =  $amount;//订单金额
        $type =  $pay_type;//银行ID（见文档）
        $callbackurl =  url(['site/wwnotify'], true);//同步接收返回URL连接
        $hrefbackurl =  url(['site/wwnotify'], true);//异步接收返回URL连接
        $attach = "chongzhi";//备注 如有中文 urlencode编码
        $sign = "parter=".$parter."&type=".$type."&orderid=".$orderid."&callbackurl=".$callbackurl;
        $sign = md5($sign.$key);//签名数据 32位小写的组合加密验证串
        $url=$submiturl."?parter=".$parter."&type=".$type."&value=".$value."&orderid=".$orderid."&callbackurl=".$callbackurl."&attach=".$attach."&hrefbackurl=".$hrefbackurl."&sign=".$sign;

        header('Location:'.$url);
        exit();
    }

    //wanmfu 微信扫码 
     public static function payWamfuwxsm($amount)
    {
        //保存充值记录
        $userCharge = new UserCharge();
        $userCharge->user_id = u()->id;
        $trade_no =  u()->id . date("YmdHis") . rand(1000, 9999);
        $userCharge->trade_no =$trade_no;
        $userCharge->amount = $amount;
        $userCharge->charge_type = UserCharge::CHARGE_TYPE_BANKWECHART;
        $userCharge->charge_state = UserCharge::CHARGE_STATE_WAIT;
        
        if (!$userCharge->save()) {
            return false;
        }

        $url = 'http://api.wamfu.com/PaySDK/WXAPI/ReadyPayQR';  //网关地址
        
        $data['MerId'] = '0000000528';
        $data['AppId'] = '0000001766';
        $data['NonceStr'] = rand(100000, 999999);
        $data['Body'] = 'saoma';
        $data["ExpireSecond"] = 600;
        $data['OrderId'] = $userCharge->trade_no;
        $data['TotalFee'] = (int)$amount*100;
        $data['NotifyUrl'] = url(['site/wamfunotify'], true);

        ksort($data);
        $paramstring = "";
        foreach($data as $key => $value)
        {
            if(strlen($paramstring) == 0)
                $paramstring .= $key . "=" . $value;
            else
                $paramstring .= "&" . $key . "=" . $value;
        }
        
        $paramstring = $paramstring."&key=fa4d8d6a9627b45b36072588ffd73522";
        
        $paySign = strtoupper(md5($paramstring));
        $data['Sign'] = $paySign;

        $returValue = '<xml>';
        foreach($data as $key => $value)
        {
            $returValue .= "<$key>".$value."</$key>";
        
        }
        $returValue .= '</xml>';
        $data['xmlData'] = $returValue;
        $result = httpRequest($url, $data);
        if(json_decode($result,true)['status'] != 1){
            echo $result;die;
        }
        return $result;
    }
     //wanmfu 微信h5
     public static function payWamfuhf($amount)
    {
        //保存充值记录
        $userCharge = new UserCharge();
        $userCharge->user_id = u()->id;
        $trade_no =  u()->id . date("YmdHis") . rand(1000, 9999);
        $userCharge->trade_no =$trade_no;
        $userCharge->amount = $amount;
        $userCharge->charge_type = UserCharge::CHARGE_TYPE_BANKWECHART;
        $userCharge->charge_state = UserCharge::CHARGE_STATE_WAIT;
        
        if (!$userCharge->save()) {
            return false;
        }

        $url = 'http://api.wamfu.com/PaySDK/WXAPI/ReadyPayJS';  //网关地址
        
        $data['MerId'] = '0000000528';
        $data['AppId'] = '0000001766';
        $data['NonceStr'] = rand(100000, 999999);
        $data['Body'] = 'h5zhifu';
        $data["ExpireSecond"] = 600;
        $data['OrderId'] = $userCharge->trade_no;
        $data['TotalFee'] = (int)$amount*100;
        $data['NotifyUrl'] = url(['site/wamfunotify'], true);
        $data['ReturnUrl'] = url(['user/index'], true);

        ksort($data);
        $paramstring = "";
        foreach($data as $key => $value)
        {
            if(strlen($paramstring) == 0)
                $paramstring .= $key . "=" . $value;
            else
                $paramstring .= "&" . $key . "=" . $value;
        }
        
        $paramstring = $paramstring."&key=fa4d8d6a9627b45b36072588ffd73522";
        
        $paySign = strtoupper(md5($paramstring));
        $data['Sign'] = $paySign;

        $returValue = '<xml>';
        foreach($data as $key => $value)
        {
            $returValue .= "<$key>".$value."</$key>";
        
        }
        $returValue .= '</xml>';
        $data['xmlData'] = $returValue;

        $result = httpRequest($url, $data);
        if(json_decode($result,true)['status'] != 1){
            echo $result;die;
        }
        $url = 'http://v.wamfu.com/PaySDK/WXAPI/ReadyPayPage/'.json_decode($result,true)['data'];
        header('Location:'.$url);
        exit();
    }
     //wanmfu 支付宝
     public static function payWamfuzfb($amount)
    {
        //保存充值记录
        $userCharge = new UserCharge();
        $userCharge->user_id = u()->id;
        $trade_no =  u()->id . date("YmdHis") . rand(1000, 9999);
        $userCharge->trade_no =$trade_no;
        $userCharge->amount = $amount;
        $userCharge->charge_type = UserCharge::CHARGE_TYPE_BANKWECHART;
        $userCharge->charge_state = UserCharge::CHARGE_STATE_WAIT;
        
        if (!$userCharge->save()) {
            return false;
        }

        $url = 'http://api.wamfu.com/PaySDK/ZFBAPI/ReadyPayQR';  //网关地址
        
        $data['MerId'] = '0000000528';
        $data['AppId'] = '0000001766';
        $data['NonceStr'] = rand(100000, 999999);
        $data['Body'] = 'h5zhifu';
        $data["ExpireSecond"] = 600;
        $data['OrderId'] = $userCharge->trade_no;
        $data['TotalFee'] = (int)$amount*100;
        $data['NotifyUrl'] = url(['site/wamfunotify'], true);

        ksort($data);
        $paramstring = "";
        foreach($data as $key => $value)
        {
            if(strlen($paramstring) == 0)
                $paramstring .= $key . "=" . $value;
            else
                $paramstring .= "&" . $key . "=" . $value;
        }
        
        $paramstring = $paramstring."&key=fa4d8d6a9627b45b36072588ffd73522";
        
        $paySign = strtoupper(md5($paramstring));
        $data['Sign'] = $paySign;

        $returValue = '<xml>';
        foreach($data as $key => $value)
        {
            $returValue .= "<$key>".$value."</$key>";
        
        }
        $returValue .= '</xml>';
        $data['xmlData'] = $returValue;

        $result = httpRequest($url, $data);
        if(json_decode($result,true)['status'] != 1){
            echo $result;die;
        }
        return $result;
    }


}

        