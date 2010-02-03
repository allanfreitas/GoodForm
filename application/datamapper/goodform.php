<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Form Extension for DataMapper classes.
 *
 * Quickly turn DM Objects into GoodForm Objects
 *
 * TODO
 * - code a form_callback function to format values before sending them to 
 *   the goodform object.
 * - code a options_callback function to return an option array to send to
 *   the goodform object.
 *
 * @license 	MIT License
 * @category	DataMapper Extensions
 * @author  	Jim Wardlaw
 * @link    	http://www.stucktogetherwithtape.com/code/
 * @version 	0.1
 */

// --------------------------------------------------------------------------

/**
 * DMZ_Goodform Class
 */
class DMZ_Goodform {

	####################################
	## METHODS TO GENERATE HTML FORMS ##
	####################################
	
   /**
	* Populates a goodform object reference
	* with fields from this model.
	*
	* Fields can be specified via array
	* as param. By default all fields are added.
	*
	* Returns a GoodForm Object instance containing the form.
	*
	* @access	public
	* @param	object 	-	instance of the DM Object calling this extension
	* @param	object	-	reference to instance of goodform object
	* @param	array	-	array of fields to add to array
	* @return	boolean
	*/
	public function form($object, &$goodform, $fields='')
	{
		// select all fields if not defined
		if(empty($fields))
		{
			$fields = $object->fields;
		}
		
		foreach($fields as $field)
		{
			$this->add_form_element($object, $goodform, $field);
		}
		
		// update form
		return TRUE;
	}

   /**
	* Populates a goodform object reference
	* with fields from this model.
	*
	* Fields can be specified via array
	* as param. By default all fields are added.
	*
	* Returns a GoodForm Object instance containing the form.
	*
	* @access	public
	* @param	object	-	instance of the DM Object calling this extension
	* @param	object	-	reference to instance of goodform object
	* @param	array	-	array of fields to add to array
	* @return	boolean
	*/
	public function post_form($object, $fields='')
	{
		log_message('error', 'post form!');
		
		// get global CI instance
		$CI =& get_instance();
		
		// select all fields to update if not defined
		if(empty($fields))
		{
			$fields = $object->fields;
		}
		
		foreach($fields as $field)
		{
			// check field value has been posted
			if ($CI->input->post($field) !== FALSE)
				// assign posted value to object field
				$object->{$field} = $CI->input->post($field);
		}
	}
	
   /**
	* Adds a form element to the goodform object
	*
	* @access	public
	* @param	object
	* @param	object ref
	* @param	string
	* @return	void
	*/
	protected function add_form_element($object, &$goodform, $field)
	{
		// check field exists in dm model
		if (!isset($object->validation[$field]))
		{
			log_message('error', 'DMZ Goodform: Field '.$field.' does not exist in DM validation array');
			return;
		}
		
			
		// collect validation array for field from object
		$spec = $object->validation[$field];
		
		// add a name field - this is the form elements name 
		$spec['name'] = $spec['field'];
		
		// get the field value from the object
		$spec['value'] = $object->$field;
		
		// look for any existing error messages for field
		if (!empty($object->error->{$field}))
			$spec['error'] = $object->error->{$field};
		
		if (isset($spec['type']))
			// get the form input type if defined
			$input_type = $spec['type'];
		else
			// use text input by default
			$input_type = 'text';
			
		// if FALSE ignore field
		if ($input_type === FALSE)
			return;
			
		// define allowed values in form spec array
		$allowed = array('name', 'label', 'description', 'error', 'view', 'value', 'class', 'id', 'rows', 'cols', 'size', 'options', 'maxlength', 'input', 'readonly');
		
		foreach ($spec as $k => $v)
		{
			if(!in_array($k, $allowed))
				// remove unwanted element from validation array
				unset($spec[$k]);
		}
			
		
		// add form to gf object
		return $goodform->{$input_type}($spec);
		
		if (isset($field['type']) AND $field['type'] != FALSE)
		{
			// is this value defined as a reationship and options do not already exists
			if($this->is_related($field['field']))
			{
				$model = str_replace('_id', '', $field['field']);
			
				$obj = new $model();
								
				$obj->get();
				
				$field['options'] = $obj->options();
			}
		
			$input_type = $field['type'];
			
			$spec['name'] = $field['field'];
			
			// check for prep method
			if (isset($field['form_prep']))
				$spec['value'] = $this->extract_callback($field['form_prep'], $this->{$field['field']});	
			else
				$spec['value'] = $this->{$field['field']};
			
			
			// check for option callback
			if (isset($field['option_callback']))
			{
				$spec['options'] = $this->{$field['option_callback']}();
			}
			
			// define allowed values in form spec array
			$allowed = array('label', 'description', 'error', 'view', 'value', 'class', 'id', 'rows', 'cols', 'size', 'options', 'maxlength', 'input', 'readonly');
			
			foreach ($allowed as $a)
			{
				if(isset($field[$a]))
					$spec[$a] = $field[$a];
			}
										
			$goodform->{$input_type}($spec);
		}
	}

}