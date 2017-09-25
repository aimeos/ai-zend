<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2014-2017
 * @package MW
 * @subpackage View
 */


namespace Aimeos\MW\View\Helper\Url;


/**
 * View helper class for building URLs using Zend Router.
 *
 * @package MW
 * @subpackage View
 */
class Zend
	extends \Aimeos\MW\View\Helper\Base
	implements \Aimeos\MW\View\Helper\Iface
{
	private $router;
	private $serverUrl;


	/**
	 * Initializes the URL view helper.
	 *
	 * @param \Aimeos\MW\View\Iface $view View instance with registered view helpers
	 * @param \Zend_Controller_Router_Interface $router Zend Router implementation
	 * @param string $serverUrl Url of the server including scheme, host and port
	 */
	public function __construct( $view, \Zend_Controller_Router_Interface $router, $serverUrl )
	{
		parent::__construct( $view );

		$this->router = $router;
		$this->serverUrl = $serverUrl;
	}


	/**
	 * Returns the URL assembled from the given arguments.
	 *
	 * @param string|null $target Route or page which should be the target of the link (if any)
	 * @param string|null $controller Name of the controller which should be part of the link (if any)
	 * @param string|null $action Name of the action which should be part of the link (if any)
	 * @param array $params Associative list of parameters that should be part of the URL
	 * @param array $trailing Trailing URL parts that are not relevant to identify the resource (for pretty URLs)
	 * @param array $config Additional configuration parameter per URL
	 * @return string Complete URL that can be used in the template
	 */
	public function transform( $target = null, $controller = null, $action = null, array $params = [], array $trailing = [], array $config = [] )
	{
		$paramList = array( 'controller' => $controller, 'action' => $action );


		foreach( $params as $key => $value )
		{
			// Slashes in URL parameters confuses the router
			$paramList[$key] = str_replace( '/', '_', $value );

			// Arrays are not supported
			if( is_array( $value ) ) {
				$paramList[$key] = implode( ' ', $value );
			}
		}

		if( !empty( $trailing ) ) {
			$paramList['trailing'] = str_replace( '/', '_', join( '_', $trailing ) );
		}

		$url = $this->router->assemble( $paramList, $target, true );

		if( isset( $config['absoluteUri'] ) ) {
			$url = $this->serverUrl . $url;
		}

		return $url;
	}
}