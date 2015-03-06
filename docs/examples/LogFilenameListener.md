```php
<?php
/**
 * Example listener class which will log the full absolute pathname for a Proffer upload
 * to the logs
 * 
 * Should be in `src/Event`
 * 
 * @category Example 
 * @package LogFilenameListener.php
 * 
 * @author David Yell <neon1024@gmail.com>
 * @when 03/03/15
 */

namespace App\Event;

use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Log\Log;
use Proffer\Lib\ProfferPath;

class LogFilenameListener implements EventListenerInterface
{
    public function implementedEvents()
    {
        // Which events to listen for, and which method to run
        return [
            'Proffer.beforeThumbs' => 'log',
            'Proffer.afterThumbs' => 'log'
        ];
    }

    public function log(Event $event, ProfferPath $path)
    {
        Log::write(LOG_INFO, $path->fullPath());
    }
}
```