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
 
class Cachefill_Connector_Model_Mysql4_Remoteproc extends Mage_Core_Model_Mysql4_Abstract {
	
	protected function _construct(){
		$this->_init('cfconnector/remoteproc', 'entity_id');
	}
	
	public function getFirstActiveProcess(){
		$read = $this->_getReadAdapter();
		$select = $read->select()->from($this->getMainTable())
				->where('`status` = ?', Cachefill_Connector_Model_Remoteproc::STATUS_PROCESSING);
		$result = $read->fetchRow($select);
		if(!$result){
			return array();
		}
		return $result;
	}
	
}