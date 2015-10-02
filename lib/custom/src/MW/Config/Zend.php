<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2011
 * @copyright Aimeos (aimeos.org), 2014
 * @package MW
 * @subpackage Config
 */


/**
 * Configuration setting class using Zend_Config.
 *
 * @package MW
 * @subpackage Config
 */
class MW_Config_Zend
	extends MW_Config_Abstract
	implements MW_Config_Interface
{
	private $config;
	private $paths;


	/**
	 * Initialize config object with Zend_Config instance.
	 *
	 * @param Zend_Config $config Configuration object
	 * @param array|string $path Filesystem path or list of paths to the configuration files
	 */
	public function __construct( Zend_Config $config, $path = array() )
	{
		$this->config = $config;
		$this->paths = (array) $path;
	}


	/**
	 * Clones the objects inside.
	 */
	public function __clone()
	{
		$this->config = clone $this->config;
	}


	/**
	 * Returns the value of the requested config key.
	 *
	 * @param string $path Path to the requested value like tree/node/classname
	 * @param mixed $default Value returned if requested key isn't found
	 * @return mixed Value associated to the requested key or default value if no value in configuration was found
	 */
	public function get( $path, $default = null )
	{
		$parts = explode( '/', trim( $path, '/' ) );

		if( ( $value = $this->getPart( $this->config, $parts ) ) !== null ) {
			return $value;
		}

		foreach( $this->paths as $fspath ) {
			$this->load( $this->config, $fspath, $parts );
		}

		if( ( $value = $this->getPart( $this->config, $parts ) ) !== null ) {
			return $value;
		}

		return $default;
	}


	/**
	 * Sets the value for the specified key.
	 *
	 * @param string $path Path to the requested value like tree/node/classname
	 * @param mixed $value Value that should be associated with the given path
	 */
	public function set( $path, $value )
	{
		$classname = 'Zend_Config';
		$parts = explode( '/', trim( $path, '/' ) );

		$config = $this->config;
		$max = count( $parts ) - 1;

		for( $i = 0; $i < $max; $i++ )
		{
			$val = $config->get( $parts[$i] );

			if( $val instanceof $classname ) {
				$config = $val;
			} else {
				$config = $config->{$parts[$i]} = new Zend_Config( array(), true );
			}
		}

		$config->{$parts[$max]} = $value;
	}


	/**
	 * Descents into the configuration specified by the given path and returns the value if found.
	 *
	 * @param Zend_Config $config Configuration object which should contain the loaded configuration
	 * @param array $parts List of config name parts to look for
	 * @return mixed Found value or null if no value is available
	 */
	protected function getPart( Zend_Config $config, array $parts )
	{
		$classname = 'Zend_Config';

		if( ( $key = array_shift( $parts ) ) !== null && isset( $config->$key ) )
		{
			if( $config->$key instanceof $classname )
			{
				if( count( $parts  ) > 0 ) {
					return $this->getPart( $config->$key, $parts );
				}

				return $config->$key->toArray();
			}

			return $config->$key;
		}

		return null;
	}


	/**
	 * Loads the configuration files when found.
	 *
	 * @param Zend_Config $config Configuration object which should contain the loaded configuration
	 * @param string $path Path to the configuration directory
	 * @param array $parts List of config name parts to look for
	 */
	protected function load( Zend_Config $config, $path, array $parts )
	{
		if( ( $key = array_shift( $parts ) ) !== null )
		{
			$newPath = $path . DIRECTORY_SEPARATOR . $key;

			if( is_dir( $newPath ) )
			{
				if( !isset( $config->$key ) ) {
					$config->$key = new Zend_Config( array(), true );
				}

				$this->load( $config->$key, $newPath, $parts );
			}

			if( file_exists( $newPath . '.php' ) )
			{
				if( !isset( $config->$key ) ) {
					$config->$key = new Zend_Config( array(), true );
				}

				$config->$key->merge( new Zend_Config( $this->includeFile( $newPath . '.php' ), true ) );
			}
		}
	}
}