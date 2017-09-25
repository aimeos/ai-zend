<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2011
 * @copyright Aimeos (aimeos.org), 2014-2017
 */


namespace Aimeos\MW\Logger;


/**
 * Test class for \Aimeos\MW\Logger\Zend.
 */
class ZendTest extends \PHPUnit\Framework\TestCase
{
	private $object;


	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 */
	protected function setUp()
	{
		if( class_exists( 'Zend_Log' ) === false ) {
			$this->markTestSkipped( 'Class \Zend_Log not found' );
		}

		$writer = new \Zend_Log_Writer_Stream( 'error.log' );

		$formatter = new \Zend_Log_Formatter_Simple( 'log: %message%' . PHP_EOL );
		$writer->setFormatter( $formatter );

		$logger = new \Zend_Log( $writer );

		$filter = new \Zend_Log_Filter_Priority( \Zend_Log::INFO );
		$logger->addFilter( $filter );

		$this->object = new \Aimeos\MW\Logger\Zend( $logger );
	}


	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @access protected
	 */
	protected function tearDown()
	{
		unlink( 'error.log' );
	}


	public function testLog()
	{
		$this->object->log( 'error' );
		$this->assertEquals( 'log: <message> error' . PHP_EOL, file_get_contents( 'error.log' ) );
	}


	public function testNonScalarLog()
	{
		$this->object->log( array ('error', 'error2', 2) );
		$this->assertEquals( 'log: <message> ["error","error2",2]' . PHP_EOL, file_get_contents( 'error.log' ) );
	}


	public function testLogDebug()
	{
		$this->object->log( 'debug', \Aimeos\MW\Logger\Base::DEBUG );
		$this->assertEquals( '', file_get_contents( 'error.log' ) );
	}


	public function testBadPriority()
	{
		$this->setExpectedException('\\Aimeos\\MW\\Logger\\Exception');
		$this->object->log( 'error', -1 );
	}
}
