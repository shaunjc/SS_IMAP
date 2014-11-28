<?php
/**
 * Imap functions moved into class form
 * 
 * Extends DataObject for database support as necessary
 * 
 * @example extend object to save a server/username/password, and call the following to connect
 * $this->imap = $this->open($this->Server, $this->Username, $this->Password);
 * 
 */
class SS_IMAP extends DataObject
{
    /**
     * Storage variable for processing
     *
     * @var Resource $imap IMAP connection resource
     */
    private $imap;
    
    /**
     * Standard imap functions can be called as part of this class object
     * The $imap resource variable is assumed to be omitted for either set
     * or overloaded functions - @use the $this->imap variable instead.
     * 
     * @example $this->imap_expunge, $this->_expunge, $this->imapexpunge,
     * and $this->expunge will all call the function imap_expunge, unless
     * the method has been set or overloaded.
     * 
     * @example $this->delete will call the default Silverstripe usage,
     * while $this->_delete, $this->imapdelete, and $this->imap_delete will
     * attempt to use the native function imap_delete.
     * 
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, $arguments) {
        // Format the method as though it was an imap function
        $imapFunction = "imap_" . preg_replace("/^(imap){0,1}[\_]{0,1}/", "", $method);
        
        // For cases when the function has been set or overloaded manually
        if ($this->hasMethod($imapFunction))
        {
            // Ensure the imap resource is not supplied for internal functions
            if ($arguments && count($arguments) > 0 && @get_resource_type($arguments[0]) == "imap")
            {
                array_shift($arguments);
            }
            return call_user_func_array(array($this, $imapFunction), $arguments);
        }
        
        // For cases when the native function is meant to be used
        if (function_exists($imapFunction))
        {
            // Ensure the first element is the imap resource if defined
            if ($arguments && count($arguments) > 0 && @get_resource_type($arguments[0]) != "imap" && $this->imap)
            {
                array_unshift($arguments, $this->imap);
            }
            return call_user_func_array($imapFunction, $arguments);
        }
        
        // Default to normal silverstripe method handling
        return parent::__call($method, $arguments);
    }
    
    /**
     * Decode the header information for a specific attribute
     * 
     * @param string $attribute
     * @param object|int $headers Message ID can be passed instead
     * of the header object, however, this may increase load times
     * if more than one header value is requested.
     * 
     * @return string
     */
    public function getHeader($attribute, $headers = 0)
    {
        if (!is_object($headers))
        {
            $headers = $this->headerinfo($headers);
        }
        
        $output = '';
        $array = imap_mime_header_decode($headers->$attribute);

        foreach ($array as $obj)
        {
            $output .= rtrim($obj->text, "\t");
        }
        return $output;
    }
    
    /**
     * Replacing imap_headerinfo function with the following
     * imap_rfc822_parse_headers(imap_fetchheader());
     * as suggested on PHP.net
     * @see http://php.net/manual/en/function.imap-headerinfo.php#98809
     * 
     * @param int $message_id
     * @param int $fromlength
     * @param int $subjectlength
     * @param string $defaulthost
     * @return object
     */
    public function imap_headerinfo($message_id, $fromlength = 0, $subjectlength = 0, $defaulthost = null)
    {
        // Collect and fetch headers
        $headers = imap_rfc822_parse_headers($this->fetchheader($message_id), $defaulthost);
        
        // Implement fromlength and subjectlength
        $headers->fetchfrom = substr(implode(", ", $headers->from), 0, $fromlength ? $fromlength : strlen(implode(" ", $headers->from)));
        $headers->fetchsubject = substr((string) $headers->Subject, 0, $subjectlength ? $subjectlength : strlen((string) $headers->Subject));
        
        return $headers;
    }
    
}
