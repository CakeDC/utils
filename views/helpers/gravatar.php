<?php
/**
 * CakePHP Gravatar Helper
 *
 * A CakePHP View Helper for the display of Gravatar images (http://www.gravatar.com)
 *
 * @copyright Copyright 2010, Graham Weldon (http://grahamweldon.com)
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

App::import(array('Security', 'Validation'));

/**
 * Gravatar Helper
 *
 * @package utils
 * @subpackage utils.views.helpers
 */
class GravatarHelper extends AppHelper {

/**
 * Gravatar avatar image base URL
 *
 * @var string
 * @access private
 */
	private $__url = array(
		'http' => 'http://www.gravatar.com/avatar/',
		'https' => 'https://secure.gravatar.com/avatar/'
	);

/**
 * Hash type to use for email addresses
 *
 * @var string
 * @access private
 */
	private $__hashType = 'md5';

/**
 * Collection of allowed ratings
 *
 * @var array
 * @access private
 */
	private $__allowedRatings = array('g', 'pg', 'r', 'x');

/**
 * Default Icon sets
 *
 * @var array
 * @access private
 */
	private $__defaultIcons = array('none', 'identicon', 'monsterid', 'wavatar', '404');

/**
 * Default settings
 *
 * @var array
 * @access private
 */
	private $__default = array(
		'default' => null,
		'size' => null,
		'rating' => null,
		'ext' => false);

/**
 * Helpers used by this helper
 *
 * @var array
 * @access public
 */
	public $helpers = array('Html');

/**
 * Constructor
 *
 * @access public
 */
	public function __construct() {
		// Default the secure option to match the current URL.
		$this->__default['secure'] = env('HTTPS');
	}

/**
 * Show gravatar for the supplied email address
 *
 * @param string $email Email address
 * @param array $options Array of options, keyed from default settings
 * @return string Gravatar image string
 * @access public
 */
	public function image($email, $options = array()) {
		$imageUrl = $this->url($email, $options);
		unset($options['default'], $options['size'], $options['rating'], $options['ext']);
		return $this->Html->image($imageUrl, $options);
	}

/**
 * Generate image URL
 *
 * @param string $email Email address
 * @param string $options Array of options, keyed from default settings
 * @return string Gravatar Image URL
 * @access public
 */
	public function url($email, $options = array()) {
		$options = $this->__cleanOptions(array_merge($this->__default, $options));
		$ext = $options['ext'];
		$secure = $options['secure'];
		unset($options['ext'], $options['secure']);
		$protocol = $secure === true ? 'https' : 'http';

		$imageUrl = $this->__url[$protocol] . $this->__emailHash($email, $this->__hashType);
		if ($ext === true) {
			// If 'ext' option is supplied and true, append an extension to the generated image URL.
			// This helps systems that don't display images unless they have a specific image extension on the URL.
			$imageUrl .= '.jpg';
		}
		$imageUrl .= $this->__buildOptions($options);
		return $imageUrl;
	}

/**
 * Sanitize the options array
 *
 * @param array $options Array of options, keyed from default settings
 * @return array Clean options array
 * @access private
 */
	private function __cleanOptions($options) {
		if (!isset($options['size']) || empty($options['size']) || !is_numeric($options['size'])) {
			unset($options['size']);
		} else {
			$options['size'] = min(max($options['size'], 1), 512);
		}

		if (!$options['rating'] || !in_array(mb_strtolower($options['rating']), $this->__allowedRatings)) {
			unset($options['rating']);
		}

		if (!$options['default']) {
			unset($options['default']);
		} else {
			if (!in_array($options['default'], $this->__defaultIcons) && !Validation::url($options['default'])) {
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
 * @access private
 */
	private function __emailHash($email, $type) {
		return Security::hash(mb_strtolower($email), $type);
	}

/**
 * Build Options URL string
 *
 * @param array $options Array of options, keyed from default settings
 * @return string URL string of options
 * @access private
 */
	private function __buildOptions($options = array()) {
		$gravatarOptions = array_intersect(array_keys($options), array_keys($this->__default));
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
?>