<?php
App::uses('Security', 'Utility');
App::uses('Validation', 'Utility');
App::uses('AppHelper', 'View/Helper');

/**
 * CakePHP Gravatar Helper
 *
 * A CakePHP View Helper for the display of Gravatar images (http://www.gravatar.com)
 *
 * @copyright Copyright 2009 - 2013, Graham Weldon (http://grahamweldon.com)
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @package goodies
 * @subpackage goodies.views.helpers
 */
class GravatarHelper extends AppHelper {

/**
 * Gravatar avatar image base URL
 *
 * @var string
 */
	protected $_url = array(
		'http' => 'http://www.gravatar.com/avatar/',
		'https' => 'https://secure.gravatar.com/avatar/'
	);

/**
 * Hash type to use for email addresses
 *
 * @var string
 */
	protected $_hashType = 'md5';

/**
 * Collection of allowed ratings
 *
 * @var array
 */
	protected $_allowedRatings = array(
		'g', 'pg', 'r', 'x'
	);

/**
 * Default Icon sets
 *
 * @var array
 */
	protected $_defaultIcons = array(
		'none',
		'identicon',
		'mm',
		'monsterid',
		'retro',
		'wavatar',
		'404'
	);

/**
 * Default settings
 *
 * @var array
 */
	protected $_default = array(
		'default' => null,
		'size' => null,
		'rating' => null,
		'ext' => false
	);

/**
 * Helpers used by this helper
 *
 * @var array
 */
	public $helpers = array(
		'Html'
	);

/**
 * Constructor
 *
 * @param View $View
 * @param array $settings
 * @return GravatarHelper
 */
	public function __construct(View $View, $settings = array()) {
		if (!is_array($settings)) {
			$settings = array();
		}
		$this->_default = array_merge($this->_default, array_intersect_key($settings, $this->_default));

		// Default the secure option to match the current URL.
		$this->_default['secure'] = env('HTTPS');

		parent::__construct($View, $settings);
	}

/**
 * Show the Gravatar for the supplied email addresses
 *
 * @param string $email Email address
 * @param array $options Array of options, keyed from default settings
 * @return string Gravatar image string
 */
	public function image($email, $options = array()) {
		$imageUrl = $this->imageUrl($email, $options);
		unset($options['default'], $options['size'], $options['rating'], $options['ext']);
		return $this->Html->image($imageUrl, $options);
	}

/**
 * Generate image URL
 *
 * @param string $email Email address
 * @param array $options Array of options, keyed from default settings
 * @return string Gravatar Image URL
 */
	public function imageUrl($email, $options = array()) {
		if (env('HTTPS') && !isset($options['secure'])) {
			$options['secure'] = true;
		}
		$options = $this->_cleanOptions(array_merge($this->_default, $options));
		$ext = $options['ext'];
		$secure = $options['secure'];
		unset($options['ext'], $options['secure']);
		$protocol = $secure === true ? 'https' : 'http';

		$imageUrl = $this->_url[$protocol] . $this->_emailHash($email, $this->_hashType);
		if ($ext === true) {
			// If 'ext' option is supplied and true, append an extension to the generated image URL.
			// This helps systems that don't display images unless they have a specific image extension on the URL.
			$imageUrl .= '.jpg';
		}
		$imageUrl .= $this->_buildOptions($options);
		return $imageUrl;
	}

/**
 * Sanitize the options array
 *
 * @param array $options Array of options, keyed from default settings
 * @return array Clean options array
 * @return array
 */
	protected function _cleanOptions($options) {
		if (!isset($options['size']) || empty($options['size']) || !is_numeric($options['size'])) {
			unset($options['size']);
		} else {
			$options['size'] = min(max($options['size'], 1), 512);
		}

		if (!$options['rating'] || !in_array(mb_strtolower($options['rating']), $this->_allowedRatings)) {
			unset($options['rating']);
		}

		if (!$options['default']) {
			unset($options['default']);
		} else {
			if (!in_array($options['default'], $this->_defaultIcons) && !Validation::url($options['default'])) {
				unset($options['default']);
			}
		}
		return $options;
	}

/**
 * Generate email address hash
 *
 * @param string $email Email address
 * @param string $type Hash type to employ
 * @return string Email address hash
 */
	protected function _emailHash($email, $type) {
		return Security::hash(mb_strtolower($email), $type);
	}

/**
 * Build Options URL string
 *
 * @param array $options Array of options, keyed from default settings
 * @return string URL string of options
 */
	protected function _buildOptions($options = array()) {
		$gravatarOptions = array_intersect(array_keys($options), array_keys($this->_default));
		if (!empty($gravatarOptions)) {
			$optionArray = array();
			foreach ($gravatarOptions as $key) {
				$value = $options[$key];
				$optionArray[] = $key . '=' . mb_strtolower($value);
			}
			return '?' . implode('&amp;', $optionArray);
		}
		return '';
	}
}
