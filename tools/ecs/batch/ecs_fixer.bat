:: Run easy-coding-standard (ecs) via this batch file inside your IDE e.g. PhpStorm (Windows only)
:: Install inside PhpStorm the  "Batch Script Support" plugin
cd..
cd..
cd..
cd..
cd..
cd..
php vendor\bin\ecs check vendor/markocupic/contao-filepond/src --fix --config vendor/markocupic/contao-filepond/tools/ecs/config.php
php vendor\bin\ecs check vendor/markocupic/contao-filepond/contao --fix --config vendor/markocupic/contao-filepond/tools/ecs/config.php
php vendor\bin\ecs check vendor/markocupic/contao-filepond/config --fix --config vendor/markocupic/contao-filepond/tools/ecs/config.php
php vendor\bin\ecs check vendor/markocupic/contao-filepond/templates --fix --config vendor/markocupic/contao-filepond/tools/ecs/config.php
php vendor\bin\ecs check vendor/markocupic/contao-filepond/tests --fix --config vendor/markocupic/contao-filepond/tools/ecs/config.php
