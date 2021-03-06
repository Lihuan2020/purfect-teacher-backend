<?php
/**
 * Created by PhpStorm.
 * User: zhang.kui
 * Date: 19/11/19
 * Time: 11:33 AM
 */
namespace App\Http\Controllers\Api\Wifi;

use Exception;
use App\Models\Wifi\Backstage\WifiOrders;
use Illuminate\Support\Facades\DB;
use Psy\Util\Json;
use App\Utils\JsonBuilder;
use Illuminate\Support\Facades\Cache; // 缓存
use App\Http\Requests\Api\Wifi\WifiPayRequest;
use App\Http\Controllers\Controller;

use App\Dao\Wifi\Api\WifisDao; // wifi 产品
use App\Dao\Wifi\Api\WifiContentsDao; // 常用须知
use App\Dao\Wifi\Api\WifiUserTimesDao; // 用户wifi和套餐时长
use App\Dao\Wifi\Api\WifiOrdersDao; // wifi订单

use App\Dao\Wifi\Api\WifiConfigsDao; // wifi 配置
use App\Dao\Wifi\Api\WifiIssuesDao; // 报修单
use App\Dao\Wifi\Api\WifiUserAgreementsDao; // wifi协议

use App\BusinessLogic\WifiInterface\Factory;

use Yansongda\Pay\Pay;
use Yansongda\Pay\Log;
use App\Jobs\WifiAsynsPodcast;

class WifiPayController extends Controller
{
   /**
    * Func 充值上网
    * @param Request $request
    * @return Json
    */
   public function index_recharge(WifiPayRequest $request)
   {
      $user = $request->user ();

      if ( ! intval ( $user->gradeUser->campus_id ) )
      {
         return JsonBuilder::Error ( '参数错误' );
      }

      // wifi产品列表
      $condition[] = [ 'campus_id' , '=' , $user->gradeUser->campus_id ];
      $condition[] = [ 'mode' , '=' , 1 ];
      $condition[] = [ 'status' , '=' , 1 ];

      $infos[ 'wifiList' ] = WifisDao::getWifisListInfo (

         $condition , [ 'wifi_sort' , 'asc' ] , [ 'page' => 1 , 'limit' => 10 ] ,

         [ 'wifiid' , 'wifi_name' , 'wifi_oprice' , 'wifi_price' ]

      )->toArray ()[ 'data' ];

      // 获取通知信息
      $condition1[] = [ 'campus_id' , '=' , $user->gradeUser->campus_id ];
      $condition1[] = [ 'typeid' , '=' , 3 ];
      $condition1[] = [ 'status' , '=' , 1 ];

      $getWifiContentsOneInfo = WifiContentsDao::getWifiContentsOneInfo (
         $condition1 , [ 'contentid' , 'desc' ] , [ 'typeid' , 'content' ]
      );
      $infos[ 'wifi_notice' ] = '';
      if ( $getWifiContentsOneInfo && $getWifiContentsOneInfo->content )
      {
         $infos[ 'wifi_notice' ] = (String)$getWifiContentsOneInfo->content;
      }

      // 获取wifi时长
      $condition2[] = [ 'user_id' , '=' , $user->id ];
      $condition2[] = [ 'status' , '=' , 1 ];
      $getWifiUserTimesOneInfo = WifiUserTimesDao::getWifiUserTimesOneInfo (
         $condition2 , [ 'timesid' , 'desc' ] , [ 'user_wifi_time' ]
      );

      // Wifi 时长
      $user_wifi_time  = isset( $getWifiUserTimesOneInfo->user_wifi_time ) ? strtotime ( $getWifiUserTimesOneInfo->user_wifi_time ) : 0;
      $getUserWifiInfo = WifisDao::getUserWifiInfo ( $user_wifi_time );
      $infos[ 'wifi_date' ]      = $getUserWifiInfo[ 'wifi_date' ];
      $infos[ 'wifi_days' ]      = $getUserWifiInfo[ 'wifi_days' ];
      $infos[ 'user_wifi_time' ] = $getUserWifiInfo[ 'wifi_time' ];
      return JsonBuilder::Success ( $infos , 'wifi无线产品列表' );
   }

   /**
    * Func
    * @param Request $request
    * @return Json
    */
   public function pay_recharge_info(WifiPayRequest $request)
   {
      $user = $request->user ();
      $param = $request->only ( [ 'paymentid' ,'wifiid','number'] );

      // 验证wifi产品
      $condition[] = [ 'wifiid' , '=' , $param['wifiid'] ];
      $condition[] = [ 'mode' , '=' , 1 ];
      $condition[] = [ 'status' , '=' , 1 ];
      $getWifisOneInfo = WifisDao::getWifisOneInfo ( $condition , [ 'wifiid' , 'asc' ] , [ '*' ] );
      if(empty($getWifisOneInfo))  return JsonBuilder::Error ( '参数错误' );

      // 组装数据
      $param1[ 'type' ]           	   = 1;  // 类型(1:充值,2:赠送)
      $param1[ 'mode' ]           	   = 1;  // 类型(1:无线,2:有线)
      $param1[ 'user_id' ]           	= $user->id; // 用户id
      $param1[ 'trade_sn' ]         	= date('YmdHis').rand (100000,999999);
      $param1[ 'school_id' ]         	= $user->gradeUser->school_id;
      $param1[ 'campus_id' ]     	   = $user->gradeUser->campus_id;
      $param1[ 'wifi_id' ]          	= $getWifisOneInfo->wifiid;
      $param1[ 'wifi_type' ]        	= $getWifisOneInfo->wifi_type;
      $param1[ 'wifi_name' ]        	= $getWifisOneInfo->wifi_name;
      $param1[ 'order_days' ]       	= $getWifisOneInfo->wifi_days;
      $param1[ 'order_number' ]     	= isset($param['number']) ? intval($param['number']) : 1;  // 数量
      $param1[ 'order_unitprice' ]  	= $getWifisOneInfo->wifi_price;
      $param1[ 'order_totalprice' ] 	= $getWifisOneInfo->wifi_price * $param1[ 'order_number' ]; // wifi价格
      $param1[ 'paymentid' ]        	= $param['paymentid'];  // 支付方式
      $param1[ 'payment_name' ]     	= WifisDao::$paymentidArr[ $param1[ 'paymentid' ] ];

      // 验证数据是否重复提交
      $dataSign = sha1 ( $user->id . 'pay_recharge_info' );

      if ( Cache::has ( $dataSign ) ) return JsonBuilder::Error ( '您提交太快了，先歇息一下。' );

      DB::beginTransaction();

      if ( WifiOrders::addOrUpdateWifiOrdersInfo ($param1) )
      {
         // 微信支付
         if($param['paymentid'] == 1)
         {
            $order = [
               'out_trade_no' => $param1[ 'trade_sn' ],
               'total_fee' => $param1[ 'order_totalprice' ] * 100,
               'body' => '购买无线wifi['.$param1[ 'wifi_name' ].']',
            ];
            try {
               $configArr = config ( 'pay.wechat' );
               $configArr[ 'notify_url' ] = url ()->previous () . $configArr[ 'notify_url' ];
               $resultObj = Pay::wechat ( $configArr )->app ( $order );
               $infos = json_decode ( $resultObj->getContent () , true );
               if ( $resultObj->getStatusCode () == 200) {
                  DB::commit ();
               } else {
                  throw new Exception('订单生成失败,请稍后重试');
               }
            } catch (Exception $e) {
               DB::rollback();
               return JsonBuilder::Error ( $e->getMessage () );
            }
         }

         // 支付宝支付
         if($param['paymentid'] == 2)
         {
            $order = [
               'out_trade_no' => $param1[ 'trade_sn' ],
               'total_amount' => $param1[ 'order_totalprice' ],
               'subject' => '购买无线wifi['.$param1[ 'wifi_name' ].']',
            ];
            try{
               $configArr = config ( 'pay.alipay' );
               $configArr[ 'notify_url' ] = url ()->previous () . $configArr[ 'notify_url' ];
               $data = Pay::alipay ( $configArr )->app ( $order );
               if ( $data->getStatusCode () == 200 )
               {
                  $infos['pay_info'] = $data->getContent ();
                  DB::commit();
               } else {
                  throw new Exception('订单生成失败,请稍后重试');
               }
            } catch (Exception $e) {
               DB::rollback();
               return JsonBuilder::Error ( $e->getMessage () );
            }
         }
         // 生成重复提交签名
         Cache::put ( $dataSign , $dataSign , 10 );
         return JsonBuilder::Success ( $infos ,'购买无线wifi');
      } else {
         return JsonBuilder::Error ( '购买失败,请稍后重试' );
      }
   }

   /**
    * Func 充值记录
    * @param Request $request
    * @return Json
    */
   public function list_recharge_info(WifiPayRequest $request)
   {
      $user            = $request->user ();
      $param           = $request->only ( [ 'page' ] );
      $param[ 'page' ] = max ( 1 , intval ( $param[ 'page' ] ) );

      // 获取充值明细
      $condition[] = [ 'user_id' , '=' , $user->id ];
      // 状态(0:关闭,1:待支付,2:支付失败,3:支付成功-WIFI充值中,4:支付成功-WIFI充值成功,5:支付成功-充值失败,6:支付成功-退款成功)
      $condition[] = [ 'status' , '>' , 0 ];

      // 获取的字段
      $fieldArr = [
         'orderid' , 'wifi_name' , 'order_days' , 'order_number' , 'order_unitprice' ,
         'order_totalprice' , 'payment_name' , 'created_at' , 'pay_time' , 'succeed_time' ,
         'defeated_time' , 'refund_time' , 'cancle_time' , 'status' ,
      ];
      $infos    = WifiOrdersDao::getWifiOrdersListInfo (
         $condition , [ 'orderid' , 'desc' ] ,
         [ 'page' => $param['page'] , 'limit' => self::$api_wifi_page_limit ] ,
         $fieldArr
      )->toArray()['data'];

      return JsonBuilder::Success ( $infos,'wifi充值记录列表' );
   }

   /**
    * Func 检测是否在线
    * @param Request $request
    * @return Json
    */
   public function check_wifi_status_info(WifiPayRequest $request)
   {
      $user = $request->user ();

      // 获取wifi数据
      $condition[] = [ 'user_id' , '=' , $user->id ];
      $getWifiUserTimesOneInfo = WifiUserTimesDao::getWifiUserTimesOneInfo (
         $condition , [ 'timesid' , 'desc' ] , [ 'wif_psessionid' , 'user_wifi_time' ]
      );
      // wifi处于为连接状态
      $infos['status'] = 0;
      if($getWifiUserTimesOneInfo
         && $getWifiUserTimesOneInfo->wif_psessionid != ''
         && strtotime ($getWifiUserTimesOneInfo->user_wifi_time) > time())
      {
         $produce = Factory::produce(1);
         $checkAccountOnline = $produce->checkAccountOnline(
            ['psessionid'=>$getWifiUserTimesOneInfo->wif_psessionid]
         );
         $infos['status'] = $checkAccountOnline['status'] == true ? 1: 0;
      }
      // status 值为1 在线 0:不在线
      return JsonBuilder::Success ( $infos,'检测wifi状态');
   }


   /**
    * Func wifi上线
    * @param Request $request
    * @return Json
    */
   public function up_wifi_status_info(WifiPayRequest $request)
   {
      $user = $request->user ();

      // TODO.....验证参数
      $param = $request->only ( [ 'authType' , 'deviceMac', 'ssid', 'terminalIp', 'terminalMac' ] );

      // 获取wifi数据
      $condition[] = [ 'user_id' , '=' , $user->id ];
      $getWifiUserTimesOneInfo = WifiUserTimesDao::getWifiUserTimesOneInfo (
         $condition , [ 'timesid' , 'desc' ] , [ 'timesid','wif_psessionid','user_wifi_time' ]
      );
      // 未充值wifi,请先充值
      if($getWifiUserTimesOneInfo && strtotime ($getWifiUserTimesOneInfo->user_wifi_time) < time() )
      {
         return JsonBuilder::Error ( '请先购买wifi' );
      }
      // 华为上线
      $produce = Factory::produce(1);
      $param1['ssid']  =  $param['ssid'];
      $param1['authType']  =  $param['authType'];
      $param1['deviceMac']  =  $param['deviceMac'];
      $param1['terminalIp']  =  $param['terminalIp'];
      $param1['terminalMac']  =  $param['terminalMac'];
      $param1['userName']  =  $getWifiUserTimesOneInfo['user_wifi_name'];
      $param1['password']  =  $getWifiUserTimesOneInfo['user_wifi_password'];
      $AccountOnline = $produce->AccountOnline($param1);
      if($AccountOnline['status'] == true)
      {
         $saveData['wif_psessionid'] = (String)$AccountOnline['data']['psessionid'];
         WifiUserTimesDao::addOrUpdateWifiUserTimesInfo ($saveData,$getWifiUserTimesOneInfo['timesid']);
         return JsonBuilder::Success ( '上线成功' );
      } else {
         return JsonBuilder::Error ( '上线失败,请稍后重试' );
      }
   }

   /**
    * Func wifi下线
    * @param Request $request
    * @return Json
    */
   public function down_wifi_status_info(WifiPayRequest $request)
   {
      $user = $request->user ();

      // 获取wifi数据
      $condition[] = [ 'user_id' , '=' , $user->id ];
      $getWifiUserTimesOneInfo = WifiUserTimesDao::getWifiUserTimesOneInfo (
         $condition , [ 'timesid' , 'desc' ] , [ 'timesid','wif_psessionid' ]
      );
      // wifi处于为连接状态
      if($getWifiUserTimesOneInfo && $getWifiUserTimesOneInfo->wif_psessionid != '' )
      {
         $produce = Factory::produce(1);
         $checkAccountOnline = $produce->AccountOffline(
            ['psessionid'=>$getWifiUserTimesOneInfo->wif_psessionid]
         );
         if($checkAccountOnline['status'] == true)
         {
            $saveData['wif_psessionid'] = '';
            WifiUserTimesDao::addOrUpdateWifiUserTimesInfo ($saveData,$getWifiUserTimesOneInfo['timesid']);
            return JsonBuilder::Success ( '下线成功' );
         } else {
            return JsonBuilder::Error ( '下线失败,请稍后重试' );
         }
      } else {
         return JsonBuilder::Error ( '下线失败,请稍后重试' );
      }
   }

   /**
    * Func 支付异步
    * @param Request $request
    * @return Json
    */
   public function asyns_notice_info(WifiPayRequest $request)
   {
      // 获取支付类型
      $type = $request->get ('type');

      // 微信支付
      if($type == 'weixin')
      {
         $pay = Pay::wechat(config ( 'pay.wechat' ));

         try{
            $data = $pay->verify();

            $data->all();
            // 状态(0:关闭,1:待支付,2:支付失败,3:支付成功-WIFI充值中,4:支付成功-WIFI充值成功,
            // 5:支付成功-充值失败,6:支付成功-退款成功)
            $condition[] = [ 'trade_sn' , 'eq' , $data['out_trade_no'] ];
            $condition[] = [ 'status' , 'eq' , 1 ];

            $orderOneInfo = DB::table ( 'wifi_orders' )->where ( $condition )->first ();

            if ( empty( $orderOneInfo ) )
            {
               return false;
            }

            // 更新状态
            $saveData['status'] = 3;
            $saveData['pay_time'] = date('Y-m-d H:i:s');

            if( DB::table ( 'wifi_orders' )->where( $condition )->update( $saveData ) )
            {
               WifiAsynsPodcast::dispatch($orderOneInfo);
               return $pay->success()->send();
            } else {
               throw new InvalidSignException('订单支付成功更新失败');
            }
         } catch (\Exception $e)
         {
            return $e->getMessage();
         }
      }

      // 支付宝支付
      if($type == 'alipay')
      {
         $alipay = Pay::alipay(config ( 'pay.alipay' ));

         try{
            $data = $alipay->verify();

            // 获取数据
            $data = $data->toArray ();

            if( in_array ( $data['trade_status'] , [ 'TRADE_SUCCESS' , 'TRADE_FINISHED' ] ) )
            {
               // 状态(0:关闭,1:待支付,2:支付失败,3:支付成功-WIFI充值中,4:支付成功-WIFI充值成功,
               // 5:支付成功-充值失败,6:支付成功-退款成功)
               $condition[] = [ 'trade_sn' , 'eq' , $data['out_trade_no'] ];
               $condition[] = [ 'status' , 'eq' , 1 ];

               $orderOneInfo = DB::table ( 'wifi_orders' )->where ( $condition )->first ();

               if ( empty( $orderOneInfo ) )
               {
                  return false;
               }

               // 更新状态
               $saveData['status'] = 3;
               $saveData['pay_time'] = date('Y-m-d H:i:s');

               if( DB::table ('wifi_orders')->where( $condition )->update( $saveData ) )
               {
                  WifiAsynsPodcast::dispatch($orderOneInfo);
                  return $alipay->success()->send();
               } else {
                  throw new InvalidSignException('订单支付成功更新失败');
               }
            } else {
               throw new InvalidSignException('支付订单错误');
            }
         } catch (\Exception $e)
         {
             $e->getMessage();
         }
      }
   }


}
