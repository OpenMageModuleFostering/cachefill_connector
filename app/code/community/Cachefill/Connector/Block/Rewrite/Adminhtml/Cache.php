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

class Cachefill_Connector_Block_Rewrite_Adminhtml_Cache extends Mage_Adminhtml_Block_Cache {
	
	public function __construct(){
		parent::__construct();
        $this->_addButton('cachefill_new', array(
            'label'     => Mage::helper('core')->__('Start New CACHE FILL Process'),
            'onclick'   => 'setLocation(\'' . Mage::helper('cfconnector')->getStartRemoteUrl() .'\')',
            'class'     => 'add',
        ));
        
        $lastResultUrl = Mage::helper('cfconnector')->getLastResultUrl();
        if(!!$lastResultUrl){
        	$this->_addButton('cachefill_last', array(
            'label'     => Mage::helper('core')->__('View Last CACHE FILL Result'),
            'onclick'   => 'window.open(\'' . $lastResultUrl .'\', \'_blank\')',
            'class'     => 'save',
        ));
        }
    }
	
    
    
}