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
use Cake\Utility\Text;

class ProfferPath implements ProfferPathInterface
{

    protected $root;

    protected $table;

    protected $field;

    protected $seed;

    protected $filename;

    protected $prefixes = [];

    /**
     * Construct the class and setup the defaults
     *
     * @param Table $table Instance of the table
     * @param Entity $entity Instance of the entity data
     * @param string $field The name of the upload field
     * @param array $settings Array of settings for the upload field
     */
    public function __construct(Table $table, Entity $entity, $field, array $settings)
    {
        if (isset($settings['root'])) {
            $this->setRoot($settings['root']);
        } else {
            $this->setRoot(WWW_ROOT . 'files');
        }

        $this->setTable($table->alias());
        $this->setField($field);
        $this->setSeed($this->generateSeed($entity->get($settings['dir'])));

        if (isset($settings['thumbnailSizes'])) {
            $this->setPrefixes($settings['thumbnailSizes']);
        }

        $this->setFilename($entity->get($field));
    }

    /**
     * Get the root
     *
     * @return string
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Set the root
     *
     * @param string $root The absolute path to the root of your upload folder, all
     * files will be uploaded under this path.
     * @return void
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }

    /**
     * Get the table
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Set the table
     *
     * @param string $table The name of the table the behaviour is dealing with.
     * @return void
     */
    public function setTable($table)
    {
        $this->table = strtolower($table);
    }

    /**
     * Get the field
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set the field
     *
     * @param string $field The name of the upload field
     * @return void
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * Get the seed
     *
     * @return string
     */
    public function getSeed()
    {
        return $this->seed;
    }

    /**
     * Set the seed
     *
     * @param string $seed The seed string used to create a folder for the uploaded files
     * @return void
     */
    public function setSeed($seed)
    {
        $this->seed = $seed;
    }

    /**
     * Get the filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set the filename or pull it from the upload array
     *
     * @param string|array $filename The name of the actual file including it's extension
     * @return void
     */
    public function setFilename($filename)
    {
        if (is_array($filename) && isset($filename['name'])) {
            $this->filename = $filename['name'];
        } else {
            $this->filename = $filename;
        }
    }

    /**
     * Get all the thumbnail size prefixes
     *
     * @return array
     */
    public function getPrefixes()
    {
        return $this->prefixes;
    }

    /**
     * Take the configured thumbnail sizes and store the prefixes
     *
     * @param array $thumbnailSizes The 'thumbnailSizes' dimension of the behaviour configuration array
     * @return void
     */
    public function setPrefixes(array $thumbnailSizes)
    {
        foreach ($thumbnailSizes as $prefix => $dimensions) {
            array_push($this->prefixes, $prefix);
        }
    }

    /**
     * Create a path seed value.
     *
     * @param string $seed The current seed if there is one
     * @return string
     */
    public function generateSeed($seed = null)
    {
        if ($seed) {
            return $seed;
        }

        return Text::uuid();
    }

    /**
     * Return the complete absolute path to an upload. If it's an image with thumbnails you can pass the prefix to
     * get the path to the prefixed thumbnail file.
     *
     * @param string $prefix Thumbnail prefix
     * @return string
     */
    public function fullPath($prefix = null)
    {
        $table = $this->getTable();
        $table = (!empty($table)) ? $table . DS : null;

        $seed = $this->getSeed();
        $seed = (!empty($seed)) ? $seed . DS : null;

        if ($prefix) {
            return $this->getRoot() . DS . $table . $this->getField() . DS . $this->getSeed() . DS . $prefix . '_' . $this->getFilename();
        }

        return $this->getRoot() . DS . $table . $this->getField() . DS . $seed . $this->getFilename();
    }

    /**
     * Return the absolute path to the containing parent folder where all the files will be uploaded
     *
     * @return string
     */
    public function getFolder()
    {
        $table = $this->getTable();
        $table = (!empty($table)) ? $table . DS : null;

        $seed = $this->getSeed();
        $seed = (!empty($seed)) ? $seed . DS : null;

        return $this->getRoot() . DS . $table . $this->getField() . DS . $seed;
    }

    /**
     * Check if the upload folder has already been created and if not create it
     *
     * @return bool
     */
    public function createPathFolder()
    {
        if (!file_exists($this->getFolder())) {
            return mkdir($this->getFolder(), 0777, true);
        }

        return true;
    }

    /**
     * Clear out a folder and optionally delete it
     *
     * @param string $folder Absolute path to the folder
     * @param bool $rmdir If you want to remove the folder as well
     * @return void
     */
    public function deleteFiles($folder, $rmdir = false)
    {
        array_map('unlink', glob($folder . DS . '*'));
        if ($rmdir) {
            rmdir($folder);
        }
    }
}
