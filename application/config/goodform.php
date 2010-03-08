<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// list of attributes that will not be included as element attributes
$config['invalid_attributes'] = array('rules', 'element', 'label', 'description', 'direction', 'error', 'options', 'view', 'validation', 'input', 'selected');

// list of datamapper validation field attributes that should be used by the dm goodform plugin
$config['allowed_dm_attributes'] = array('name', 'label', 'rules', 'description', 'error', 'view', 'value', 'class', 'title', 'id', 'rows', 'cols', 'size', 'options', 'maxlength', 'input', 'readonly');

// Error message classes
$config['error_class'] = 'error';
$config['error_prefix'] = '';
$config['error_suffix'] = '-error';

// Tooltip message classes
$config['tooltip_class'] = 'tooltip';
$config['tooltip_prefix'] = '';
$config['tooltip_suffix'] = '-tooltip';

// String added to each required Lable element
$config['required_suffix'] = ' <span class="c-red">*</span>';


## HORIZONTAL OPTION GROUPS  ##

// wraps vertical input groups options
// NOTE use %att% placeholder to mark where elements attributes will be inserted
$config['input_group_h_prefix'] = '<div class="horizontal-input-group" %att%>';
$config['input_group_h_suffix'] = '<div class="clear"></div></div>';
// wraps each option in a horzontal group
$config['input_group_h_option_prefix'] = '';
$config['input_group_h_option_suffix'] = '';
// wraps each selected option in a horzontal group
$config['input_group_h_selected_option_prefix'] = '';
$config['input_group_h_selected_option_suffix'] = '';
// wraps each options label in a horzontal group
$config['input_group_h_label_prefix'] = '<label>';
$config['input_group_h_label_suffix'] = '</label>';


## VERTICAL OPTION GROUPS  ##

// wraps vertical input groups options
// NOTE use %att% placeholder to mark where elements attributes will be inserted
$config['input_group_v_prefix'] = '<ul class="vertical-input-group" %att%>';
$config['input_group_v_suffix'] = '</ul>';
// wraps each option in a vertical group
$config['input_group_v_option_prefix'] = '<li>';
$config['input_group_v_option_suffix'] = '<div class="clear"></div></li>';
// wraps each selected option in a vertical group
$config['input_group_v_selected_option_prefix'] = '<li class="selected">';
$config['input_group_v_selected_option_suffix'] = '<div class="clear"></div></li>';
// wraps each options label in a vertical group
$config['input_group_v_label_prefix'] = '<label>';
$config['input_group_v_label_suffix'] = '</label>';
// Wrapper for optgroups
$config['input_group_v_optgroup_prefix'] = '<li>';
$config['input_group_v_optgroup_suffix'] = '</li>';
// Optgroup Label
$config['input_group_v_optgroup_label_prefix'] = '<h6>';
$config['input_group_v_optgroup_label_suffix'] = '</h6>';

// Force GoodForm to use field names as their id
// This helps keep forms valid as label 'for' 
// attributes require an element to exist with that id
$config['force_field_id'] = FALSE;


## JQUERY VALIDATION INTERGRATION ##

// if true, Goodform will auto add jquery validation classes 
// to fields with defined validation rules
$config['jquery_validation'] = TRUE;

// array defines jquery class names that match CI form validation methods
$config['jquery_validation_classes'] = array(
	'required'		=> 'required',
	'min_length'	=> 'minlength',
	'max_length'	=> 'maxlength',
	'min_size'		=> 'min',
	'max_size'		=> 'max',
	'valid_email'	=> 'email',
	'valid_emails'	=> 'email_multiple', /* included in additional-methods.js */
	'numeric'		=> 'number',
	'integer'		=> 'digits',
);

/* End of file goodform.php */
/* Location: ./system/application/config/goodform.php */