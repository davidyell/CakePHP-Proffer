<?php

/**
 * @category Proffer
 * @package ProfferPathInterface.php
 *
 * @author David Yell <neon1024@gmail.com>
 * @when 23/03/15
 *
 */

namespace Proffer\Lib;

use Cake\ORM\Entity;
use Cake\ORM\Table;

interface ProfferPathInterface
{

    /**
     * Returns the root folder in which all uploads should be placed.
     *
     * @return string
     */
    public function getRoot();

    /**
     * Set the root folder for all uploads.
     * Default is WWW_DIR . 'files'
     *
     * @param string $root The root folder into which all uploads will be placed
     * @return void
     */
    public function setRoot($root);

    /**
     * Returns the name of the table the upload is associated with.
     *
     * @return string
     */
    public function getTable();

    /**
     * Set the table name
     *
     * @param string $table The name of the table
     * @return void
     */
    public function setTable($table);

    /**
     * Returns the name of the upload field as configured in the table.
     *
     * @return string
     */
    public function getField();

    /**
     * The name of the upload field
     *
     * @param string $field The upload field
     * @return void
     */
    public function setField($field);

    /**
     * Returns the seed used to generate a folder to hold all the associated uploads.
     * Should be the contents of the 'dir' field configured in the Table.
     *
     * @return string
     */
    public function getSeed();

    /**
     * The path seed used to create a folder into which files can be uploaded
     *
     * @param string $seed The seed value
     * @return void
     */
    public function setSeed($seed);

    /**
     * Return the name of the uploaded file.
     *
     * @return string
     */
    public function getFilename();

    /**
     * The filename for the uploaded file
     *
     * @param string $filename The name of the file
     * @return void
     */
    public function setFilename($filename);

    /**
     * Returns an array of all the configured prefixes.
     *
     * @return array
     */
    public function getPrefixes();

    /**
     * Set the thumbnail prefixes from the configured thumbnail sizes.
     *
     * @param array $thumbnailSizes Array of different thumbnail sizes, keyed with the thumbnail prefix
     * @return void
     */
    public function setPrefixes(array $thumbnailSizes);

    /**
     * Will create a new seed for new uploads. Should also pass back existing seed for new uploads to the same record.
     * Default return is String::uuid()
     *
     * @param string $seed The existing seed if one exists
     * @return string
     */
    public function generateSeed($seed);

    /**
     * Return the complete absolute path to an upload. If it's an image with thumbnails you can pass the prefix to
     * get the path to the prefixed thumbnail file.
     *
     * @param string $prefix The specific prefix to get the path for
     * @return string
     */
    public function fullPath($prefix = null);

    /**
     * Return the absolute path to the containing parent folder where all the files will be uploaded
     *
     * @return string
     */
    public function getFolder();

    /**
     * Check if the upload folder has already been created and if not create it
     *
     * @return bool
     */
    public function createPathFolder();

    /**
     * Remove all images from a folder and optionally remove the folder as well
     *
     * @param string $folder The absolute path to the folder to remove.
     * @param bool $rmdir If you want to remove the folder as well as the files.
     * @return bool
     */
    public function deleteFiles($folder, $rmdir = false);
}
