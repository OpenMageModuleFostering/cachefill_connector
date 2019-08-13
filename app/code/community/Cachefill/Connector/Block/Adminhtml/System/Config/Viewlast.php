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

class Cachefill_Connector_Block_Adminhtml_System_Config_Viewlast extends Mage_Adminhtml_Block_System_Config_Form_Field {
	
	protected function _toHtml() {
    	$htmlId = $this->getHtmlId();
    	$ajaxUrl = $this->getAjaxUrl();
    	$buttonLabel = $this->escapeHtml($this->getButtonLabel());
    	$accessKey = Mage::helper('cfconnector')->getAccessKey();
    	if(!$accessKey){
    		return "";
    	}
    	
    	$lastResultUrl = Mage::helper('cfconnector')->getLastResultUrl();
    	if(!$lastResultUrl){
    		return "";
    	}
    	
    	
		$htmlContent = <<< HTML_CONTENT
<button onclick="window.open('$lastResultUrl', '_blank')" class="scalable" type="button" id="$htmlId">
    <span><span><span>$buttonLabel</span></span></span>
</button>
HTML_CONTENT;
    	
    	return $htmlContent;
    }

    public function render(Varien_Data_Form_Element_Abstract $element){
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
    
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element){
        $originalData = $element->getOriginalData();
        $this->addData(array(
            'button_label' => Mage::helper('cfconnector')->__($originalData['button_label']),
            'html_id' => $element->getHtmlId()
        ));
        return $this->_toHtml();
    }
    
}