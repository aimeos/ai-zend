<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2011
 * @copyright Aimeos (aimeos.org), 2014
 * @package MW
 * @subpackage Logger
 */


namespace Aimeos\MW\Logger;


/**
 * Log messages using Zend_Log.
 *
 * @package MW
 * @subpackage Logger
 */
class Zend extends \Aimeos\MW\Logger\Base implements \Aimeos\MW\Logger\Iface
{
	private $logger = null;


	/**
	 * Initializes the logger object.
	 *
	 * @param Zend_Log $logger Zend_Log object
	 */
	public function __construct( Zend_Log $logger )
	{
		$this->logger = $logger;
	}


	/**
	 * Writes a message to the configured log facility.
	 *
	 * @param string $message Message text that should be written to the log facility
	 * @param integer $priority Priority of the message for filtering
	 * @param string $facility Facility for logging different types of messages (e.g. message, auth, user, changelog)
	 * @throws \Aimeos\MW\Logger\Exception If an error occurs in Zend_Log
	 * @see \Aimeos\MW\Logger\Base for available log level constants
	 */
	public function log( $message, $priority = \Aimeos\MW\Logger\Base::ERR, $facility = 'message' )
	{
		try
		{
			if( !is_scalar( $message ) ) {
				$message = json_encode( $message );
			}

			$this->logger->log( '<' . $facility . '> ' . $message, $priority );
		}
		catch( Zend_Log_Exception $ze )	{
			throw new \Aimeos\MW\Logger\Exception( $ze->getMessage() );
		}
	}
}