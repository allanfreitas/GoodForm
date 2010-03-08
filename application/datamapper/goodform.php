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
 * CHANGES
 * + convert_rules() takes the validation array and turns it into a string so goodform
 *	 can read validation rules and construct metadata for jquery validation plugin
 *
 * @license 	MIT License
 * @category	DataMapper Extensions
 * @author  	Jim Wardlaw
 * @link    	http://www.stucktogetherwithtape.com/code/
 * @version 	1.3.2
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
		// turn of CI validation as DM handels this for us
		$ci_validation = FALSE;
	
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
	public function post_form($object, &$goodform, $fields='')
	{
		// get global CI instance
		$CI =& get_instance();
		
		// select all fields to update if not defined
		if(empty($fields))
		{
			$fields = $object->fields;
		}
		
		foreach($goodform->fields as $field)
		{
			// check field value has been posted
			if ($CI->input->post($field) !== FALSE)
				// assign posted value to object field
				$object->{$field} = $CI->input->post($field);
		}
	}
	
   /**
	* Updates a DM Goodform with posted data and errors
	*
	* @access	public
	* @param	object 	-	instance of the DM Object calling this extension
	* @param	object	-	reference to instance of goodform object
	* @return	boolean
	*/
	public function update_form($object, &$goodform)
	{		
		// select all fields to update if not defined
		foreach($goodform->fields as $field)
		{
			if(isset($_POST[$field]))
			{
				// update form with posted value
				$goodform->elements[$field]['value'] = $_POST[$field];
			
				log_message('error', $field.' = '.$_POST[$field]);
			}
						
			if (isset($object->error->{$field}))
			{
				// update form with error message
				$goodform->elements[$field]['error'] = $object->error->{$field};
			
				log_message('error', $field.' = '.print_r($object->error, TRUE));
			}
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
		$allowed = $goodform->config->item('allowed_dm_attributes', 'goodform');
		
		foreach ($spec as $k => $v)
		{
			if(!in_array($k, $allowed))
				// remove unwanted element from validation array
				unset($spec[$k]);
		}
		
		// is this value defined as a relationship and options do not already exists
		if(isset($spec['options']) AND $spec['options'] == 'related')
		{
			// get model from field name
			$model = str_replace('_id', '', $field);
			
			// create new instance
			$obj = new $model();
			
			// get all records
			$obj->get();
			
			// assign to options array
			$spec['options'] = $obj->options();
		}
		
		// turn rules array into pipe seperated string
		$spec['validation'] = $this->convert_rules($spec);
		
		//log_message('error', 'dm = '.print_r($spec, TRUE));
		
		// add form to gf object
		return $goodform->{$input_type}($spec);
	}

   /**
	* returns an options array for all records
	* in the object. Uses the objects id field and
	* its __toString method
	*
	* @access	public
	* @param	object
	* @param	boolean
	* @return	array
	*/
	public function options($object, $include_null=TRUE)
	{
		$options = array();
		
		if($include_null)
			$options['---'] = NULL;
		
		foreach($object->all as $o)
			
			$options[(string)$o] = $o->id;
		
		return $options;
	}

   /**
	* comment
	*
	* @access	public
	* @param	string
	* @return	string
	*/
	public function convert_rules($spec)
	{
		if(!isset($spec['rules']))
			return '';
		
		$rule_array = array();
		
		foreach($spec['rules'] as $key => $value)
		{
			if(is_numeric($key))
				$rule_array[] = $value;
			else
				$rule_array[] = $key.'['.$value.']';
		}
		
		return implode('|', $rule_array);
	}
}