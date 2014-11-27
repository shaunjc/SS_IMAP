SS_IMAP
=======

Silverstripe IMAP class for extending imap functions

<h2>Installation:</h2>

Simply copy the master folder to the base directory of your Silverstripe install, rename it to something appropriate, such as <em>ss_imap</em>, and perform a <em>/dev/build/?flush=all</em>.

<h2>Usage:</h2>

Extend the SS_IMAP class with class of your own, and add or extend functions to take advantage of the native imap functions.

<pre>
&lt;?php
class EmailScanner extends SS_IMAP
{
  public $db = array(
    'Username' => 'Varchar(255)',
    'Password' => 'Varchar(255)',
    'Server' => 'Varchar(255)'
  );
  
  public function connect()
  {
    $this->imap = $this->open('{'.$this->Server.'}',$this->Username,$this->Password);
  }
}
</pre>
