<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2013
 * @copyright Aimeos (aimeos.org), 2014
 * @package MW
 * @subpackage Mail
 */


/**
 * Zend implementation for creating and sending e-mails.
 *
 * @package MW
 * @subpackage Mail
 */
class MW_Mail_Zend implements MW_Mail_Interface
{
	private $object;
	private $transport;


	/**
	 * Initializes the instance of the class.
	 *
	 * @param Zend_Mail $object Zend mail object
	 * @param Zend_Mail_Transport_Abstract|null $transport Mail transport object
	 */
	public function __construct( Zend_Mail $object, Zend_Mail_Transport_Abstract $transport = null )
	{
		$this->object = $object;
		$this->transport = $transport;
	}


	/**
	 * Creates a new e-mail message object.
	 *
	 * @param string $charset Default charset of the message
	 * @return MW_Mail_Message_Interface E-mail message object
	 */
	public function createMessage( $charset = 'UTF-8' )
	{
		return new MW_Mail_Message_Zend( clone $this->object );
	}


	/**
	 * Sends the e-mail message to the mail server.
	 *
	 * @param MW_Mail_Message_Interface $message E-mail message object
	 */
	public function send( MW_Mail_Message_Interface $message )
	{
		$message->getObject()->send( $this->transport );
	}


	/**
	 * Clones the internal objects.
	 */
	public function __clone()
	{
		$this->object = clone $this->object;
		$this->transport = ( isset( $this->transport ) ? clone $this->transport : null );
	}
}
