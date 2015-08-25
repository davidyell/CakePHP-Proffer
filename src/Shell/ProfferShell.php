<?php
namespace Proffer\Shell;

use Cake\Console\Shell;
use Cake\Core\Exception\Exception;
use Proffer\Lib\ImageTransform;
use Proffer\Lib\ProfferPath;

/**
 * Proffer shell command.
 */
class ProfferShell extends Shell
{

    /**
     * Store the table instance
     *
     * @var \Cake\ORM\Table $table Table instance
     */
    private $Table;

    /**
     * Return the help options and validate arguments
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->addSubcommand('generate', [
            'help' => __('Regenerate thumbnails for a specific table.'),
            'parser' => [
                'description' => [__('Use this command to regenerate the thumbnails for a specific table.')],
                'arguments' => [
                    'table' => ['help' => __('The table to regenerate thumbs for'), 'required' => true]
                ],
                'options' => [
                    'path-class' => [
                        'short' => 'p',
                        'help' => __('Fully name spaced custom path class, you must use double backslash.')
                    ],
                    'image-class' => [
                        'short' => 'i',
                        'help' => __('Fully name spaced custom image transform class, you must use double backslash.')
                    ]
                ]
            ]
        ]);
        $parser->addSubcommand('cleanup', [
            'help' => __('Clean up old images on the file system which are not linked in the database.'),
            'parser' => [
                'description' => [__('This command will delete images which are not part of the model configuration.')],
                'arguments' => [
                    'table' => ['help' => __('The table to regenerate thumbs for'), 'required' => true]
                ],
                'options' => [
                    'dry-run' => [
                        'short' => 'd',
                        'help' => __('Do a dry run and don\'t delete any files.'),
                        'boolean' => true
                    ]
                ]
            ],
        ]);

        return $parser;
    }

    /**
     * Introduction to the shell
     *
     * @return void
     */
    public function main()
    {
        $this->out('Welcome to the Proffer shell.');
        $this->out('This shell can be used to regenerate thumbnails and cleanup unlinked images.');
        $this->hr();
        $this->out($this->OptionParser->help());
    }

    /**
     * Load a table, get it's config and then regenerate the thumbnails for that tables upload fields.
     *
     * @param string $table The name of the table
     * @return void
     */
    public function generate($table)
    {
        $this->checkTable($table);

        $config = $this->Table->behaviors()->Proffer->config();

        foreach ($config as $field => $settings) {
            $records = $this->{$this->Table->alias()}->find()
                ->select([$this->Table->primaryKey(), $field, $settings['dir']])
                ->where([
                    "$field IS NOT NULL",
                    "$field != ''"
                ]);

            foreach ($records as $item) {
                if ($this->param('verbose')) {
                    $this->out(
                        __('Processing ' . $this->Table->alias() . ' ' . $item->get($this->Table->primaryKey()))
                    );
                }

                if (!empty($this->param('path-class'))) {
                    $class = (string)$this->param('path-class');
                    $path = new $class($this->Table, $item, $field, $settings);
                } else {
                    $path = new ProfferPath($this->Table, $item, $field, $settings);
                }

                if (!empty($this->param('image-class'))) {
                    $class = (string)$this->param('image_class');
                    $transform = new $class($this->Table, $path);
                } else {
                    $transform = new ImageTransform($this->Table, $path);
                }

                $transform->processThumbnails($settings);

                if ($this->param('verbose')) {
                    $this->out(__('Thumbnails regenerated for ' . $path->fullPath()));
                } else {
                    $this->out(__('Thumbnails regenerated for ' . $this->Table->alias() . ' ' . $item->get($field)));
                }
            }
        }

        $this->out($this->nl(0));
        $this->out(__('<info>Completed</info>'));
    }

    /**
     * Clean up files associated with a table which don't have an entry in the db
     *
     * @param string $table The name  of the table
     * @return void
     */
    public function cleanup($table)
    {
        $this->checkTable($table);

        if (!$this->param('dry-run')) {
            $okayToDestroy = $this->in(__('Are you sure? This will irreversibly delete files'), ['y', 'n'], 'n');
            if ($okayToDestroy === 'N') {
                $this->out(__('Aborted, no files deleted.'));
                exit;
            }
        } else {
            $this->out(__('<info>Performing dry run cleanup.</info>'));
            $this->out($this->nl(0));
        }

        $config = $this->Table->behaviors()->Proffer->config();

        // Get the root upload folder for this table
        $uploadFieldFolders = glob(WWW_ROOT . 'files' . DS . strtolower($table) . DS . '*');

        // Loop through each upload field configured for this table (field)
        foreach ($uploadFieldFolders as $fieldFolder) {
            // Loop through each instance of an upload for this field (seed)
            $uploadFolders = glob($fieldFolder . DS . '*');
            foreach ($uploadFolders as $seedFolder) {
                // Does the seed exist in the db?
                $seed = pathinfo($seedFolder, PATHINFO_BASENAME);

                foreach ($config as $field => $settings) {
                    $targets = [];

                    $record = $this->{$this->Table->alias()}->find()
                        ->select([
                            $field,
                            $settings['dir']
                        ])
                        ->where([
                            $settings['dir'] => $seed
                        ])
                        ->first();

                    if ($record) {
                        $record = $record->toArray();
                    } else {
                        $record = [];
                    }

                    if (!in_array($seed, $record)) {
                        // No it doesn't - remove the folder and it's contents - probably with a user prompt
                        if ($this->param('dry-run')) {
                            if ($this->param('verbose')) {
                                $this->out(__("Would remove folder `$seedFolder`"));
                            } else {
                                $this->out(__("Would remove folder `$seed`"));
                            }
                        } else {
                            array_map('unlink', glob($seedFolder . DS . '*'));
                            rmdir($seedFolder);

                            if ($this->param('verbose')) {
                                $this->out(__("Remove `$seedFolder` folder and contents"));
                            } else {
                                $this->out(__("Removed `$seed` folder and contents"));
                            }
                        }

                    } else {
                        $files = glob($seedFolder . DS . '*');

                        $filenames = array_map(function ($p) {
                            return pathinfo($p, PATHINFO_BASENAME);
                        }, $files);

                        $targets[] = $record[$field];
                        if (!empty($settings['thumbnailSizes'])) {
                            foreach ($settings['thumbnailSizes'] as $prefix => $dimensions) {
                                $targets[] = $prefix . '_' . $record[$field];
                            }
                        }

                        $filesToRemove = array_diff($filenames, $targets);

                        foreach ($filesToRemove as $file) {
                            if ($this->param('dry-run') && $this->param('verbose')) {
                                $this->out(__("Would delete `$seedFolder" . DS . "$file`"));
                            } elseif ($this->param('dry-run')) {
                                $this->out(__("Would delete `$file`"));
                            } else {
                                unlink($seedFolder . DS . $file);
                                if ($this->param('verbose')) {
                                    $this->out(__("Deleted `$seedFolder" . DS . "$file`"));
                                } else {
                                    $this->out(__("Deleted `$file`"));
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->out($this->nl(0));
        $this->out(__('<info>Completed</info>'));
    }

    /**
     * Do some checks on the table which has been passed to make sure that it has what we need
     *
     * @param string $table The table
     * @return void
     */
    protected function checkTable($table)
    {
        try {
            $this->Table = $this->loadModel($table);
        } catch (Exception $e) {
            $this->out(__('<error>' . $e->getMessage() . '</error>'));
            exit;
        }

        if (get_class($this->Table) === 'AppModel') {
            $this->out(__('<error>The table could not be found, instance of AppModel loaded.</error>'));
            exit;
        }

        if (!$this->Table->hasBehavior('Proffer')) {
            $out = __(
                "<error>The table '" . $this->Table->alias() .
                "' does not have the Proffer behavior attached.</error>"
            );
            $this->out($out);
            exit;
        }

        $config = $this->Table->behaviors()->Proffer->config();
        foreach ($config as $field => $settings) {
            if (!$this->Table->hasField($field)) {
                $out = __(
                    "<error>The table '" . $this->Table->alias() .
                    "' does not have the configured upload field in it's schema.</error>"
                );
                $this->out($out);
                exit;
            }
            if (!$this->Table->hasField($settings['dir'])) {
                $out = __(
                    "<error>The table '" . $this->Table->alias() .
                    "' does not have the configured dir field in it's schema.</error>"
                );
                $this->out($out);
                exit;
            }

        }
    }
}
