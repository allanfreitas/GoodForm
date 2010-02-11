<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// list of attributes that will not be included as element attributes
$config['invalid_attributes'] = array('element', 'label', 'description', 'error', 'options', 'view', 'validation', 'input', 'selected');

// list of datamapper validation field attributes that should be used by the dm goodform plugin
$config['allowed_dm_attributes'] = array('name', 'label', 'description', 'error', 'view', 'value', 'class', 'title', 'id', 'rows', 'cols', 'size', 'options', 'maxlength', 'input', 'readonly');

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

// Input Groups
$config['input_group_label_class'] = 'label';
// Wrapper for optgroups
$config['input_group_optgroup_prefix'] = '<div class="optgroup">';
$config['input_group_optgroup_suffix'] = '</div>';
// Optgroup Label
$config['input_group_optgroup_label_prefix'] = '<p class="optgroup-label">';
$config['input_group_optgroup_label_suffix'] = '</p>';

/* End of file goodform.php */
/* Location: ./system/application/config/goodform.php */