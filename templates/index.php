<?php

declare(strict_types=1);

use OCP\Util;

Util::addScript(OCA\TestCases\AppInfo\Application::APP_ID, OCA\TestCases\AppInfo\Application::APP_ID . '-main');
Util::addStyle(OCA\TestCases\AppInfo\Application::APP_ID, OCA\TestCases\AppInfo\Application::APP_ID . '-main');

?>

<div id="testcases"></div>
