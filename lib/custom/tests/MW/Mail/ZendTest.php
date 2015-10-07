<?php

namespace Aimeos\MW\Mail;


/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2013
 * @copyright Aimeos (aimeos.org), 2014
 */
class ZendTest extends \PHPUnit_Framework_TestCase
{
	private $object;
	private $mock;


	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 */
	protected function setUp()
	{
		if( !class_exists( 'Zend_Mail' ) ) {
			$this->markTestSkipped( 'Zend_Mail is not available' );
		}

		$this->mock = $this->getMockBuilder( 'Zend_Mail' )->disableOriginalConstructor()->getMock();
		$this->object = new \Aimeos\MW\Mail\Zend( $this->mock );
	}


	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @access protected
	 */
	protected function tearDown()
	{
	}


	public function testCreateMessage()
	{
		$result = $this->object->createMessage( 'ISO-8859-1' );
		$this->assertInstanceOf( '\\Aimeos\\MW\\Mail\\Message\\Iface', $result );
	}


	public function testSend()
	{
		$this->mock->expects( $this->once() )->method( 'send' );

		$this->object->send( $this->object->createMessage() );
	}
}
