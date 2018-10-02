<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2013
 * @copyright Aimeos (aimeos.org), 2014-2018
 * @package MW
 * @subpackage Mail
 */


namespace Aimeos\MW\Mail;


/**
 * Zend implementation for creating and sending e-mails.
 *
 * @package MW
 * @subpackage Mail
 */
class Zend implements \Aimeos\MW\Mail\Iface
{
	private $object;
	private $transport;


	/**
	 * Initializes the instance of the class.
	 *
	 * @param \Zend_Mail $object Zend mail object
	 * @param \Zend_Mail_Transport_Base|null $transport Mail transport object
	 */
	public function __construct( \Zend_Mail $object, \Zend_Mail_Transport_Base $transport = null )
	{
		$this->object = $object;
		$this->transport = $transport;
	}


	/**
	 * Creates a new e-mail message object.
	 *
	 * @param string $charset Default charset of the message
	 * @return \Aimeos\MW\Mail\Message\Iface E-mail message object
	 */
	public function createMessage( $charset = 'UTF-8' )
	{
		return new \Aimeos\MW\Mail\Message\Zend( clone $this->object );
	}


	/**
	 * Sends the e-mail message to the mail server.
	 *
	 * @param \Aimeos\MW\Mail\Message\Iface $message E-mail message object
	 */
	public function send( \Aimeos\MW\Mail\Message\Iface $message )
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
