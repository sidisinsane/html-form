<?php

namespace HtmlForm;

class Form extends Abstracts\Addable
{	
	/**
	 * Form configuration
	 * @var array
	 */
	protected $config = array();

	/**
	 * Stores the compiled additional
	 * attributes string.
	 * @var [string
	 */
	protected $compiledAttr;

	/**
	 * Validator object
	 * @var object
	 */
	protected $validator;
	
	/**
	 * Validation errors
	 */
	protected $validationErrors = array();
		
	/**
	 * Sets up the form
	 * @param array $config Associaitve array of configuration overrides
	 */
	public function __construct($config = array())
	{
		$this->setConfig($config);

		$textManipulator = new Utility\TextManipulator();
		$this->compiledAttr = $textManipulator->arrayToTagAttributes($this->config["attr"]);
	}

	/**
	 * Builds the "action" attribute, which defaults to
	 * the current page plus any query sting
	 *
	 * @return string Form action
	 */
	protected function buildAction()
	{
		return $_SERVER["QUERY_STRING"] ? $_SERVER["PHP_SELF"] . "?" . $_SERVER["QUERY_STRING"] : $_SERVER["PHP_SELF"];
	}

	public function setConfig($config)
	{
		$defaults = array(
			"method" => "post",
			"action" => $this->buildAction(),
			"id" => "hfc",
			"repopulate" => true,
			"attr" => array(),
			"beforeElement" => "",
			"afterElement" => ""
		);

		$this->config = array_merge($defaults, $config);
	}

	/**
	 * Fetches the HTML designated to go before
	 * a specific form element.
	 * 
	 * @param  object $element Form element object
	 * @return string The HTML
	 */
	protected function beforeElement($element)
	{
		return $element->beforeElement ? $element->beforeElement : $this->config["beforeElement"];
	}

	/**
	 * Fetches the HTML designated to go after
	 * a specific form element.
	 * 
	 * @param  object $element Form element object
	 * @return string The HTML
	 */
	protected function afterElement($element)
	{
		return $element->afterElement ? $element->afterElement : $this->config["afterElement"];
	}

	public function addHoneypot($args = array())
	{
		$element = new \HtmlForm\Elements\Honeypot(sha1($this->config["id"]), "Do not enter content here", $args);
		$this->elements[] = $element;

		return $this;
	}
	
    /**
     * Checks the validity of the form and enables
     * form field repopulating
     * 
     * @return boolean TRUE if form is valid; FALSE if there are errors
     */
	public function isValid()
	{
		$this->saveToSession();
		$this->validator = new \HtmlForm\Utility\Validator($this->elements);
		$this->validationErrors = $this->validator->validate();

		if (!empty($this->validationErrors)) {
			return false;
		} else {
			return true;
		}
	}

	public function passedHoneypot()
	{
		return !$this->validator->honeypotError;
	}

	public function setErrorMessage($message)
	{
		$this->validationErrors[] = $message;
	}

	/**
	 * Saves form data to the session.
	 * 
	 * @return null
	 */
	protected function saveToSession()
	{	
		if ($this->config["repopulate"]) {
			$data = strtolower($_SERVER["REQUEST_METHOD"]) == "post" ? $_POST : $_GET;
			
			foreach ($data as $k => $v) {
				$_SESSION[$this->config["id"]][$k] = $v;
			}
		}
	}

	/**
     * Gets the current value attribute of a form element
     * @param  object $name 	Form element object
     * @return string 			The form element's current value
     */
	protected function getValue($element)
	{	
		$name = $element->name;

		if (isset($_SESSION[$this->config["id"]][$name])) {
			return $this->cleanValue($_SESSION[$this->config["id"]][$name] );
			
		} else if (isset($_POST[$name])) {
			return $this->cleanValue($_POST[$name]);
		
		} else {	
			return $this->cleanValue($element->defaultValue);
		}
	}

	protected function cleanValue($value)
	{
		if (is_array($value)) {
			$a = array();
			foreach ($value as $v) {
				$a[] = stripslashes($v);
			}
			return $a;
		} else {
			return stripslashes($value);
		}
	}
	
	/**
	 * Renders the HTML form
	 * 
     * @return null
     */
	public function render()
	{	
		echo $this->compileForm();
	}

	public function compileForm()
	{
		$html = "";
		$html .= $this->compileErrors();
		$html .= "<form novalidate=\"novalidate\" method=\"{$this->config["method"]}\" action=\"{$this->config["action"]}\" id=\"{$this->config["id"]}\" {$this->compiledAttr}>";
		$html .= $this->renderElements();		
		$html .= "</form>";

		return $html;
	}

	/**
	 * Compile error message HTML
	 * 
	 * @return string HTML error div
	 */
	protected function compileErrors()
	{
		if (!empty($this->validationErrors)) {
				
			$html = "";
			
			$count = count($this->validationErrors);
			$message = $count > 1 ? "The following {$count} errors were found:" : "The following error was found:";
			
			$html .= "<div class=\"alert alert-error {$this->config["id"]}\">";
			$html .= "<p class=\"alert-heading\">{$message}</p>";
			$html .= "<ul>";
			
			foreach ($this->validationErrors as $k => $v) {
				$html .= "<li>{$v}</li>";
			}
			
			$html .= "</ul></div>";
			return $html;
		}
	}

	/**
	 * Compiles HTML for each form element
	 * 
	 * @return string HTML of form elements
	 */
	protected function renderElements()
	{
		$html = "";
		foreach ($this->elements as $element) {
			$value = $this->getValue($element);
			$html .= $this->beforeElement($element);
			$html .= $element->compile($value);
			$html .= $this->afterElement($element);
		}
		return $html;
	}	
}