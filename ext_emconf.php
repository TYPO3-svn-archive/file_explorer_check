<?php

########################################################################
# Extension Manager/Repository config file for ext: "file_explorer_check"
#
# Auto generated 29-05-2008 15:15
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'File explorer consistency check',
	'description' => 'Checks the consistency of file explorer and lets you fix files that are not in database or vice versa.',
	'category' => 'module',
	'author' => 'Cyrill Helg',
	'author_email' => 'typo3 (at) phlogi.net',
	'shy' => '',
	'dependencies' => 'file_explorer',
	'conflicts' => '',
	'priority' => '',
	'module' => 'mod1',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.1.3',
	'constraints' => array(
		'depends' => array(
			'file_explorer' => '2.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:13:{s:9:"ChangeLog";s:4:"ff43";s:10:"README.txt";s:4:"3c56";s:12:"ext_icon.gif";s:4:"749a";s:14:"ext_tables.php";s:4:"4b0d";s:14:"doc/manual.sxw";s:4:"a2fb";s:19:"doc/wizard_form.dat";s:4:"5aa3";s:20:"doc/wizard_form.html";s:4:"6441";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"05d6";s:14:"mod1/index.php";s:4:"993d";s:18:"mod1/locallang.xml";s:4:"85e2";s:22:"mod1/locallang_mod.xml";s:4:"91a6";s:19:"mod1/moduleicon.gif";s:4:"749a";}',
	'suggests' => array(
	),
);

?>