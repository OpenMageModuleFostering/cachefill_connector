<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 */

class Cachefill_Connector_Helper_Data extends Mage_Core_Helper_Data {
	
	protected $_accessKey = null;

	public function getSubscriptionValidateRequestUrl($requestParamInfo) {
		return "http://www.cachefill.com/cachefill/remote/subscriptionValidate/info/$requestParamInfo";
	}
	
	public function getEngineExecRequestUrl($requestParamInfo) {
		return "http://www.cachefill.com/cachefill/remote/engineExec/info/$requestParamInfo";
	}
	
	public function getEngineStatusRequestUrl($requestParamInfo) {
		return "http://www.cachefill.com/cachefill/remote/engineStatusCheck/info/$requestParamInfo";
	}
	
	public function getEngineExecResultUrl($execKey){
		return "http://www.cachefill.com/cachefill/engine/result/key/$execKey";
	}
	
	public function getHostOnlyUrl(){
		//Host only, the CACHE FILL always starts from the webroot
		$siteUrl = "";
    	$urlInfo = parse_url(Mage::getUrl());{
    		if(isset($urlInfo['host'])){
    			$siteUrl = $urlInfo['host'];
    		}
    	}
    	return $siteUrl;
	}
	
	public function getStartRemoteUrl() {
    	return Mage::helper('adminhtml')->getUrl('cfconnector_adminhtml/service/remoteEngineExec');
    }
	
	public function getLastResultUrl() {
    	$accessKey = $this->getAccessKey();
    	if(!$accessKey){
    		return "";
    	}
    	$activeRemoteprocCollection = Mage::getModel('cfconnector/remoteproc')->getCollection();
    	$activeRemoteprocCollection->getSelect()
    		->order('created_at DESC')
    		->limit(1);
    	foreach($activeRemoteprocCollection as $activeRemoteproc){
    		//Update status remotely
    		$remoteRequestStatus = Mage::helper('cfconnector')->updateProcStatusByEngineRemote($activeRemoteproc, $accessKey);
    		if($remoteRequestStatus !== false){
    			$resultUrl = Mage::helper('cfconnector')->getEngineExecResultUrl($activeRemoteproc->getData('exec_key'));
    			return $resultUrl;
    		}
    	}
		return "";
    }
    
    public function getAccessKey(){
    	if($this->_accessKey === null){
    		$coreConfigData = Mage::getModel('core/config_data')->load('cfconnector/general/access_key', 'path');
    		if(!!$coreConfigData && $coreConfigData->getValue()){
    			$this->_accessKey = $coreConfigData->getValue();
    		}
    	}
    	return $this->_accessKey;
    }
	
	public function updateProcStatusByEngineRemote($activeRemoteproc, $accessKey){
		if(!$accessKey || !$activeRemoteproc || !$activeRemoteproc->getData('exec_key')){
			return false;
		}
		   	
	    $requestParamInfo = base64_encode(json_encode(array(
	       		'access_key' 	=> $accessKey, 
	    		'exec_key'		=> $activeRemoteproc->getData('exec_key'),
	       		'site_url'		=> $this->getHostOnlyUrl(), //Host only, the CACHE FILL always starts from the webroot
	    		'platform' 		=> 'Magento',
	        	'version' 		=> Mage::getVersion()
	    )));
	        
    	$statusRequestUrl = Mage::helper('cfconnector')->getEngineStatusRequestUrl($requestParamInfo);
		$statusRequestCurl = curl_init();
		curl_setopt($statusRequestCurl, CURLOPT_URL, $statusRequestUrl);
		curl_setopt($statusRequestCurl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($statusRequestCurl, CURLOPT_RETURNTRANSFER, 1);
		$statusResultData = json_decode(curl_exec($statusRequestCurl), 1);
		curl_close($statusRequestCurl);
		
		//Check status, exec_key and engine_status
		if(!isset($statusResultData['status']) 
				|| $statusResultData['status'] != 1
				|| !isset($statusResultData['engine_status'])
		){
			//Request error
    		return false;
		}
		
		//Update status process
		if($statusResultData['engine_status'] != $activeRemoteproc->getStatus()){
	    	$activeRemoteproc->setStatus($statusResultData['engine_status']);
	    	$activeRemoteproc->save();
		}
    	return $statusResultData['engine_status'];
	}
	
}