<?php
namespace Proffer\Shell;

use Cake\Console\Shell;
use Cake\Core\Exception\Exception;
use Proffer\Lib\ImageTransform;
use Proffer\Lib\ProfferPath;

/**
 * Proffer shell command.
 */
class ProfferShell extends Shell {

/**
 * Store the table instance
 *
 * @var Cake\ORM\Table $table Table instance
 */
	private $__Table;

/**
 * Return the help options and validate arguments
 *
 * @return \Cake\Console\ConsoleOptionParser
 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		$parser->addSubcommand('generate', [
			'help' => __('Regenerate thumbnails for a specific table.'),
			'parser' => [
				'description' => [__('Use this command to regenerate the thumbnails for a specific table.')],
				'arguments' => [
					'table' => ['help' => __('The table to regenerate thumbs for'), 'required' => true]
				]
			]
		]);
		$parser->addSubcommand('cleanup', [
			'help' => __('Clean up old images on the file system which are not linked in the database.'),
			'parser' => [
				'description' => [__('This command will delete images which are not part of the model configuration.')],
				'arguments' => [
					'table' => ['help' => __('The table to regenerate thumbs for'), 'required' => true]
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
	public function main() {
		$this->out('Welcome to the Proffer shell.');
		$this->out('This shell can be used to regenerate thumbnails.');
		$this->out("Please use 'bin/cake proffer.proffer -h' flag to get further help.");
	}

/**
 * Load a table, get it's config and then regenerate the thumbnails for that tables upload fields.
 *
 * @param string $table The name of the table
 * @return void
 */
	public function generate($table) {
		$this->_checkTable($table);

		$config = $this->__Table->behaviors()->Proffer->config();

		foreach ($config as $field => $settings) {
			$transform = new ImageTransform();

			$records = $this->{$this->__Table->alias()}->find()
				->select([$field, $settings['dir']])
				->where([
					"$field IS NOT NULL",
					"$field != ''"
				]);

			foreach ($records as $item) {
				$path = new ProfferPath($this->__Table, $item, $field, $settings);
				$engine = $settings['thumbnailMethod'];

				foreach ($settings['thumbnailSizes'] as $prefix => $dimensions) {
					$image = $transform->makeThumbnails($path, $dimensions, $engine);
					$transform->saveThumbs($image, $path, $prefix);

					$this->out(__('Thumbnails regenerated ' . $prefix . '_' . $item->get($field)));
				}
			}
		}
	}

/**
 * Clean up files associated with a table which don't have an entry in the db
 *
 * @param string $table The name of the table
 * @return void
 */
	public function cleanup($table) {
		$this->_checkTable($table);

		$okayToDestroy = $this->in(__('Are you sure? This will irreversibly delete files'), ['y', 'n'], 'n');
		if ($okayToDestroy === 'N') {
			$this->out(__('Aborted, no files deleted.'));
			exit;
		}

		$folders = glob(WWW_ROOT . 'files' . DS . strtolower($table) . DS . '*');
		foreach ($folders as $folder) {
			$config = $this->__Table->behaviors()->Proffer->config();
			$seed = pathinfo($folder, PATHINFO_BASENAME);

			foreach ($config as $field => $settings) {
				$dir = $settings['dir'];

				$record = $this->__Table->exists([$dir => $seed]);

				if (!$record) {
					$files = glob($folder . DS . '*');
					foreach ($files as $file) {
						unlink($file);
						$this->out(__("Deleted file '$file'"));
					}
				}
			}

			if (!$record) {
				rmdir($folder);
				$this->out(__("Deleted folder '$folder'"));
			}
		}

		$this->out(__('Completed'));
	}

/**
 * Do some checks on the table which has been passed to make sure that it has what we need
 *
 * @param string $table The table
 * @return void
 */
	protected function _checkTable($table) {
		try {
			$this->__Table = $this->loadModel($table);
		} catch (Exception $e) {
			$this->out(__('<error>' . $e->getMessage() . '</error>'));
			exit;
		}

		if (get_class($this->__Table) === 'AppModel') {
			$this->out(__('<error>The table could not be found, instance of AppModel loaded.</error>'));
			exit;
		}

		if (!$this->__Table->hasBehavior('Proffer')) {
			$this->out(__("<error>The table '" . $this->__Table->alias() . "' does not have the Proffer behavior attached.</error>"));
			exit;
		}
	}
}
