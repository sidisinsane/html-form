<?php

namespace HtmlForm;

class Utility
{
	/**
     * Checks an array to see if it is associative
     * @param  array  $a Array to check
     * @return boolean
     */
	public static function isAssoc($a)
	{
		return (bool) count(array_filter(array_keys($a), "is_string"));
	}
}