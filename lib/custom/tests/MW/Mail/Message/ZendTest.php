<?php

namespace Aimeos\MW\Mail\Message;


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
		$this->object = new \Aimeos\MW\Mail\Message\Zend( $this->mock );
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


	public function testAddFrom()
	{
		$this->mock->expects( $this->once() )->method( 'setFrom' )
			->with( $this->stringContains( 'a@b' ), $this->stringContains( 'test' ) );

		$result = $this->object->addFrom( 'a@b', 'test' );
		$this->assertSame( $this->object, $result );
	}


	public function testAddTo()
	{
		$this->mock->expects( $this->once() )->method( 'addTo' )
			->with( $this->stringContains( 'a@b' ), $this->stringContains( 'test' ) );

		$result = $this->object->addTo( 'a@b', 'test' );
		$this->assertSame( $this->object, $result );
	}


	public function testAddCc()
	{
		$this->mock->expects( $this->once() )->method( 'addCc' )
			->with( $this->stringContains( 'a@b' ), $this->stringContains( 'test' ) );

		$result = $this->object->addCc( 'a@b', 'test' );
		$this->assertSame( $this->object, $result );
	}


	public function testAddBcc()
	{
		$this->mock->expects( $this->once() )->method( 'addBcc' )
			->with( $this->stringContains( 'a@b' ), $this->stringContains( 'test' ) );

		$result = $this->object->addBcc( 'a@b', 'test' );
		$this->assertSame( $this->object, $result );
	}


	public function testAddReplyTo()
	{
		$this->mock->expects( $this->once() )->method( 'setReplyTo' )
			->with( $this->stringContains( 'a@b' ), $this->stringContains( 'test' ) );

		$result = $this->object->addReplyTo( 'a@b', 'test' );
		$this->assertSame( $this->object, $result );
	}


	public function testAddHeader()
	{
		$this->mock->expects( $this->once() )->method( 'addHeader' )
			->with( $this->stringContains( 'test' ), $this->stringContains( 'value' ) );

		$result = $this->object->addHeader( 'test', 'value' );
		$this->assertSame( $this->object, $result );
	}


	public function testSetSender()
	{
		$this->mock->expects( $this->once() )->method( 'setFrom' )
			->with( $this->stringContains( 'a@b' ), $this->stringContains( 'test' ) );

		$result = $this->object->setSender( 'a@b', 'test' );
		$this->assertSame( $this->object, $result );
	}


	public function testSetSubject()
	{
		$this->mock->expects( $this->once() )->method( 'setSubject' )
			->with( $this->stringContains( 'test' ) );

		$result = $this->object->setSubject( 'test' );
		$this->assertSame( $this->object, $result );
	}


	public function testSetBody()
	{
		$this->mock->expects( $this->once() )->method( 'setBodyText' )
			->with( $this->stringContains( 'test' ) );

		$result = $this->object->setBody( 'test' );
		$this->assertSame( $this->object, $result );
	}


	public function testSetBodyHtml()
	{
		$result = $this->object->setBodyHtml( 'test' );
		$this->object->getObject();

		$this->assertSame( $this->object, $result );
	}


	public function testAddAttachment()
	{
		$partMock = $this->getMockBuilder( 'Zend_Mime_Part' )->disableOriginalConstructor()->getMock();

		$this->mock->expects( $this->once() )->method( 'createAttachment' )
			->with( $this->stringContains( 'test' ), $this->stringContains( 'text/plain' ),
				$this->stringContains( 'inline' ), $this->stringContains( Zend_Mime::ENCODING_BASE64 ),
				$this->stringContains( 'test.txt' ) )
			->will( $this->returnValue( $partMock ) );

		$result = $this->object->addAttachment( 'test', 'text/plain', 'test.txt', 'inline' );
		$this->assertSame( $this->object, $result );
	}


	public function testEmbedAttachment()
	{
		$this->mock->expects( $this->once() )->method( 'getBodyHtml' )
			->will( $this->returnValue( new stdClass() ) );

		$result = $this->object->embedAttachment( 'test', 'text/plain', 'test.txt' );
		$this->object->getObject();

		$this->assertInternalType( 'string', $result );
	}


	public function testEmbedAttachmentMultiple()
	{
		$object = new \Aimeos\MW\Mail\Message\Zend( new Zend_Mail() );

		$object->setBody( 'text body' );
		$object->embedAttachment( 'test', 'text/plain', 'test.txt' );
		$object->embedAttachment( 'test', 'text/plain', 'test.txt' );

		$transport = new Test_Zend_Mail_Transport_Memory();
		$object->getObject()->send( $transport );

		$exp = '#Content-Disposition: inline; filename="test.txt".*Content-Disposition: inline; filename="1_test.txt"#smu';
		$this->assertRegExp( $exp, $transport->message );
	}


	public function testGetObject()
	{
		$this->assertInstanceOf( 'Zend_Mail', $this->object->getObject() );
	}


	public function testGenerateMailAlternative()
	{
		$object = new \Aimeos\MW\Mail\Message\Zend( new Zend_Mail() );

		$object->setBody( 'text body' );
		$object->setBodyHtml( 'html body' );

		$transport = new Test_Zend_Mail_Transport_Memory();
		$object->getObject()->send( $transport );

		$exp = '#Content-Type: multipart/alternative;.*Content-Type: text/plain;.*Content-Type: text/html;#smu';
		$this->assertRegExp( $exp, $transport->message );
	}


	public function testGenerateMailRelated()
	{
		$object = new \Aimeos\MW\Mail\Message\Zend( new Zend_Mail() );

		$object->embedAttachment( 'embedded-data', 'text/plain', 'embedded.txt' );
		$object->setBodyHtml( 'html body' );

		$transport = new Test_Zend_Mail_Transport_Memory();
		$object->getObject()->send( $transport );

		$exp = '#Content-Type: multipart/related.*Content-Type: text/html;.*Content-Type: text/plain#smu';
		$this->assertRegExp( $exp, $transport->message );
	}


	public function testGenerateMailFull()
	{
		$object = new \Aimeos\MW\Mail\Message\Zend( new Zend_Mail() );

		$object->addAttachment( 'attached-data', 'text/plain', 'attached.txt' );
		$object->embedAttachment( 'embedded-data', 'text/plain', 'embedded.txt' );
		$object->setBodyHtml( 'html body' );
		$object->setBody( 'text body' );

		$transport = new Test_Zend_Mail_Transport_Memory();
		$object->getObject()->send( $transport );

		$exp = '#Content-Type: multipart/mixed;.*Content-Type: multipart/alternative;.*Content-Type: text/plain;.*Content-Type: multipart/related.*Content-Type: text/html;.*Content-Type: text/plain.*Content-Type: text/plain#smu';
		$this->assertRegExp( $exp, $transport->message );
	}
}



if( !class_exists( 'Zend_Mail_Transport_Base' ) ) {
	return;
}

class Test_Zend_Mail_Transport_Memory extends Zend_Mail_Transport_Base
{
	public $message;

	protected function _sendMail()
	{
		$this->message = $this->header . "\r\n" . $this->body;
	}
}
