<?php
class Publisher_Form_regform extends Zend_Form
{
    public $elementDecorators = array(
        'ViewHelper',
        'Errors',
        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
        array('Label', array('tag' => 'td', 'class' => 'tdleftBold')),
		array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    );
	
	public $buttonDecorators = array(
        'ViewHelper',
        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
        array(array('Label' => 'HtmlTag'), array('tag' => 'td', 'placement' => 'prepend')),
        array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    );

    public function init()
    {
        $this->addElement('text', 'username', array(
            'decorators' => $this->elementDecorators,
            'label'      => 'Username:',
		));
			
        $this->addElement('text', 'firstname', array(
            'decorators' => $this->elementDecorators,
            'label'       => 'First Name:',
			'class'		=>'req form-textfield',
        ));
        $this->addElement('text', 'lastname', array(
            'decorators' => $this->elementDecorators,
            'label'       => 'Last Name:',
        ));
        $this->addElement('submit', 'save', array(
            'decorators' => $this->buttonDecorators,
            'label'       => 'Save',
			'class'		=>'button-Save',
        ));
		
		$this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table')),
            'Form',
        ));
		
		//$this->addFilter('HtmlEntities')->addFilter('StringToLower')->addFilter('StringTrim');
		
	}

    public function loadDefaultDecorators()
    {
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table')),
            'Form',
        ));
		
		$this->setAttribs(array('id'=>'form-add-user','onsubmit'=>'return validate_user(\'form-add-user\');'));
    }
}
?>