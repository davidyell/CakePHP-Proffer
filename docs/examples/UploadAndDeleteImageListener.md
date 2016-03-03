##Customizing behavior of file creation/deletion using event listener

You can hook Proffer's image creation/deletion as below.

###Create src/Event/UploadAndDeleteImageListener.php

```php
<?php

namespace App\Event;

use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Log\Log;
use Proffer\Lib\ProfferPath;

class UploadAndDeleteImageListener implements EventListenerInterface {

    public function implementedEvents()
    {
        return [
            'Proffer.afterCreateImage' => 'createImage',
            'Proffer.beforeDeleteImage' => 'deleteImage',
        ];
    }

    public function createImage(Event $event, ProfferPath $path, $imagePath)
    {
        Log::write('debug', 'hook event of createImage path: ' . $imagePath);

        // copy file to external service (e.g. Amazon S3)
        // delete locale file
    }

    public function deleteImage(Event $event, ProfferPath $path)
    {
        Log::write('debug', 'hook event of deleteImage folder: ' . $path->getFolder());

        // delete file from external service (e.g. Amazon S3)
    }
}
```

###Register listener to EventManager in config/bootstrap.php

```php
Cake\Event\EventManager::instance()->on(new \App\Event\UploadAndDeleteImageListener());
```
