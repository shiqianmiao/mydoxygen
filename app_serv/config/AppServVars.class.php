<?php
/**
 * @package              v3
 * @subpackage
 * @author               $Author:   weihongfeng$
 * @file                 $HeadURL$
 * @version              $Rev$
 * @lastChangeBy         $LastChangedBy$
 * @lastmodified         $LastChangedDate$
 * @copyright            Copyright (c) 2012, www.273.cn
 */
define('API_VERSION_NOW', 1.1);
define('API_VERSION_NEW', 1.3);
class AppServVars {
    //主站越狱
    const MS_IOS_VERSION = '1.3';
    //主站app store
    const MS_IOS_APP_VERSION = '1.3';
    //主站安卓
    const MS_ARD_VERSION = '1.4';

    //业管安卓
    const MBS_ARD_VERSION = '2.91';

    //业管ios越狱
    const MBS_IOS_VERSION = '2.9.1';

    //业管ios appStore
    const MBS_IOS_APP_VERSION = '2.9.1';

    const APP_SOURCE_IOS = 1;

    const APP_SOURCE_IOS_STORE = 2;

    const APP_SOURCE_ANDROID = 3;

    const APP_TYPE_MBS = 1;

    const APP_TYPE_MS = 2;

    const APP_TYPE_CKB = 3;

    /**
     * 是否需要强制升级
     * @var bool
     */
    public static $IS_MANDATORY_UPGRADE = true;

    public static $APP_VERSIONS = array(
        self::APP_TYPE_MBS   => array(
            self::APP_SOURCE_IOS   => self::MBS_IOS_APP_VERSION,
            self::APP_SOURCE_IOS_STORE => self::MBS_IOS_APP_VERSION,
            self::APP_SOURCE_ANDROID   => self::MBS_ARD_VERSION,
        ),
        self::APP_TYPE_MS   => array(
            self::APP_SOURCE_IOS   => self::MS_IOS_VERSION,
            self::APP_SOURCE_IOS_STORE => self::MS_IOS_APP_VERSION,
            self::APP_SOURCE_ANDROID   => self::MS_ARD_VERSION,
        ),
    );

    public static $APP_SERV_METHOD_CONFIG = array(
            'apns.spendTime' => 1,
            'apns.pushMessageToSingle' => 1,
            'apns.push' => 1,
            'apns.pushOneMessage'=>1,
            'apns.getOneDeviceToken' => 1,
            'apns.updateDeviceToken' => 1,
            'apns.updateYgDeviceToken' => 1,
             'apns.updateDeviceTokenUser' => 1,
            'apns.getAndroidInfoByTime' => 1,
            'apns.getCarInfoByIds' => 1,
            'apns.addDeviceToken' => 1,
            'apns.addYgDeviceToken' => 1,
            'apns.getApnsInfoById' => 1,
            'contacts.addContacts' =>1,
            'contacts.searchUsers' => 1, 
            'city.getFuzzyCityByName' => 1,
            'city.isChainCity' => 1,
            'config.getConfig' => 1,
            'config.getFrameConfig' => 1,
            'config.getTagsConfig'  => 1,
            'dept.getPhoneBanlance' => 1,
            'dept.getAllDeptPoint' => 1,
            'dept.isExtPhone' => 1,
            'dept.getDeptByCity' => 1,
            'dept.getDeptsByCity' => 1,
            'dept.getDeptCarNum' => 1,
            'dept.getDeptClauseStatus' => 1,
            'dept.getDeptRuleDetail' => 1,
            'dept.getDeptById' => 1,
            'dept.getSyncCount' => 1,
            'var.getPriceOption' => 1,
            'var.getCarAgeOption' => 1,
            'var.getKilometerOption' => 1,
            'var.getCarTypeOption' => 1,
            'var.getHotCarBrandOption' => 1,
            'var.getCarBrandOption' => 1,
            'var.getCarSeriesOption' => 1,
            'var.getHotCity' => 1,
            'var.getCarModelOption' => 1,
            'var.getStoreCity' => 1,
            'var.showRecommended' => 1,//是否显示精品推荐
            'evaluate.getHistoryEvaluate' => 1,
            'evaluate.getResult' => 1,
            'evaluate.getFactoryPrice' => 1,
            'evaluate.publish' => 1,
            'search.getSaleList' => 1,
            'search.getSaleDetail' => 1,
            'search.filterZjIdsByIds'=>1,
            'auth.getAccessToken' => 1,
            'user.login' => 1,
            'user.getUserInfo' => 1,
            'user.getSaleUsersInfo' => 1,
            'user.getUserInfoById' => 1,
            'user.changePwd' => 1,
            'usercomment.getUserCommentRank'=>1,
            'usercomment.getUserPhone'=>1,
            'usercomment.getRecentCarRankByUserName'=>1,
            'usercomment.getPostCount'=>1,
            'vehicle.vin' => 1,
            'vehicle.getInfoByVIN' => 1,
            'vehicle.getModelCaption' => 1,
            'vehicle.getBrandListByChar' => 1,
            'vehicle.getSeriesById' => 1,
            'vehicle.getBrandByUrl' => 1,
            'vehicle.getBrandById' => 1,
            'vehicle.getBrandByIds' => 1,
            'vehicle.getBrandList' => 1,
            'vehicle.getModelList' =>1,
            'vehicle.getSeriesByCodes' => 1,
            'condition.publish' => 1,
            'condition.getInfoById' => 1,
            'condition.getList' => 1,
            'condition.getCount' => 1,
            'condition.startCheck' => 1,
            'condition.getAppLibrary' => 1,
            'condition.setAppGps' => 1,
            'condition.getUpdateTime' => 1,
            'condition.checkCarId' => 1,
            'upload.imgUpload' => 1,
            'extphone.updateUserAllBind' => 1,
            'extphone.updateDeptAllBind' => 1,
            'extphone.getInfoByExt' => 1,
            'extphone.getPhoneByUser' => 1,
            'extphone.bindNumber' => 1,
            'extphone.getBindResult' => 1,
            'extphone.delNumber' => 1,
            'extphone.getPhoneCallLog' => 1,
            'extphone.getShowExtPhoneByDept' => 1,
            'extphone.getInfoByExtPhone' => 1,
            //推广中心
            'adcenter.cancelPayrank' => 1,
            //主站帖子浏览量统计
            'page.getViewCount'=>1,
            'page.setViewCount'=>1,
            'pay.getPhoneBalance' => 1,
            'pay.getPayBalance' => 1,
            'pay.depositCredit' => 1,
            'pay.migrate' => 1,
            'update.getVersion' => 1,
            'update.getMsIosVersion' => 1,
            'update.getMbsArdVersion' => 1,
            'update.getMbsIosVersion' => 1,
            'update.getMsArdVersion' => 1,
            'update.getCKBVersion' => 1,
            //积分排名
            'score.getUserRankInfo' => 1,
            'score.getUserComplaintCount' => 1,
            'score.getUserOtherRankList' => 1,
            'score.getDeptRankList'   => 1,
            //uvtop100的帖子接口
            'uvtop.getTop' => 1,
            'sale.saleList' => 1,
            'sale.mySaleList' => 1,
            'sale.phoneList' => 1,
            'sale.buyList' => 1,
            'sale.newSale' => 1,
            'sale.updateSale' => 1,
            'sale.newBuy' => 1,
            'sale.updateBuy' => 1,
            'sale.refreshRank' => 1,
            'feedback.add' => 1,
            'feedback.price' => 1,
            'feedback.report' => 1,
            'autocomplete.find' => 1,
            'autocomplete2.find' => 1,
            'sale.getInfoByExtPhone' => 1,
            'sale.addressBook' => 1,
            'sale.getOtherDepts' => 1,
            'sale.stop'=>1,
            'sale.cancleStop' => 1,
            'sale.checkMessage' => 1,
            'sale.visitMessage' => 1,
            'sale.checkPass' => 1,
            'sale.noCheckPass' => 1,
            'sale.checkList' =>1,
            'sale.getMessageBytime' =>1,
            'sale.messageCount' => 1,
            'sale.updateRead' =>1,
            'sale.getSaleDetailByCarId' => 1,
            'sale.getBuyDetailByCarId' => 1,
            'sale.suggest'=>1,
            'sale.priceEvaluate'=>1,
            'sale.vinSearch'=>1,
            'sale.deleteDraft' => 1,
            'sale.saleStatusList' => 1,
            'sale.potentialUserList' => 1,
            'sale.callbackList' => 1,
            'sale.execOperator' => 1,
            'sale.todoTypeList' => 1,
            'sale.todoList' => 1,
            'sale.getCarTabs' => 1,
            'priceevaluate.getEvaluate'=>1,
            'priceevaluate.priceEvaluate' => 1,
            'priceevaluate.getSimilarCars' => 1,
            'priceevaluate.mbsPriceEvaluate' => 1,
            'priceevaluate.mbsSimilarCars' => 1,
            'pos.posMethod' => 1,
            'chefubao.payForList' => 1,
            'chefubao.getOrderInfo' => 1,
            'chefubao.finishPay' => 1,
            //爬虫入库
            'spiders.add' => 1,
            'spiders.updateOrderSource' => 1,
            'spiders.addToCarSpiderData' => 1,
            'spiders.addMysql' => 1,
            'spiders.insertPersonalCar'=>1,
            'spiders.delPersonalCarByUrl'=>1,
            'spiders.getNewAllNetCount'=>1,
            //手机号码库
            'phone.insertThirdPartyInfo' => 1,
            'spiders.insertPersonalCar'=>1,

            'sync2site.getRefreshQueue' => 1,
            'sync2site.setRefreshQueue' => 1,
            'sync2site.getTopQueue' => 1,
            'sync2site.checkSyncList' => 1,
            'sync2site.getSyncList'=>1,
            'sync2site.setQueue'=>1,
            'sync2site.getSyncDeptInfo' =>1,
            'sync2site.getCarsHook' =>1,
            'sync2site.setEditQueue' =>1,
            'sync2site.deletePost' =>1,
            'sync2site.Sync2OtherSite' => 1,
            'sync2site.getSeriesCacheAction' => 1,
            'sync2site.getModelsCacheAction' => 1,
            'sync2site.getBrandsCacheAction' => 1,
            'sale.newSaleSouche' =>1,
            'subscribe.addSubscribeCondition' =>1,
            'subscribe.getSubscribeCondition' =>1,
            'subscribe.updateSubscribeCondition' =>1,
            'subscribe.updateConfigStatus' =>1,
            'subscribe.getConfig'=>1,
            'subscribe.getConfigV2'=>1,
            'subscribe.getCarListByConditionId' =>1,
            'subscribe.getCarListByClientId' =>1,
            'subscribe.deleteSubscribeById' =>1,
            'subscribe.updateClientId' =>1,
            'user.checkUser' => 1,
            'car.getSaleList' => 1,
            'log.insertLog' => 1,
            //门店合作
            'opendept.getUserData' => 1,
            'opendept.getDeptData' => 1,
            'opendept.getDeptNum' => 1,
            'opendept.getDeptList' => 1,
            'opendept.getUserInfoByUsername'  => 1,
            'opendept.getDeptInfoById'  => 1,
            'opendept.isLoginMbs' => 1,
            //城市接口
            'geo.getProvinceList' => 1,
            'geo.getCityList' => 1,
            //车友圈
            'friends.addMessage'=> 1,
            'friends.addImages'=> 1,
            'friends.deleteMessage'=> 1,
            //用户手机保护
            'phone.phoneProtect' => 1,
            //订单支付
            'payment.getOrderList' => 1,
            'payment.searchOrderList' => 1,
            'payment.newOrder' => 1,
            //单元测试
            'unit.testSso' => 1,
            //主站app会员
            'member.login' => 1,
            'member.register' => 1,
            'member.fastLogin' => 1,
            'member.getInfo' => 1,
            'member.updateInfo' => 1,
            'member.changePasswd' => 1,
            'member.checkResetCode' => 1,
            'member.sendMobileValidCode' => 1,
            //主站app会员
            'saleapp.getCarInfoByCarId' => 1,
            'saleapp.getRecommendedFollowUser' => 1,
            'saleapp.getEfficientFollowUser' => 1,
            'saleapp.getHistoryFollowUser' => 1,
            'saleapp.searchFollowUser' => 1,
            'saleapp.getFollowUserByCarId' => 1,
            'saleapp.setBlack' => 1,
            //卖车APP
            'saleapp.publish' => 1,
            'saleapp.edit' => 1,
            'saleapp.delete' => 1,
            'saleapp.getOnSaleList' => 1,
            'saleapp.getDoneList' => 1,
            'saleapp.getCarInfoByCarId' => 1,
            'saleapp.getRecommendedFollowUser' => 1,
            'saleapp.getEfficientFollowUser' => 1,
            'saleapp.getHistoryFollowUser' => 1,
            'saleapp.searchFollowUser' => 1,
            'saleapp.getFollowUserByCarId' => 1,
            'saleapp.setBlack' => 1,
            'saleapp.cityConfig' => 1,
            'saleapp.changePrice' => 1,
            //我要出价
            'depreciate.submit' => 1,
            //微信业管，发送消息
            'weixin.sendMsg' => 1,
            'weixin.saveUnsub' => 1,
            'weixin.getUnsubList' => 1,
    );
    //需要登陆的方法，客户端需要传入_api_passport和_api_app
    public static $APP_SERV_METHOD_LOGIN_CONFIG = array(
        'user.getUserInfo' => array(),
        'user.changePwd' => array(),
        'condition.publish' => array(),
        //'condition.getInfoById' => array(),
        'condition.getList' => array(),
        'condition.getCount' => array(),
        'config.getConfig' => array(),
        'contacts.addContacts' =>array(),
        'contacts.searchUsers' => array(),
        'sale.saleList' => array(),
        'sale.mySaleList' => array(),
        'sale.execOperator' => array(),
        'sale.phoneList' => array(),
        'sale.buyList' => array(),
        'sale.newSale' => array(),
        'sale.updateSale' => array(),
        'sale.newBuy' => array(),
        'sale.updateBuy' => array(),
        'sale.refreshRank' => array(),
        'sale.addressBook' => array(),
        'sale.getOtherDepts' => array(),
        'sale.stop'=> array(),
        'sale.cancleStop' => array(),
        'sale.checkMessage' => array(),
        'sale.visitMessage' => array(),
        'sale.checkPass' => array(),
        'sale.noCheckPass' => array(),
        'sale.checkList' => array(),
        'sale.messageCount' => array(),
        'sale.updateRead' => array(),
        'sale.getSaleDetailByCarId' => array(),
        'sale.getBuyDetailByCarId' => array(),
        'sale.suggest'=>1,
        'sale.priceEvaluate'=>1,
        'sale.vinSearch'=>1,
        'sale.contacts'=>1,
        'sale.newSaleSouche' =>array(),
        'sale.deleteDraft' => array(),
        'sale.todoList' => array(),
        'friends.addMessage'=> 1,
        'friends.addImages'=> 1,
        'friends.deleteMessage'=> 1,
        //订单支付
        'payment.getOrderList' => 1,
        'payment.searchOrderList' => 1,
        'payment.newOrder' => 1,
        //单元测试
        'unit.testSso' => 1,
        //全网淘车消息数量提醒
        'spiders.getNewAllNetCount' => 1
    );
}
