<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2014-2018
 * @package MW
 * @subpackage Translation
 */


namespace Aimeos\MW\Translation;


/**
 * Translation using \Zend_Translate
 *
 * @package MW
 * @subpackage Translation
 */
class Zend
	extends \Aimeos\MW\Translation\Base
	implements \Aimeos\MW\Translation\Iface
{
	private $options;
	private $translationSources;
	private $translations = [];


	/**
	 * Initializes the translation object using \Zend_Translate.
	 * This implementation only accepts files as source for the \Zend_Translate_Adapter.
	 *
	 * @param array $translationSources Associative list of translation domains and lists of translation directories.
	 * 	Translations from the first file aren't overwritten by the later ones
	 * as key and the directory where the translation files are located as value.
	 * @param string $adapter Name of the Zend translation adapter
	 * @param string $locale ISO language name, like "en" or "en_US"
	 * @param string $options Associative array containing additional options for \Zend_Translate
	 *
	 * @link http://framework.zend.com/manual/1.11/en/zend.translate.adapter.html
	 */
	public function __construct( array $translationSources, $adapter, $locale, array $options = [] )
	{
		parent::__construct( $locale );

		$this->options = $options;
		$this->options['adapter'] = (string) $adapter;
		$this->options['locale'] = (string) $locale;
		$this->translationSources = $translationSources;
	}


	/**
	 * Returns the translated string for the given domain.
	 *
	 * @param string $domain Translation domain
	 * @param string $string String to be translated
	 * @return string The translated string
	 * @throws \Aimeos\MW\Translation\Exception Throws exception on initialization of the translation
	 */
	public function dt( $domain, $string )
	{
		try
		{
			foreach( $this->getTranslations( $domain ) as $object )
			{
				if( $object->isTranslated( $string ) === true ) {
					return $object->translate( $string, $this->getLocale() );
				}
			}
		}
		catch( \Exception $e ) { ; } // Discard exceptions, return the original string instead

		return (string) $string;
	}


	/**
	 * Returns the translated singular or plural form of the string depending on the given number.
	 *
	 * @param string $domain Translation domain
	 * @param string $singular String in singular form
	 * @param string $plural String in plural form
	 * @param integer $number Quantity to choose the correct plural form for languages with plural forms
	 * @return string Returns the translated singular or plural form of the string depending on the given number
	 * @throws \Aimeos\MW\Translation\Exception Throws exception on initialization of the translation
	 *
	 * @link http://framework.zend.com/manual/en/zend.translate.plurals.html
	 */
	public function dn( $domain, $singular, $plural, $number )
	{
		try
		{
			foreach( $this->getTranslations( $domain ) as $object )
			{
				if( $object->isTranslated( $singular ) === true ) {
					return $object->plural( $singular, $plural, $number, $this->getLocale() );
				}
			}
		}
		catch( \Exception $e ) { ; } // Discard exceptions, return the original string instead

		if( $this->getPluralIndex( $number, $this->getLocale() ) > 0 ) {
			return (string) $plural;
		}

		return (string) $singular;
	}


	/**
	 * Returns all locale string of the given domain.
	 *
	 * @param string $domain Translation domain
	 * @return array Associative list with original string as key and associative list with index => translation as value
	 */
	public function getAll( $domain )
	{
		$messages = [];

		foreach( $this->getTranslations( $domain ) as $object ) {
			$messages = $messages + $object->getMessages();
		}

		return $messages;
	}


	/**
	 * Returns the initialized Zend translation object which contains the translations.
	 *
	 * @param string $domain Translation domain
	 * @return array List of translation objects implementing \Zend_Translate
	 * @throws \Aimeos\MW\Translation\Exception If initialization fails
	 */
	protected function getTranslations( $domain )
	{
		if( !isset( $this->translations[$domain] ) )
		{
			if ( !isset( $this->translationSources[$domain] ) )
			{
				$msg = sprintf( 'No translation directory for domain "%1$s" available', $domain );
				throw new \Aimeos\MW\Translation\Exception( $msg );
			}

			// Reverse locations so the former gets not overwritten by the later
			$locations = array_reverse( $this->getTranslationFileLocations( $this->translationSources[$domain], $this->getLocale() ) );
			$options = $this->options;

			foreach( $locations as $location )
			{
				$options['content'] = $location;
				$this->translations[$domain][] = new \Zend_Translate( $options );
			}
		}

		return ( isset( $this->translations[$domain] ) ? $this->translations[$domain] : [] );
	}
}
