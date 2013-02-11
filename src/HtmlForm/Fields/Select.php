<?php

namespace HtmlForm\Fields;

class Select extends Field
{
	/**
	 * Creates a select box form element
	 *
     * @param array $field an array that defines the form element
     * @return string the select box
     */
	public function compile($field)
	{
		
		/* check to see if the options are an associative array
		   this will determine how we handle the input value */
		$isAssoc = \HtmlForm\Utility::isAssoc($field["options"]);
		
		$html = "";
		$value = $this->getValue($field);
		
		$html .= "<select ";
		$html .= "name=\"{$field["name"]}\" ";
		$html .= "{$this->compileAttributes( $field )}>";
		
		
		// handle options in an associative array differently than ones in a numeric array
		if ($isAssoc) {
			
			foreach ($field["options"] as $k => $v) {
				$html .= "<option value=\"{$k}\"";
				if ($value == $k) {
					$html .= " selected=\"selected\"";
				}
				$html .= ">{$v}</option>";
			}
		
		} else {
		
			foreach ($field["options"] as $k => $v) {
				$html .= "<option value=\"{$v}\"";
				if ( $value == $v) {
					$html .= " selected=\"selected\"";
				}
				$html .= ">{$v}</option>";
			}
		}
		
		$html .= "</select>";
		return $html;
	}
}