<?php
/**
 * ProfferPath
 * Class for building, managing and finding paths to uploaded files
 *
 * @author David Yell <neon1024@gmail.com>
 */

namespace Proffer\Lib;

use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\Utility\String;

class ProfferPath {

	private $__root;
	private $__table;
	private $__field;
	private $__seed;
	private $__filename;
	private $__prefixes = [];

/**
 * Construct the class and setup the defaults
 *
 * @param Table $table Instance of the table
 * @param Entity $entity Instance of the entity data
 * @param string $field The name of the upload field
 * @param array $settings Array of settings for the upload field
 */
	public function __construct(Table $table, Entity $entity, $field, $settings) {
		$this->setRoot(WWW_ROOT . 'files');
		$this->setTable($table->alias());
		$this->setField($field);
		$this->setSeed($this->generateSeed($entity->get($settings['dir'])));
		$this->setPrefixes($settings['thumbnailSizes']);
		$this->setFilename($entity->get($field));
	}

/**
 * @return string
 */
	public function getRoot() {
		return $this->__root;
	}

/**
 * @param string $root The absolute path to the root of your upload folder, all files will be uploaded under this path.
 */
	protected function setRoot($root) {
		$this->__root = $root;
	}

/**
 * @return string
 */
	public function getTable() {
		return $this->__table;
	}

/**
 * @param string $table The name of the table the behaviour is dealing with.
 */
	protected function setTable($table) {
		$this->__table = $table;
	}

/**
 * @return string
 */
	public function getField() {
		return $this->__field;
	}

/**
 * @param string $field The name of the upload field
 */
	protected function setField($field) {
		$this->__field = $field;
	}

/**
 * @return string
 */
	public function getSeed() {
		return $this->__seed;
	}

/**
 * @param string $seed The seed string used to create a folder for the uploaded files
 */
	protected function setSeed($seed) {
		$this->__seed = $seed;
	}

/**
 * @return string
 */
	public function getFilename() {
		return $this->__filename;
	}

/**
 * @param string $filename The name of the actual file including it's extension
 */
	protected function setFilename($filename) {
		$this->__filename = $filename;
	}

/**
 * @return array
 */
	public function getPrefixes() {
		return $this->__prefixes;
	}

/**
 * Take the configured thumbnail sizes and store the prefixes
 *
 * @param array $thumbnailSizes The 'thumbnailSizes' dimension of the behaviour configuration array
 */
	protected function setPrefixes($thumbnailSizes) {
		foreach ($thumbnailSizes as $prefix => $dimensions) {
			array_push($this->__prefixes, $prefix);
		}
	}

/**
 * Create a path seed value.
 *
 * @param string $seed The current seed if there is one
 * @return string
 */
	protected function generateSeed($seed = null) {
		if ($seed === null) {
			return String::uuid();
		}

		return $seed;
	}

/**
 * Return the complete absolute path to an upload. If it's an image with thumbnails you can pass the prefix to get the
 * path to the prefixed thumbnail file.
 *
 * @param string $prefix Thumbnail prefix
 * @return string
 */
	public function fullPath($prefix = null) {
		if ($prefix) {
			return $this->getRoot() . DS . $this->getTable() . DS . $this->getField() . DS . $this->getSeed() . DS . $prefix . '_' . $this->getFilename();
		}

		return $this->getRoot() . DS . $this->getTable() . DS . $this->getField() . DS . $this->getSeed() . DS . $this->getFilename();
	}

/**
 * Return the absolute path to the containing parent folder where all the files will be uploaded
 *
 * @return string
 */
	public function getFolder() {
		return $this->getRoot() . DS . $this->getTable() . DS . $this->getField() . DS . $this->getSeed() . DS;
	}

/**
 * Check if the upload folder has already been created and if not create it
 *
 * @return bool
 */
	public function createPathFolder() {
		if (!file_exists($this->getFolder())) {
			return mkdir($this->getFolder(), 0777, true);
		}
	}

/**
 * Clear out a folder and optionally delete it
 *
 * @param string $folder Absolute path to the folder
 * @param bool $rmdir If you want to remove the folder as well
 */
	public function deleteFiles($folder, $rmdir = false) {
		array_map('unlink', glob($folder . DS . '*'));
		if ($rmdir) {
			rmdir($folder);
		}
	}
}