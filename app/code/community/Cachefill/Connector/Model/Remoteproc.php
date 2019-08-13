<?php

/* NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 */ 

class Cachefill_Connector_Model_Remoteproc extends Mage_Core_Model_Abstract {

	const STATUS_PENDING = 10;
	const STATUS_PROCESSING = 20;
	const STATUS_COMPLETE = 30;
	
	protected function _construct(){
        $this->_init('cfconnector/remoteproc');
    }
	
    //====== Note that json_status is compressed ======//
	protected function _beforeSave(){
		$datetime = date('Y-m-d H:i:s');
    	if(!$this->getId()){
    		$this->setData('created_at', $datetime);
    	}
    	$this->setData('updated_at', $datetime);
    	parent::_beforeSave();
    }

    public function getFirstActiveProcess(){
		$result = $this->getResource()->getFirstActiveProcess();
		$this->addData($result);
		return $this;
    }
    
    public static function getStatusCodeLabel($crawlerProcStatus){
    	switch($crawlerProcStatus){
    		case self::STATUS_PENDING:
    			return 'pending';
    			break;
    		case self::STATUS_PROCESSING:
    			return 'processing';
    			break;
    		case self::STATUS_COMPLETE:
    			return 'complete';
    			break;
    		default:
    			return 'unknown';
    			break; 
    	}
    }

}