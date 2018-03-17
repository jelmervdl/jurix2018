<?php

class Form
{
	public $fields = array();

	public function add(FormField $field)
	{
		$this->fields[$field->name()] = $field;
	}

	public function __get($field_name)
	{
		return $this->fields[$field_name];
	}

	public function submitted()
	{
		return $_SERVER['REQUEST_METHOD'] == 'POST';
	}

	public function validate()
	{
		$errors = array();

		foreach ($this->fields as $name => $field) {
			$error = $field->validate();

			if ($error !== null)
				$errors[$name] = $error;
		}

		return $errors;
	}

	public function data()
	{
		$data = array();

		foreach ($this->fields as $name => $field)
			$data[$name] = $field->value();
		
		return $data;
	}
}

abstract class FormField
{
	protected $attributes = array();

	public function __construct($name, $label, array $attributes = array())
	{
		$this->name = $name;

		$this->label = $label;

		$this->attributes = array_merge($attributes, array('name' => $name, 'id' => 'field-' . $name));
	}

	public function name()
	{
		return $this->name;
	}

	public function value()
	{
		return isset($_POST[$this->name]) ? trim($_POST[$this->name]) : null;
	}

	public function validate()
	{
		if (!empty($this->attributes['required']) && $this->value() == '')
			return 'This field should not be left empty';

		return null;
	}

	abstract public function render(array &$errors = array());

	protected function render_attributes(array $attributes)
	{
		$pairs = array();

		foreach ($attributes as $prop => $value)
			$pairs[] = ($value === true)
				? $prop // HTML5 name only attributes, e.g. 'required' and 'checked'
				: sprintf('%s="%s"', $prop, htmlspecialchars($value, ENT_QUOTES));

		return implode(' ', $pairs);
	}

	protected function render_group($field, &$error = null)
	{
		$label = sprintf('<label for="%s">%s%s</label>',
			$this->attributes['id'],
			$this->label,
			strlen($this->label) > 0 ? ':' : '');

		$hint = !empty($error)
			? sprintf('<p class="hint">%s</p>', htmlspecialchars($error))
			: '';

		return sprintf('<div class="form-group%s">%s%s%s</div>',
			!empty($this->attributes['required']) ? ' required' : '',
			$label,
			$field,
			$hint);	
	}
}

class FormTextField extends FormField 
{	
	protected $default_attributes = array(
		'type' => 'text'
	);

	public function render(array &$errors = null)
	{
		$attributes = array_merge($this->default_attributes, $this->attributes);

		if ($this->value() !== null)
			$attributes = array_merge($attributes, array('value' => $this->value()));

		$field = sprintf('<input %s>', $this->render_attributes($attributes));

		return $this->render_group($field, $errors[$this->name]);
	}
}

class FormEmailField extends FormTextField
{
	protected $default_attributes = array(
		'type' => 'email'
	);

	public function validate()
	{
		if (filter_var($this->value(), FILTER_VALIDATE_EMAIL) === false)
			return 'This email address does not seem to be valid';

		return parent::validate();
	}
}

class FormSelectField extends FormField
{
	public function __construct($name, $label, array $options, array $attributes = array())
	{
		parent::__construct($name, $label, $attributes);

		$this->options = $options;
	}

	public function render(array &$errors = array())
	{
		$html_options = array();

		foreach ($this->options as $option_value => $option_label)
			$html_options[] = sprintf('<option%s value="%s">%s</option>',
				$this->value() == $option_value ? ' selected' : '',
				htmlspecialchars($option_value, ENT_QUOTES),
				$option_label);

		$field = sprintf('<select %s>%s</select>', $this->render_attributes($this->attributes), implode("\n\t", $html_options));

		return $this->render_group($field, $errors[$this->name]);
	}
}

class FormRadioField extends FormField
{
	public function __construct($name, $label, array $options, array $attributes = array())
	{
		parent::__construct($name, $label, $attributes);
		unset($this->attributes['name']);
		$this->options = $options;
	}

	public function render(array &$errors = array())
	{
		$html_options = array();

		foreach ($this->options as $option_value => $option_label)
			$html_options[] = sprintf('<li><label class="option"><input type="radio" name="%s" value="%s"%s> %s</label></li>',
				htmlspecialchars($this->name, ENT_QUOTES),
				htmlspecialchars($option_value, ENT_QUOTES),
				$this->value() == $option_value ? ' checked' : '',
				$option_label);

		$field = sprintf('<ul %s>%s</ul>', $this->render_attributes($this->attributes), implode("\n\t", $html_options));

		return $this->render_group($field, $errors[$this->name]);
	}

	protected function render_group($field, &$error = null)
	{
		$label = sprintf('<label>%s%s</label>',
			$this->label,
			strlen($this->label) > 0 ? ':' : '');

		$hint = !empty($error)
			? sprintf('<p class="hint">%s</p>', htmlspecialchars($error))
			: '';

		return sprintf('<div class="form-group choice%s">%s%s%s</div>',
			!empty($this->attributes['required']) ? ' required' : '',
			$label,
			$field,
			$hint);	
	}
}

class FormCheckboxField extends FormField
{
	protected $default_attributes = array(
		'type' => 'checkbox',
		'value' => 'yes'
	);

	public function render(array &$errors = null)
	{
		$attributes = array_merge($this->default_attributes, $this->attributes);

		if ($this->value() !== null)
			$attributes = array_merge($attributes, array('checked' => 'checked'));

		$field = sprintf('<label class="option"><input %s> %s</label>',
			$this->render_attributes($attributes), $this->label);

		return $this->render_group($field, $errors[$this->name]);
	}

	protected function render_group($field, &$error = null)
	{
		$hint = !empty($error)
			? sprintf('<p class="hint">%s</p>', htmlspecialchars($error))
			: '';

		return sprintf('<div class="form-group checkbox %s">%s%s</div>',
			!empty($this->attributes['required']) ? ' required' : '',
			$field,
			$hint);
	}
}