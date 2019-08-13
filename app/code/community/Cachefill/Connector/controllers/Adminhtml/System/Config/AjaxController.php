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

class Cachefill_Connector_Adminhtml_System_Config_AjaxController extends Mage_Adminhtml_Controller_Action {

    public function loginAction() {
		try{
			$username = $this->getRequest()->getParam('username');
    		$password = $this->getRequest()->getParam('password');
			//Check encrypted password
			if (preg_match('/^\*+$/', $password)) {
	            $password = Mage::helper('core')->decrypt(Mage::getStoreConfig('waconnector/general/password'));
	        }
	        
	        //Host only, the CACHE FILL always starts from the webroot
	        $requestParamInfo = base64_encode(json_encode(array(
	        		'username' 	=> $username, 
	        		'password' 	=> $password,
	        		'site_url'	=> Mage::helper('cfconnector')->getHostOnlyUrl(), //Host only, the CACHE FILL always starts from the webroot
	        		'platform' 	=> 'Magento',
	        		'version' 	=> Mage::getVersion()
	        )));
	        
    		$loginRequestUrl = Mage::helper('cfconnector')->getSubscriptionValidateRequestUrl($requestParamInfo);
			$loginRequestCurl = curl_init();
			curl_setopt($loginRequestCurl, CURLOPT_URL, $loginRequestUrl);
			curl_setopt($loginRequestCurl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($loginRequestCurl, CURLOPT_RETURNTRANSFER, 1);
			$loginResultData = json_decode(curl_exec($loginRequestCurl), 1);
			curl_close($loginRequestCurl);
			
			if(!isset($loginResultData['status'])){
				throw new Exception('Connection Failed.');
			}
			
			if($loginResultData['status'] != 1){
				if(!isset($loginResultData['message'])){
					throw new Exception('Connection Failed.');
				}else{
					throw new Exception($loginResultData['message']);
				}
			}
			
			if(!isset($loginResultData['access_key'])){
				throw new Exception('Invalid access key.');
			}
			
			$accessKeyConfigValue = $loginResultData['access_key'];
			echo json_encode(array(
					'status' => 1, 
					'message' => 'Success! Please save configuration.'
			)); //Json success
			
		}catch(Exception $e){
			$accessKeyConfigValue = "";
			echo json_encode(array(
					'status' => 0,
					'message' => $e->getMessage()
			)); //Json error
			
		}
		
		$coreConfigData = Mage::getModel('core/config_data')->load('cfconnector/general/access_key', 'path');
		$coreConfigData->setPath('cfconnector/general/access_key'); //In case of new save
		$coreConfigData->setValue($accessKeyConfigValue);
		$coreConfigData->save();
		exit;
    }
    
}