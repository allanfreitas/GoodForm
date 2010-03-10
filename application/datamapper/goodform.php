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
 * + form() added the following methods: add_form_element(), add_model_field() and add_related_field()
 *	 changes enable a related model to be specified in the field array, which creates a dropdown to
 *	 select a realted record.
 *
 * + post_form() added the following methods: post_form_element(), post_related_field()
 *	 these methods handle posted related records, related objects are instaciated and
 *   returned from the post_form array so they can be used in the call to the save method
 *	 like so:
 *		$related = $obj->post_form($gf);
 *
 *		$obj->save($realted);
 *		
 * @license 	MIT License
 * @category	DataMapper Extensions
 * @author  	Jim Wardlaw
 * @link    	http://www.stucktogetherwithtape.com/code/
 * @version 	1.3.3
 */

// --------------------------------------------------------------------------

/**
 * DMZ_Goodform Class
 */
class DMZ_Goodform {

	// flag if a has_many model was posted
	private $has_many_posted = NULL;

   /**
	* Constructor
	*
	* Instantiates
	*
	* @access	public
	* @param	string
	* @return	string
	*/
	public function __construct()
	{
		// get global CI instance
		$CI =& get_instance();
		
		$this->input = $CI->input;
	}


	####################
	## PUBLIC METHODS ##
	####################

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
	* @param	boolean -	if TRUE will handle has_many relationships and 
	*						return both 'save' and 'remove' related model 
	*						arrays. defaults to NULL (auto)
	* @return	array
	*/
	public function post_form($object, &$goodform, $post_has_many=NULL)
	{
		// assign has_many mode to global var
		$this->has_many_posted = $post_has_many;
		
		// array containing posted related objects to save to this object
		$save_related = NULL;
				
		// array containing posted related objects to remove from this object
		$remove_related = NULL;
		
		// loop through fields in the goodform
		foreach($goodform->fields as $field)
		{
			// handle the posted form element
			$r = $this->post_form_element($object, $goodform, $field);

			// check if a realted object array was returned
			if($r)
			{
				// are we in has many mode?
				if($this->has_many_posted)
				{
					// parse returned array into seperate vars
					list($save, $remove) = $r;
					
					if($save)
						// add returned object to save array
						$save_related[$field] = $save;
					
					if($remove)
						// add returned object to remove array
						$remove_related[$field] = $remove;
				}
				else
				{
					// add returned object to save array
					$save_related[$field] = $r;
				}
				
			}
		}
		
		//log_message('error', 'related = '.print_r($related, TRUE));
		
		// are we in has many mode?
		if($this->has_many_posted)
		{
			// return array of related objects to save and remove
			return array(
				0 => $save_related,		// new objects to save
				1 => $remove_related	// existing objects to delete
			);
		}
		else if($save_related)
		{
			// return array of objects to save
			return $save_related;
		}
		else
		{
			// no relationships posted
			return NULL;
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
			if($this->input->post($field))
			{
				// update form with posted value
				$goodform->elements[$field]['value'] = $this->input->post($field);
			
				//log_message('error', $field.' = '.$_POST[$field]);
			}
						
			if (isset($object->error->{$field}))
			{
				// update form with error message
				$goodform->elements[$field]['error'] = $object->error->{$field};
			
				//log_message('error', $field.' = '.print_r($object->error, TRUE));
			}
		}
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


	#################
	## SUB METHODS ##
	#################

   /**
	* Adds a form element to the goodform object
	*
	* @access	protected
	* @param	object
	* @param	object ref
	* @param	string
	* @return	void
	*/
	protected function add_form_element($object, &$goodform, $field)
	{
		// check field exists in dm model
		if(isset($object->has_one[$field]))
		{
			return $this->add_has_one_related_field($object, &$goodform, $field);		
		}
		else if(isset($object->has_many[$field]))
		{
			return $this->add_has_many_related_field($object, &$goodform, $field);		
		}
		else if (isset($object->validation[$field]))
		{
			return $this->add_model_field($object, &$goodform, $field);
		}
		else
		{
			log_message('error', 'DMZ Goodform: Field '.$field.' does not exist in DM validation array or one of its related models');
			return;
		}
	}

   /**
	* Adds a form element for one of the dm
	* records field
	*
	* @access	protected
	* @param	object
	* @param	object ref
	* @param	string
	* @return	void
	*/
	protected function add_model_field($object, &$goodform, $field)
	{
		## Prep form field spec ##
		
		// collect validation array for field from object
		$spec = $object->validation[$field];
		
		// add a name field - this is the form elements name 
		$spec['name'] = $spec['field'];
		
		// get the current field value from the object
		$spec['value'] = $object->$field;
		
		// look for any existing error messages for field
		if (!empty($object->error->{$field}))
			$spec['error'] = $object->error->{$field};
		
		
		## Get the fields input type ##
		
		if (isset($spec['type']))
			// get the form input type if defined
			$input_type = $spec['type'];
		else
			// use text input by default
			$input_type = 'text';

		// if input type is FALSE ignore field
		if ($input_type === FALSE)
			return;

		## Get the related records options ##
		
		// does this field contain an options attribute
		if(isset($spec['options']))
		{
			// is the options attribute a string?
			if(is_string($spec['options']))
			{
				// callback method to return custom
				// option array
				$spec['options'] = $object->{$spec['options']}();
			}
			// else? must be an array or defined options. let it be.
		}


		## Filter spec and set validation rules ##
		
		// filter spec array
		$spec = $this->filter_field_spec($goodform, $spec);
		
		// turn rules array into pipe seperated string
		$spec['validation'] = $this->convert_rules($spec);
		
		// add form to gf object
		return $goodform->{$input_type}($spec);
	}

   /**
	* Adds a form element for one of the dm
	* records related records
	*
	* @access	protected
	* @param	object
	* @param	object ref
	* @param	string
	* @return	void
	*/
	protected function add_has_one_related_field($object, &$goodform, $field)
	{		
		// collect validation array for related object
		$spec = $object->has_many[$field];

		// related objects may also have infomation in the 
		// objects validation array, merge the two if set!
		if(isset($object->validation[$field]))
		{
			$spec = array_merge($spec, $object->validation[$field]);
		}
		
		// add a name field - this is the form elements name 
		$spec['name'] = $field;
		
		// get the current field value from the object
		
		// relationship already loaded?
		if($object->{$field}->exists())
		{		
			// assign value
			$spec['value'] = $object->{$field}->id;
		}
		else
		{
			// load relationship and assign value
			$spec['value'] = $object->{$field}->get()->id;
		}
		
		// look for any existing error messages for field
		if (!empty($object->error->{$field}))
			$spec['error'] = $object->error->{$field};


		## Get the related records options ##
		
		// is this value defined as a relationship and options do not already exists
		if(!isset($spec['options']))
		{			
			// create new instance of related model
			$obj = new $spec['class']();
			
			// get all records
			$obj->get();
			
			// assign to options array
			$spec['options'] = $obj->options();
		}
		else
		{
			// is the options attribute a string?
			if(is_string($spec['options']))
			{
				// callback method to return custom
				// option array
				$spec['options'] = $object->{$spec['options']}();
			}
			// else? must be an array or defined options. let it be.
		}
		
		## Get the fields input type ##
		
		if (isset($spec['type']))
			// get the form input type if defined
			$input_type = $spec['type'];
		else
			// use text dropdown by default
			$input_type = 'dropdown';

		// if input type is FALSE ignore field
		if ($input_type === FALSE)
			return;


		## Filter spec and set validation rules ##
		
		// filter spec array
		$spec = $this->filter_field_spec($goodform, $spec);
		
		// turn rules array into pipe seperated string
		$spec['validation'] = $this->convert_rules($spec);
		
		// add form to gf object
		return $goodform->{$input_type}($spec);
	}

   /**
	* Adds a form element for one of the dm
	* records related records
	*
	* @access	protected
	* @param	object
	* @param	object ref
	* @param	string
	* @return	void
	*/
	protected function add_has_many_related_field($object, &$goodform, $field)
	{
		// collect validation array for related object
		$spec = $object->has_many[$field];

		// related objects may also have infomation in the 
		// objects validation array, merge the two if set!
		if(isset($object->validation[$field]))
		{
			$spec = array_merge($spec, $object->validation[$field]);
		}

		// add a name field - this is the form elements name 
		$spec['name'] = $field;

		// get the current field value from the object

		// relationship already loaded?
		if($object->{$field}->exists())
		{
			//log_message('error', count($object->{$field}->all).' '.$field.' records related');
			
			$ids = array();

			foreach($object->{$field}->all as $o)
			{
				$ids[] = $o->id;
			}

			$spec['value'] = $ids;
		}
		else
		{
			$object->{$field}->select('id')->get();
			
			//log_message('error', count($object->{$field}->all).' '.$field.' records related loaded');
			
			$ids = array();

			foreach($object->{$field}->all as $o)
			{
				$ids[] = $o->id;
			}

			$spec['value'] = $ids;
		}
		
		//log_message('error', 'value = '.print_r($spec['value'], TRUE));

		// look for any existing error messages for field
		if (!empty($object->error->{$field}))
			$spec['error'] = $object->error->{$field};


		## Get the related records options ##
		
		// is this value defined as a relationship and options do not already exists
		if(!isset($spec['options']))
		{			
			// create new instance of related model
			$obj = new $spec['class']();
			
			// get all records
			$obj->get();
			
			// assign to options array
			$spec['options'] = $obj->options();
		}
		else
		{
			// is the options attribute a string?
			if(is_string($spec['options']))
			{
				// callback method to return custom
				// option array
				$spec['options'] = $object->{$spec['options']}();
			}
			// else? must be an array or defined options. let it be.
		}
		
		## Get the fields input type ##
		
		if (isset($spec['type']))
			// get the form input type if defined
			$input_type = $spec['type'];
		else
			// use text dropdown by default
			$input_type = 'dropdown';

		// if input type is FALSE ignore field
		if ($input_type === FALSE)
			return;


		## Filter spec and set validation rules ##
		
		// filter spec array
		$spec = $this->filter_field_spec($goodform, $spec);
		
		// turn rules array into pipe seperated string
		$spec['validation'] = $this->convert_rules($spec);
		
		// add form to gf object
		return $goodform->{$input_type}($spec);
	}


   /**
	* Posts a form element to the dm object
	*
	* @access	protected
	* @param	object
	* @param	object ref
	* @param	string
	* @return	void
	*/
	private function post_form_element($object, &$goodform, $field)
	{
		// check field value has been posted
		if ($this->input->post($field) !== FALSE)
		{
			// check field exists in dm model
			if(isset($object->has_one[$field]))
			{
				return $this->post_has_one($object, &$goodform, $field);		
			}
			else if(isset($object->has_many[$field]))
			{				
				return $this->post_has_many($object, &$goodform, $field);		
			}
			else if (isset($object->validation[$field]))
			{
				// assign posted value to object field
				$object->{$field} = $this->input->post($field);
				
				return;
			}					
		}
	}

   /**
	* Adds a form element for one of the dm
	* records related records
	*
	* @access	protected
	* @param	object
	* @param	object ref
	* @param	string
	* @return	void
	*/
	protected function post_has_one($object, &$goodform, $field)
	{
		// get posted value
		$value = $this->input->post($field);
		
		// return null if post is empty
		if(empty($value))
			return;


		## Get form field spec ##

		// collect validation array for related object
		$spec = $object->has_one[$field];


		## Create new model instance ##

		$obj = new $spec['class']();

		// load related model by id
		$obj->get_by_id($value);
			
		// if record found return object
		if($obj->exists())
		{
			//log_message('error', 'has one '.$field.' record posted.');
		
			return $obj;
		}
		
		return NULL;
	}

   /**
	* Adds a form element for one of the dm
	* records related records
	*
	* @access	protected
	* @param	object
	* @param	object ref
	* @param	string
	* @return	void
	*/
	protected function post_has_many($object, &$goodform, $field)
	{
		if($this->has_many_posted !== FALSE)
			// set has_many mode to true, if false do not override
			$this->has_many_posted = TRUE;


		// get posted value
		$posted = $this->input->post($field);
		
		// return null if post is empty
		if(empty($posted))
			return;
		

		## Get form field spec ##
		
		// collect validation array for related object
		$spec = $object->has_many[$field];
		
		
		## Get existing records ##
		
		$existing = array();
		
		// relationship already loaded?
		if($object->{$field}->exists())
		{			
			foreach($object->{$field}->all as $o)
			{
				$existing[] = $o->id;
			}
		}
		else
		{
			// load all related models
			$object->{$field}->select('id')->get();
		
			foreach($object->{$field}->all as $o)
			{
				$existing[] = $o->id;
			}
		}
			

		
		// get new records posted to save to object		
		$ids_to_save = array_diff($posted, $existing);

		// get existing records posted to remove from object		
		$ids_to_remove = array_diff($existing, $posted);


		## Instansiate id's to models ##
		
		// array to hold objects to save/remoce
		$relationships = array(
			0 => FALSE,
			1 => FALSE
		);
				
		// make sure save array is not empty
		if(count($ids_to_save) > 0)
		{
			//log_message('error', 'ids_to_save = '.print_r($ids_to_save, TRUE));
			
			$obj_to_save = new $spec['class']();
			
			// get multiple records
			$obj_to_save->where_in('id', $ids_to_save)->get();
		
			// if record found return objects all array
			if($obj_to_save->exists())
			{				
				//log_message('error', count($obj_to_save->all).' has many '.$field.' records posted.');
			
				$relationships[0] = $obj_to_save->all;
			}
		}
	
		// make sure remove array is not empty
		if(count($ids_to_remove) > 0)
		{
			//log_message('error', 'ids_to_remove = '.print_r($ids_to_remove, TRUE));
			
			$obj_to_remove = new $spec['class']();
			
			// get multiple records
			$obj_to_remove->where_in('id', $ids_to_remove)->get();
		
			// if record found return objects all array
			if($obj_to_remove->exists())
			{
				//log_message('error', count($obj_to_remove->all).' has many '.$field.' records not posted.');
				
				$relationships[1] = $obj_to_remove->all;
			}
		}
		
		//log_message('error', 'return  = '.print_r($relationships, TRUE));
		
		return $relationships;
	}
	
   /**
	* Filters a spec array getting rid of attributes not
	* needed by the goodform library
	*
	* @access	protected
	* @param	object ref
	* @param	array
	* @return	array
	*/
	protected function filter_field_spec(&$goodform, $spec)
	{
		// get array of allowed spec attributes
		$allowed = $goodform->config->item('allowed_dm_attributes', 'goodform');
		
		foreach ($spec as $k => $v)
		{
			if(!in_array($k, $allowed))
				// remove unwanted element from validation array
				unset($spec[$k]);
		}
		
		// return filterd array
		return $spec;
	}

   /**
	* converts a dm rule array into a
	* goodform compatible pipe seperated
	* rule string
	*
	* @access	private
	* @param	array
	* @return	string
	*/
	private function convert_rules($spec)
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