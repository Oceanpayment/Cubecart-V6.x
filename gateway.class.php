<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 http://opensource.org/licenses/GPL-3.0
 */
class Gateway {
	private $_config;
	private $_module;
	private $_basket;

	public function __construct($module = false, $basket = false) {
		$this->_module	= $module;
		$this->_basket =& $GLOBALS['cart']->basket;
	}

	##################################################

	public function transfer() {
		
		$transfer	= array(
			'action'	=> (filter_var($this->_module['payment_page_url'], FILTER_VALIDATE_URL)) ? $this->_module['payment_page_url'] : 'https://secure.oceanpayment.com/gateway/service/pay',
			'method'	=> 'post',
			'target'	=> '_self',
			'submit'	=> 'auto',
		);
		return $transfer;
	}

	public function repeatVariables() {
		return false;
	}

	public function fixedVariables() {
		//获取产品做遍历处理
		$productSku        = "";
		$productName       = "";
		$productNum        = "";
		$productPrice      = "";
		if(isset($this->_basket['contents'])){
			$items = $this->_basket['contents'];
				foreach($items as $item){
					$productSku  .= isset($item['product_code'])?$productSku.= $item['product_code'].',':$productSku.= 'N/A,';
					$productName  .= $item['name'].',';
					$productNum   .= $item['quantity'].',';
					$productPrice .= $item['sale_price'].',';
				}
		}else{
			$items = '';
		}
		$productSku         = substr($productSku,0,-1);
		$productName        = substr($productName,0,-1);
		$productNum         = substr($productNum,0,-1);
		$productPrice       = substr($productPrice,0,-1);

		$hidden	= array(	
		//支付币种
        'order_currency'    => $GLOBALS['config']->get('config', 'default_currency'),
        //金额
        'order_amount'      => $this->_basket['total'],
        
        //账户号
        'account'           => $this->_module['account'],
        //终端号
        'terminal'          => $this->_module['terminal'],
        //securecode
        'securecode'        => $this->_module['securecode'],
        //支付方式
        'methods'           => 'Credit Card',
        //订单号
        'order_number'      => $this->_basket['cart_order_id'],
        //返回地址
        'backUrl'			=> $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=Oceanpayment-CreditCard',
        //服务器响应地址
        'noticeUrl'			=> $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=call&module=Oceanpayment-CreditCard',
        //备注
        'order_notes'       => '',
        //账单人名
        'billing_firstName' => $this->_basket['billing_address']['first_name'],
        //账单人姓
        'billing_lastName'  => $this->_basket['billing_address']['last_name'],
        //账单人email
        'billing_email'     => $this->_basket['billing_address']['email'],
        //账单人电话
        'billing_phone'     => str_replace( array( '(', '-', ' ', ')', '.' ), '', $this->_basket['billing_address']['phone'] ),
        //账单人国家
        'billing_country'   => $this->_basket['billing_address']['country_iso'],
        //账单人州(可不提交)
        'billing_state'     => $this->_basket['billing_address']['state'],
        //账单人城市
        'billing_city'      => $this->_basket['billing_address']['town'],
        //账单人地址
        'billing_address'   => $this->_basket['billing_address']['line1'].$this->_basket['billing_address']['line2'],
        //账单人邮编
        'billing_zip'       => $this->_basket['billing_address']['postcode'],
        //产品名称
        'productName'       => $productName,
        //产品数量
        'productPrice'        => $productNum,
		//产品单价
        'productSku'        => $productPrice,
        //产品sku
        'productSku'        => $productSku,
        //收货人的名
        'ship_firstName'	   => $this->_basket['delivery_address']['first_name'],
        //收货人的姓
        'ship_lastName' 	   => $this->_basket['delivery_address']['last_name'],
        //收货人的电话
        'ship_phone' 	       => str_replace( array( '(', '-', ' ', ')', '.' ), '', $this->_basket['billing_address']['phone'] ),
        //收货人的国家
        'ship_country' 	   => $this->_basket['delivery_address']['country_iso'],
        //收货人的州（省、郡）
        'ship_state' 	   => $this->_basket['delivery_address']['state'],
        //收货人的城市
        'ship_city' 		   => $this->_basket['delivery_address']['town'],
        //收货人的详细地址
        'ship_addr' 		   => $this->_basket['delivery_address']['line1'].$this->_basket['delivery_address']['line2'],
        //收货人的邮编
        'ship_zip' 		   => $this->_basket['delivery_address']['postcode'],

        //支付页面样式
        'pages'			=> $this->isMobile() ? 1 : 0,
        //网店程序类型
        'isMobile'						=> $this->isMobile() ? 'Mobile' : 'PC',
        'cart_info'			=> 'cubecart|V1.0.0|'.$this->isMobile(),
        //接口版本
        'cart_api'          => 'V1.0',
        //sha256加密结果
        'signValue'         => hash("sha256",$this->_module['account'].$this->_module['terminal'].$GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=Oceanpayment-CreditCard'.$this->_basket['cart_order_id'].$GLOBALS['config']->get('config', 'default_currency').$this->_basket['total'].$this->_basket['billing_address']['first_name'].$this->_basket['billing_address']['last_name'].$this->_basket['billing_address']['email'].$this->_module['securecode'])

		);
		return (isset($hidden)) ? $hidden : false;
	}

    /**
     * 检验是否移动端
     */
    public function isMobile(){
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA'])){
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        // 判断手机发送的客户端标志
        if (isset ($_SERVER['HTTP_USER_AGENT'])){
            $clientkeywords = array (
                'nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel',
                'lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm',
                'operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'
            );
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))){
                return true;
            }
        }
        // 判断协议
        if (isset ($_SERVER['HTTP_ACCEPT'])){
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))){
                return true;
            }
        }
        return false;
    }
    
	##################################################

	public function call() {
		
		//获取推送输入流XML
        $xml_str = file_get_contents("php://input");

        //判断返回的输入流是否为xml
        if($this->xml_parser($xml_str)){
            $xml = simplexml_load_string($xml_str);

            //把推送参数赋值到$_REQUEST
            $_REQUEST['response_type']		= (string)$xml->response_type;
            $_REQUEST['account']			= (string)$xml->account;
            $_REQUEST['terminal']			= (string)$xml->terminal;
            $_REQUEST['payment_id']			= (string)$xml->payment_id;
            $_REQUEST['order_number']		= (string)$xml->order_number;
            $_REQUEST['order_currency']		= (string)$xml->order_currency;
            $_REQUEST['order_amount']		= (string)$xml->order_amount;
            $_REQUEST['payment_status']		= (string)$xml->payment_status;
            $_REQUEST['payment_details']	= (string)$xml->payment_details;
            $_REQUEST['signValue']			= (string)$xml->signValue;
            $_REQUEST['order_notes']		= (string)$xml->order_notes;
            $_REQUEST['card_number']		= (string)$xml->card_number;
            $_REQUEST['card_type']			= (string)$xml->card_type;
            $_REQUEST['card_country']		= (string)$xml->card_country;
            $_REQUEST['payment_authType']	= (string)$xml->payment_authType;
            $_REQUEST['payment_risk']		= (string)$xml->payment_risk;
            $_REQUEST['methods']			= (string)$xml->methods;
            $_REQUEST['payment_country']	= (string)$xml->payment_country;
            $_REQUEST['payment_solutions']	= (string)$xml->payment_solutions;

            //用于支付结果页面显示响应代码
            $getErrorCode		= explode(':', $_REQUEST['payment_details']);
            $errorCode			= $getErrorCode[0];

			$cart_order_id = sanitizeVar($_REQUEST['order_number']); // Used in remote.php $cart_order_id is important for failed orders
			$order				= Order::getInstance();
			$order_summary		= $order->getSummary($cart_order_id);

            $local_signValue  = hash("sha256",$_REQUEST['account'].$_REQUEST['terminal'].$_REQUEST['order_number'].$_REQUEST['order_currency'].$_REQUEST['order_amount'].$_REQUEST['order_notes'].$_REQUEST['card_number'].
                $_REQUEST['payment_id'].$_REQUEST['payment_authType'].$_REQUEST['payment_status'].$_REQUEST['payment_details'].$_REQUEST['payment_risk'].$this->_module['securecode']);

            if($_REQUEST['response_type'] == 1){

                //加密校验
                if(strtoupper($local_signValue) == strtoupper($_REQUEST['signValue'])){

                    //支付状态
                    if ($_REQUEST['payment_status'] == 1) {
                        //成功
                        $transData['status'] = "Successful";
                    } elseif ($_REQUEST['payment_status'] == -1) {
                        //待处理
                        $transData['status'] = "Successful";
                    } elseif ($_REQUEST['payment_status'] == 0) {
                        //失败
						$transData['status'] = "Failed";
                        //是否点击浏览器后退造成订单号重复 20061
                        if($errorCode == '20061'){
                            
                        }else{
                            
                        }

                    }

					
					$transData['customer_id'] 	= $order_summary["customer_id"];
					$transData['gateway'] 		= "Oceanpayment-CreditCard";
					$transData['trans_id'] 		= $_REQUEST['payment_id'];
					$transData['amount'] 		= $_REQUEST['order_amount'];
					$transData['order_id']		= $_REQUEST['order_number'];
				
					
					$transData['notes'] = $_REQUEST['payment_details'];
					$order->logTransaction($transData);
					
					

                }else{
                    
                }


                echo "receive-ok";
            }
        }
        exit;
	
	}

	public function process() {
		
		//返回账户
        $account          = $_REQUEST['account'];
        //返回终端号
        $terminal         = $_REQUEST['terminal'];
		//本地secureCode
		$secureCode       = $this->_module['securecode'];
        //返回Oceanpayment 的支付唯一号
        $payment_id       = $_REQUEST['payment_id'];
        //返回网站订单号
        $order_number     = $_REQUEST['order_number'];
        //返回交易币种
        $order_currency   = $_REQUEST['order_currency'];
        //返回支付金额
        $order_amount     = $_REQUEST['order_amount'];
        //返回支付状态
        $payment_status   = $_REQUEST['payment_status'];
        //返回支付详情
        $payment_details  = $_REQUEST['payment_details'];

        //用于支付结果页面显示响应代码
        $getErrorCode		= explode(':', $payment_details);
        $errorCode			= $getErrorCode[0];

        //返回交易安全签名
        $back_signValue   = $_REQUEST['signValue'];
        //返回备注
        $order_notes      = $_REQUEST['order_notes'];
        //未通过的风控规则
        $payment_risk     = $_REQUEST['payment_risk'];
        //返回支付信用卡卡号
        $card_number      = $_REQUEST['card_number'];
        //返回交易类型
        $payment_authType = $_REQUEST['payment_authType'];
        //解决方案
        $payment_solutions = $_REQUEST['payment_solutions'];

        //SHA256加密
        $local_signValue = hash("sha256",$account.$terminal.$order_number.$order_currency.$order_amount.$order_notes.$card_number.
            $payment_id.$payment_authType.$payment_status.$payment_details.$payment_risk.$secureCode);

        //加密校验
        if(strtoupper($local_signValue) == strtoupper($back_signValue)){

            $order			= Order::getInstance();
            //支付状态
            if ($payment_status == 1) {
                //成功
                $order->orderStatus(Order::ORDER_PROCESS, $order_number);
				$order->paymentStatus(Order::PAYMENT_SUCCESS, $order_number);
            } elseif ($payment_status == -1) {
                //待处理
                $order->orderStatus(Order::ORDER_PENDING, $order_number);
                $order->paymentStatus(Order::PAYMENT_PENDING, $order_number);
            } elseif ($payment_status == 0) {
				$order->orderStatus(Order::ORDER_CANCELLED, $order_number);
                $order->paymentStatus(Order::PAYMENT_CANCEL, $order_number);

            }

            $order_summary	= $order->getSummary(sanitizeVar($order_number));

            //页面跳转
            if ($order_summary['status'] == Order::ORDER_PROCESS || $order_summary['status'] == Order::ORDER_PENDING) {
                httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
            } else {
                $GLOBALS['gui']->setError($payment_details);
                httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'confirm')));
            }
        }else{

        }
		
        
	}

	public function form() {
		return false;
	}

	/**
     *  判断是否为xml
     */
    function xml_parser($str){
        $xml_parser = xml_parser_create();
        if(!xml_parse($xml_parser,$str,true)){
            xml_parser_free($xml_parser);
            return false;
        }else {
            return true;
        }
    }

}