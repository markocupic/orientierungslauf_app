<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'Markocupic',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Modules
	'Markocupic\OrientierungslaufApp' => 'system/modules/mcupic_orientierungslauf_app/modules/OrientierungslaufApp.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_orientierungslauf_app'           => 'system/modules/mcupic_orientierungslauf_app/templates',
	'mod_orientierungslauf_app_loginform' => 'system/modules/mcupic_orientierungslauf_app/templates',
));
