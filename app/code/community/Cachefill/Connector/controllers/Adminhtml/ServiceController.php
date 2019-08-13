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

class Cachefill_Connector_Adminhtml_ServiceController extends Mage_Adminhtml_Controller_Action {

    public function remoteEngineExecAction() {
    	//Must have a valid access_key
    	$accessKey = Mage::helper('cfconnector')->getAccessKey();
    	if(!$accessKey){
    		$systemConfigUrl = $this->getUrl('adminhtml/system_config/edit', array('section' => 'cfconnector'));
    		$this->_getSession()->addError("Your CACHE FILL service is not properly configured. Please <a href=\"$systemConfigUrl\">go to the CACHE FILL configuration panel</a>.");
    		$this->_redirectReferer();
    		return;
    	}
    	
    	//Check if any remote process is still running
    	$hasActiveRemoteproc = false;
    	$activeRemoteprocCollection = Mage::getModel('cfconnector/remoteproc')->getCollection();
    	$activeRemoteprocCollection->getSelect()->where('`status` = ?', Cachefill_Connector_Model_Remoteproc::STATUS_PROCESSING);
    	foreach($activeRemoteprocCollection as $activeRemoteproc){
    		//Update status remotely
    		$remoteRequestStatus = Mage::helper('cfconnector')->updateProcStatusByEngineRemote($activeRemoteproc, $accessKey);
    		if($remoteRequestStatus !== false && $activeRemoteproc->getStatus() == Cachefill_Connector_Model_Remoteproc::STATUS_PROCESSING){
    			$hasActiveRemoteproc = true;
    			$resultUrl = Mage::helper('cfconnector')->getEngineExecResultUrl($activeRemoteproc->getData('exec_key'));
    			break;
    		}
    	}
    	if($hasActiveRemoteproc){
    		$this->_getSession()->addNotice("The last CACHE FILL process is still running. Please <a href=\"$resultUrl\" target=\"_blank\">click here to see preliminary results</a>.");
    		$this->_redirectReferer();
    		return;
    	}

    	//Prepare to make a new request
	    $requestParamInfo = base64_encode(json_encode(array(
	       		'access_key' 	=> $accessKey, 
	       		'site_url'		=> Mage::helper('cfconnector')->getHostOnlyUrl(), //Host only, the CACHE FILL always starts from the webroot
	    		'platform' 		=> 'Magento',
	        	'version' 		=> Mage::getVersion()
	    )));
	        
    	$crawlerRequestUrl = Mage::helper('cfconnector')->getEngineExecRequestUrl($requestParamInfo);
		$crawlerRequestCurl = curl_init();
		curl_setopt($crawlerRequestCurl, CURLOPT_URL, $crawlerRequestUrl);
		curl_setopt($crawlerRequestCurl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($crawlerRequestCurl, CURLOPT_RETURNTRANSFER, 1);
		$crawlerResultData = json_decode(curl_exec($crawlerRequestCurl), 1);
		curl_close($crawlerRequestCurl);
		
		//Check status, exec_key and engine_status
		if(!isset($crawlerResultData['status']) 
				|| $crawlerResultData['status'] != 1
				|| !isset($crawlerResultData['exec_key'])
		){
			$this->_getSession()->addError("There is an error trying to run CACHE FILL on your site. Please try again later.");
			$this->_redirectReferer();
    		return;
		}
		
		//Save a new process
    	$remoteproc = Mage::getModel('cfconnector/remoteproc');
    	$remoteproc->setExecKey($crawlerResultData['exec_key']);
    	if(isset($crawlerResultData['engine_status'])){
    		$remoteproc->setStatus($crawlerResultData['engine_status']);
    	}
    	$remoteproc->save();
    	
		//Try to save the engine request and status
		$resultUrl = Mage::helper('cfconnector')->getEngineExecResultUrl($crawlerResultData['exec_key']);
    	$this->_getSession()->addSuccess(Mage::helper('adminhtml')->__("The CACHE FILL process is running in the background. <a href=\"$resultUrl\" target=\"_blank\">Click here to see preliminary results</a>."));
    	$this->_redirectReferer();
    	return;
    }
    
    protected function _getSession() {
        return Mage::getSingleton('adminhtml/session');
    }
    
}