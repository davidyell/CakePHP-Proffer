<?php
namespace Proffer\Shell;

use Cake\Console\Shell;
use Cake\Core\Exception\Exception;
use Proffer\Lib\ImageTransform;

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
				'description' => [
					__('Use this command to regenerate the thumnails for a specific table.')
				],
				'arguments' => [
					'table' => ['help' => __('The table to regenerate thumbs for'), 'required' => true]
				]
			]
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
		$this->out('Please use the -h flag to get further help.');
	}

/**
 * Load a table, get it's config and then regenerate the thumbnails for that tables upload fields.
 *
 * @param string $table The name of the table
 * @return void
 */
	public function generate($table) {
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
				$path = $this->__Table->behaviors()->Proffer->getPath($this->__Table, $item, $field, $item->get($field));
				$engine = $settings['thumbnailMethod'];

				foreach ($settings['thumbnailSizes'] as $prefix => $dimensions) {
					$image = $transform->makeThumbnails($path, $dimensions, $engine);
					$transform->saveThumbs($image, $path, $prefix);

					$this->out(__('Thumbnails regenerated ' . $prefix . '_' . $item->get($field)));
				}
			}
		}
	}
}
