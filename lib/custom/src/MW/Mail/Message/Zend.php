<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2013
 * @copyright Aimeos (aimeos.org), 2014
 * @package MW
 * @subpackage Mail
 */


/**
 * Zend implementation for creating e-mails.
 *
 * @package MW
 * @subpackage Mail
 */
class MW_Mail_Message_Zend implements MW_Mail_Message_Interface
{
	private $_object;
	private $_embedded = array();
	private $_html;


	/**
	 * Initializes the message instance.
	 *
	 * @param Zend_Mail $object Zend mail object
	 */
	public function __construct( Zend_Mail $object )
	{
		$this->_object = $object;
	}


	/**
	 * Adds a source e-mail address of the message.
	 *
	 * @param string $email Source e-mail address
	 * @param string|null $name Name of the user sending the e-mail or null for no name
	 * @return MW_Mail_Message_Interface Message object
	 */
	public function addFrom( $email, $name = null )
	{
		$this->_object->setFrom( $email, $name );
		return $this;
	}


	/**
	 * Adds a destination e-mail address of the target user mailbox.
	 *
	 * @param string $email Destination address of the target mailbox
	 * @param string|null $name Name of the user owning the target mailbox or null for no name
	 * @return MW_Mail_Message_Interface Message object
	 */
	public function addTo( $email, $name = null )
	{
		$this->_object->addTo( $email, $name );
		return $this;
	}


	/**
	 * Adds a destination e-mail address for a copy of the message.
	 *
	 * @param string $email Destination address for a copy
	 * @param string|null $name Name of the user owning the target mailbox or null for no name
	 * @return MW_Mail_Message_Interface Message object
	 */
	public function addCc( $email, $name = null )
	{
		$this->_object->addCc( $email, $name );
		return $this;
	}


	/**
	 * Adds a destination e-mail address for a hidden copy of the message.
	 *
	 * @param string $email Destination address for a hidden copy
	 * @param string|null $name Name of the user owning the target mailbox or null for no name
	 * @return MW_Mail_Message_Interface Message object
	 */
	public function addBcc( $email, $name = null )
	{
		$this->_object->addBcc( $email, $name );
		return $this;
	}


	/**
	 * Adds the return e-mail address for the message.
	 *
	 * @param string $email E-mail address which should receive all replies
	 * @param string|null $name Name of the user which should receive all replies or null for no name
	 * @return MW_Mail_Message_Interface Message object
	 */
	public function addReplyTo( $email, $name = null )
	{
		$this->_object->setReplyTo( $email, $name );
		return $this;
	}


	/**
	 * Adds a custom header to the message.
	 *
	 * @param string $name Name of the custom e-mail header
	 * @param string $value Text content of the custom e-mail header
	 * @return MW_Mail_Message_Interface Message object
	 */
	public function addHeader( $name, $value )
	{
		$this->_object->addHeader( $name, $value );
		return $this;
	}


	/**
	 * Sets the e-mail address and name of the sender of the message (higher precedence than "From").
	 *
	 * @param string $email Source e-mail address
	 * @param string|null $name Name of the user who sent the message or null for no name
	 * @return MW_Mail_Message_Interface Message object
	 */
	public function setSender( $email, $name = null )
	{
		$this->_object->setFrom( $email, $name );
		return $this;
	}


	/**
	 * Sets the subject of the message.
	 *
	 * @param string $subject Subject of the message
	 * @return MW_Mail_Message_Interface Message object
	 */
	public function setSubject( $subject )
	{
		$this->_object->setSubject( $subject );
		return $this;
	}


	/**
	 * Sets the text body of the message.
	 *
	 * @param string $message Text body of the message
	 * @return MW_Mail_Message_Interface Message object
	 */
	public function setBody( $message )
	{
		$this->_object->setBodyText( $message );
		return $this;
	}


	/**
	 * Sets the HTML body of the message.
	 *
	 * @param string $message HTML body of the message
	 * @return MW_Mail_Message_Interface Message object
	 */
	public function setBodyHtml( $message )
	{
		$this->_html = $message;
		return $this;
	}


	/**
	 * Adds an attachment to the message.
	 *
	 * @param string $data Binary or string
	 * @param string $mimetype Mime type of the attachment (e.g. "text/plain", "application/octet-stream", etc.)
	 * @param string|null $filename Name of the attached file (or null if inline disposition is used)
	 * @param string $disposition Type of the disposition ("attachment" or "inline")
	 * @return MW_Mail_Message_Interface Message object
	 */
	public function addAttachment( $data, $mimetype, $filename, $disposition = 'attachment' )
	{
		$enc = Zend_Mime::ENCODING_BASE64;
		$part = $this->_object->createAttachment( $data, $mimetype, $disposition, $enc, $filename );

		return $this;
	}


	/**
	 * Embeds an attachment into the message and returns its reference.
	 *
	 * @param string $data Binary or string
	 * @param string $mimetype Mime type of the attachment (e.g. "text/plain", "application/octet-stream", etc.)
	 * @param string|null $filename Name of the attached file
	 * @return string Content ID for referencing the attachment in the HTML body
	 */
	public function embedAttachment( $data, $mimetype, $filename )
	{
		$cnt = 0;
		$newfile = $filename;

		while( isset( $this->_embedded[$newfile] ) ) {
			$newfile = ++$cnt . '_' . $filename;
		}

		$part = new Zend_Mime_Part( $data );

		$part->disposition = Zend_Mime::DISPOSITION_INLINE;
		$part->encoding = Zend_Mime::ENCODING_BASE64;
		$part->filename = $newfile;
		$part->type = $mimetype;
		$part->id = md5( $newfile . mt_rand() );

		$this->_embedded[$newfile] = $part;

		return 'cid:' . $part->id;
	}


	/**
	 * Returns the internal Zend mail object.
	 *
	 * @return Zend_Mail Zend mail object
	 */
	public function getObject()
	{
		if( !empty( $this->_embedded ) )
		{
			$parts = array();

			if( $this->_html != null )
			{
				$part = new Zend_Mime_Part( $this->_html );

				$part->charset = $this->_object->getCharset();
				$part->encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE;
				$part->disposition = Zend_Mime::DISPOSITION_INLINE;
				$part->type = Zend_Mime::TYPE_HTML;

				$parts = array( $part );
			}

			$msg = new Zend_Mime_Message();
			$msg->setParts( array_merge( $parts, $this->_embedded ) );

			// create html body (text and maybe embedded), modified afterwards to set it to multipart/related
			$this->_object->setBodyHtml( $msg->generateMessage() );

			$related = $this->_object->getBodyHtml();
			$related->type = Zend_Mime::MULTIPART_RELATED;
			$related->encoding = Zend_Mime::ENCODING_8BIT;
			$related->boundary = $msg->getMime()->boundary();
			$related->disposition = null;
			$related->charset = null;
		}
		else if( $this->_html != null )
		{
			$this->_object->setBodyHtml( $this->_html );
		}

		return $this->_object;
	}


	/**
	 * Clones the internal objects.
	 */
	public function __clone()
	{
		$this->_object = clone $this->_object;
	}
}
