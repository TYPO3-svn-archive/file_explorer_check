<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Cyrill Helg <typo3 (at) phlogi.net>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');

$LANG->includeLLFile('EXT:file_explorer_check/mod1/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

require_once (PATH_t3lib.'class.t3lib_page.php');
require_once (PATH_t3lib.'class.t3lib_tstemplate.php');
require_once (PATH_t3lib.'class.t3lib_tsparser_ext.php');

require_once(t3lib_extMgm::extPath('file_explorer')."pi1/class.tx_fileexplorer_pi1.php");
require_once(PATH_tslib.'class.tslib_content.php');




/**
 * Module 'File Explorer' for the 'file_explorer_check' extension.
 *
 * @author	Cyrill Helg <typo3 (at) phlogi.net>
 * @package	TYPO3
 * @subpackage	tx_fileexplorercheck
 */
class  tx_fileexplorercheck_module1 extends t3lib_SCbase {
				var $pageinfo;

				/**
				 * Initializes the Module
				 * @return	void
				 */
				function init()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

					$this->prefixIdPlugin  = 'tx_fileexplorer_pi1';		// Same as class name
					parent::init();
				}

				/**
				 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
				 *
				 * @return	void
				 */
				function menuConfig()	{
					global $LANG;
					$this->MOD_MENU = Array (
						'function' => Array (
							'1' => $LANG->getLL('check'),
 							'2' => $LANG->getLL('update'),
						)
					);
					parent::menuConfig();
				}

				/**
				 * Main function of the module. Write the content to $this->content
				 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
				 *
				 * @return	[type]		...
				 */
				function main()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

					// Access check!
					// The page will show only if there is a valid page and if this page may be viewed by the user
					$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
					$access = is_array($this->pageinfo) ? 1 : 0;

					if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{

							// Draw the header.
						$this->doc = t3lib_div::makeInstance('mediumDoc');
						$this->doc->backPath = $BACK_PATH;
						$this->doc->form='<form action="" method="POST">';

							// JavaScript
						$this->doc->JScode = '
							<script language="javascript" type="text/javascript">
								script_ended = 0;
								function jumpToUrl(URL)	{
									document.location = URL;
								}
							</script>
						';
						$this->doc->postCode='
							<script language="javascript" type="text/javascript">
								script_ended = 1;
								if (top.fsMod) top.fsMod.recentIds["web"] = 0;
							</script>
						';

						$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						$this->content.=$this->doc->header($LANG->getLL('title'));
						$this->content.=$this->doc->spacer(5);
						$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
						$this->content.=$this->doc->divider(5);


						// Render content:
						$this->moduleContent();

						//render verbose output
						if ($_POST['verbose']==1&&!empty($this->verbose))	$this->content .= '<strong>Additional verbose output: </strong><br/>'.$this->verbose.'<hr/>';

						// ShortCut
						if ($BE_USER->mayMakeShortcut())	{
							$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
						}

						$this->content.=$this->doc->spacer(10);
					} else {
							// If no access or if ID == zero

						$this->doc = t3lib_div::makeInstance('mediumDoc');
						$this->doc->backPath = $BACK_PATH;

						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						$this->content.=$this->doc->header($LANG->getLL('title'));
						$this->content.=$this->doc->spacer(5);
						$this->content.=$this->doc->spacer(10);
					}
				}

				/**
				 * Prints out the module HTML
				 *
				 * @return	void
				 */
				function printContent()	{
					$this->content.=$this->doc->endPage();
					echo $this->content;
				}

				/**
				 * Generates the module content
				 *
				 * @return	void
				 */
				function moduleContent()	{

					$newClass = t3lib_div::makeInstanceClassName('tx_fileexplorer_pi1');
					$fileExpObj = new $newClass($this);
					$this->loadTS($_GET['id']);
// 					$fileExpObj->conf = $this->conf;
//  					$fileExpObj->initFlexform();
// 					$fileExpObj->setVariousConfValues();
// // 					print_r($fileExpObj->conf);
//  					$content .=t3lib_div::view_array($fileExpObj->conf);
					require_once(t3lib_extMgm::extPath('file_explorer')."pi1/classes/class.tx_fileexplorer_data.php");
					$newClass = t3lib_div::makeInstanceClassName('tx_fileexplorer_data');
					$this->handleData = new $newClass($this);

					switch((string)$this->MOD_SETTINGS['function'])	{
						case 1:
							$content .= 'Its very important that you select your <strong>root storage folder</strong> on the left in the page tree! If you do not, the result is <strong>completely wrong </strong>and you may <strong>lose your files!</strong><br/><br/>';
							$content.='<form action="">
											<p>Choose the action please:</p>
											<p>
												<input type="radio" name="action" checked="checked" value="checkOnly"/> Do the check only, dry run. <br/>
												<input type="radio" name="action" value="insert"/> Insert missing file/folder records into db. <br/>
												<input type="radio" name="action" value="deleteInDB"/> Delete file/folder records from db that are not existing on the filesystem.<br/>
												<input type="radio" name="action" value="deleteFS"/> Delete file(s)/folder(s) from filesystem that have no record in database. <br/>
												Values for permissions of inserted database records:<br/>
												<input type="text" name="ownerUid" value=""/> The id of the owner user for inserted file(s)/folder(s).<br/>
												<input type="text" name="groupReadUid" value=""/> List of group id\'s for read access for inserted folder(s).<br/>
												<input type="text" name="groupWriteUid" value=""/> List of group id\'s for write access for inserted folder(s). <br/>
												<input type="checkbox" name="verbose" checked="checked" value="1"/> Be verbose and print additional information.<br/>
											</p><br/>
											      <input type="submit" value="Go!"/>
											</form>

								<hr />';

							switch($_POST['action']){
								case 'checkOnly':
									$this->doTheCheck();
									$this->content.=$this->doc->section('Results from the check',$this->getCheckResults(),0,2);
									$this->content.=$this->doc->section('Choose further action',$content,0,1);

									break;
								case 'insert':
									if (empty($_POST['ownerUid']) || !(is_numeric(trim($_POST['ownerUid']))) ){
										$this->content .= '<strong><span style="color:red;">Error: You should set at at least an owner id for the inserted records!</span></strong>';
										$this->content.=$this->doc->section('Information',$content,0,1);
									}
									else{
										$this->doTheCheck();
										$this->insertMissing();
										$this->content .= '<strong>Finished</strong>';
										$this->content.=$this->doc->section('Choose further action',$content,0,1);
									}
									break;
								case 'deleteInDB':
										$this->doTheCheck();
										$this->removeFromDB();
										$this->content .= '<strong>Finished</strong>';
										$this->content.=$this->doc->section('Choose further action',$content,0,1);
									break;
								case 'deleteFS':
										$this->doTheCheck();
										$this->removeFromFS();
										$this->content .= '<strong>Finished</strong>';
										$this->content.=$this->doc->section('Choose further action',$content,0,1);
									;
									break;
								default:
									$this->content.=$this->doc->section('Information',$content,0,1);
									break;
							}

						break;
						case 2:
							$content .= 'Its very important that you select your <strong>root storage folder</strong> on the left in the page tree! If you do not, the result is <strong>completely wrong </strong>and you may <strong>lose your files!</strong><br/><br/>';
							$content.='<form action="">
								<p>Choose the action please:</p>
								<p>
									<input type="radio" name="action" checked="checked" value="dryRun"/> Only do dry run, show the actions that would be taken. <br/>
									<input type="radio" name="action" value="upgrade"/> Upgrade: Move files to new created folders.<br/>
								</p><br/>
								      <input type="submit" value="Go!"/>
								</form>

								<hr />';
							switch($_POST['action']){
								case 'dryRun':
									$this->upgradeFromOldVersion(false);
									$this->content.=$this->doc->section('Results from dry run',$this->verbose,0,2);
									$this->content.=$this->doc->section('Choose further action',$content,0,1);
									break;
								case 'upgrade':
									$this->upgradeFromOldVersion(true);
									$this->content.=$this->doc->section('Results from the upgrade process',$this->verbose,0,2);
									$this->content.=$this->doc->section('Choose further action',$content,0,1);
								break;

								default:
								$this->content.=$this->doc->section('Information',$content,0,1);
								break;
							}
					}
				}

				function getCheckResults(){
					$resultContent = '<br/><strong>Folders recorded in the database, but not existing on filesystem:</strong><br/>';
					if (!empty($this->missingFoldersFS)){
						$tmpFoldersMissingFS = array();
						foreach ($this->missingFoldersFS as $curFolder){
							array_push($tmpFoldersMissingFS,$curFolder['fullPath']);
						}
						$resultContent  .= $this->printArrAsListHtml($tmpFoldersMissingFS);
					}
					else
						$resultContent  .= ' <span style="color:green;"> ->none found, seems fine.</span><br/>';

					$resultContent  .= '<br/><strong>Folders existing on filesystem, but not recorded in database:</strong><br/>';
					if (!empty($this->missingFoldersDB)){
						$resultContent  .= $this->printArrAsListHtml($this->missingFoldersDB);
					}
					else
						$resultContent  .= '<span style="color:green;">  ->none found, seems fine.</span><br/>';

					$resultContent  .= '<br/><strong>Files recorded in database, but not existing on filesystem:</strong><br/>';
					if (!empty($this->missingFilesFS)){
						$tmpFilesMissingFS = array();
						foreach ($this->missingFilesFS as $curFile){
							array_push($tmpFilesMissingFS,$curFile['fpath'].$curFile['fname']);
						}
						$resultContent .= $this->printArrAsListHtml($tmpFilesMissingFS);
					}
					else
						$resultContent  .= '<span style="color:green;">  ->none found, seems fine.</span><br/>';

					$resultContent  .= '<br/><strong>Files existing on filesystem, but not recorded in database:</strong><br/>';
					if (!empty($this->missingFilesDB)){
						$resultContent  .= $this->printArrAsListHtml($this->missingFilesDB);
					}
					else
						$resultContent  .= '<span style="color:green;">  ->none found, seems fine.</span><br/>';

					return $resultContent.'<br/><hr/>';
				}

				function printArrAsListHtml($theArray){
					$tmpContent = '<ul>';
					foreach ($theArray as $curEntry){
						$tmpContent .= '<li>'.$curEntry.'</li>';
					}
					$tmpContent .= '</ul>';
					return $tmpContent;
				}

				function doTheCheck(){
					$this->folders = array();
					$this->files = array();
					$this->getFolderFileStructureFromDB($_GET['id']);
// 					echo t3lib_div::view_array($this->folders);
// 					echo t3lib_div::view_array($this->files);
					$this->missingFoldersFS = array();
					$this->okFoldersFS = array();
					$this->checkFoldersFS();

// 					echo "<br>ok folders";
// 					echo t3lib_div::view_array($this->okFoldersFS);
// 					echo "<br>missing folders on fs";
// 					echo t3lib_div::view_array($this->missingFoldersFS);

					$this->missingFilesFS = array();
					$this->okFilesFS = array();
					$this->checkFilesFS();

// 					echo "<br>ok files";
// 					echo t3lib_div::view_array($this->okFilesFS);
// 					echo "<br>missing files on fs";
// 					echo t3lib_div::view_array($this->missingFilesFS);

					$this->filesFS = array();
					$this->foldersFS = array();
					$this->getFolderFileStructureFromFS();
// 					echo t3lib_div::view_array($this->filesFS);
// 					echo "watch";
// 					echo t3lib_div::view_array($this->foldersFS);

					$this->missingFoldersDB = array();
					$this->missingFilesDB = array();
					$this->filterOutFolders();
					$this->filterOutFiles();
// 					echo "<br>missing folders in database:";
// 					echo t3lib_div::view_array($this->missingFoldersDB);
// 					echo "<br>missing files in database:";
// 					echo t3lib_div::view_array($this->missingFilesDB);

				}

				function checkFilesFS(){
					foreach ($this->files as $curFile){
						if (is_file($curFile['fpath'].$curFile['fname'])){
								array_push($this->okFilesFS,$curFile);
							}
							else{
								array_push($this->missingFilesFS,$curFile);
							}
						}
				}

				function checkFoldersFS(){
					foreach ($this->folders as $curFolder){
						if (is_dir($curFolder['fullPath'])){
							array_push($this->okFoldersFS,$curFolder);
						}
						else{
							array_push($this->missingFoldersFS,$curFolder);
						}
					}
				}

				function filterOutFolders(){
					//!TODO: We could also check against okFolders
					$tmpPathArr = array();
					foreach ($this->folders as $curFolder){
						array_push($tmpPathArr,$curFolder['fullPath']);
					}
					foreach ($this->foldersFS as $curFolder){
						if (!in_array($curFolder,$tmpPathArr)){
							array_push($this->missingFoldersDB,$curFolder);
						}
					}
				}

				function filterOutFiles(){
					//!TODO: We could also check against okFiles
					$tmpFileArr = array();
					foreach ($this->files as $curFile){
						array_push($tmpFileArr,$curFile['fpath'].$curFile['fname']);
					}
					foreach ($this->filesFS as $curFile){
						if (!in_array($curFile,$tmpFileArr)){
							array_push($this->missingFilesDB,$curFile);
						}
					}
				}

				function getFolderFileStructureFromDB($pid){
					$sql = "SELECT uid,pid,file FROM `tx_fileexplorer_files`
							WHERE deleted = 0 AND hidden = 0 AND pid = ".(int)$pid;
					$res = $GLOBALS['TYPO3_DB']->sql_query($sql);
					while( false != ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) ){
						$sourcePath = PATH_site.$this->conf['upload_folder'].$this->handleData->getFolderPath($row['pid'],$_GET['id']);
						array_push($this->files,array('uid' => $row['uid'], 'fname'=>$row['file'],'fpath'=>str_replace('//','/',$sourcePath)));
// 						print_r($sourcePath);

					}

					$sql = "SELECT title, uid, tx_fileexplorer_read, tx_fileexplorer_write, tx_fileexplorer_feCrUserId
							FROM pages AS t1
							WHERE doktype = 150 AND deleted = 0 AND pid = ".(int)$pid;
					$res = $GLOBALS['TYPO3_DB']->sql_query($sql);

					$i = 0;
					while( false != ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) ){
						if($i == 0)
							$i ++;

						$fullPath = PATH_site.$this->conf['upload_folder'].$this->handleData->getFolderPath($row['uid'],$_GET['id']);
// 						if (substr($fullPath,-1,1) === "/"){
// 							$fullPath = substr($fullPath,0,-1);
// 						}
						array_push($this->folders,array('uid' => $row['uid'], 'fullPath' => $fullPath));
						$this->getFolderFileStructureFromDB($row['uid']);
					}
				}

				function getFolderFileStructureFromFS(){
// 					print_r($this->conf['trash_folder']);
					if (!empty($this->conf['trash_folder']))
						$excludeTrashPath = PATH_site.$this->conf['trash_folder'];

					$this->filesFoldersFS = t3lib_div::getAllFilesAndFoldersInPath(array(),PATH_site.$this->conf['upload_folder'],'',1,99);
					foreach ($this->filesFoldersFS as $curEntry){
						//filter out trash folders and files
						//!TODO: There is a bug here!!!
						if (!empty($excludeTrashPath) && false !== strpos($excludeTrashPath,$curEntry)){
							continue;
						}
						if (substr($curEntry,-1,1) === '/'){ //its a path
							array_push($this->foldersFS,$curEntry);
						}
						else{
							array_push($this->filesFS,$curEntry);
						}
					}
					//remove the first path as it is the upload folder itself, we assume that this one is existing
 					array_shift($this->foldersFS);
				}

			function insertMissing(){
					//first insert the folder data records
					$relRootFolderId = $_GET['id'];
					foreach($this->missingFoldersDB as $curDirectory){
							//strip current directory to relative
						$curDirectory = str_replace(PATH_site.$this->conf['upload_folder'],'',$curDirectory);

						$this->verbose .= '<br>Inserting record for relative directory: '.$curDirectory;
						$folderSplit = explode('/',$curDirectory);
						//dive into that folder to get the new pid's
						$parentFolderId = $relRootFolderId;
							foreach($folderSplit AS $singleFolderName){
								if (empty($singleFolderName)) continue;
								//check if the folder already exists
								if ($this->handleData->getFolderId($singleFolderName,$parentFolderId)) {
									$parentFolderId = $this->handleData->getFolderId($singleFolderName,$parentFolderId);
									continue;
								}
 								$newPid = $this->handleData->storeFolderEntry($parentFolderId,$singleFolderName,$_POST['ownerUid'],explode(',',$_POST['groupReadUid']),explode(',',$_POST['groupWriteUid']));
// 								$this->verbose .='<br/>do store: '.$parentFolderId.','.$singleFolderName.','.$_POST['ownerUid'].','.explode(',',$_POST['groupReadUid']).','.explode(',',$_POST['groupWriteUid']);
								$parentFolderId = $newPid;
							}
// 						$this->verbose .='<br/>would store bl: '.$parentFolderId.','.$singleFolderName.','.$_GET['ownerUid'].','.explode(',',$_GET['groupReadUid']).','.explode(',',$_GET['groupWriteUid']);
						//$this->handleData->storeFolderEntry($folderPid,$folderTitle,$userId,$readPerm,$writePerm)
					}

					//now insert the files
// 							print_r($this->missingFilesDB);
					foreach($this->missingFilesDB as $curFile){
						//!TODO
						$curFileRelPath = str_replace(PATH_site.$this->conf['upload_folder'],'',$curFile);
						$folderSplit = explode('/',$curFileRelPath);
						$fileTitle = array_pop($folderSplit); //this removes the actual file name
// 								print_r($fileName);
						$parentFolderId = $relRootFolderId;
// 						print_r($folderSplit);
						foreach($folderSplit AS $singleFolderName){
							if (empty($singleFolderName)) continue;
// 							print_r($singleFolderName);
							//check if the folder already exists
							$parentFolderId = $this->handleData->getFolderId($singleFolderName,$parentFolderId);
							if ($parentFolderId==false){
								die('an error occured, path not found for file: '.$curFile);
							}
						}
						$this->verbose .= '<br/>Creating file record for file: '.$fileTitle.' with relative folder path: '.$curFileRelPath;
 						$this->handleData->storeFileEntry($_POST['ownerUid'],$fileTitle,$fileTitle,$parentFolderId,'');
					}
				}

				function removeFromDB(){
					foreach($this->missingFoldersFS as $curFolder){
						$this->verbose .= '<br/>Deleting folder record of not existing folder: '.$curFolder['fullPath'];
						$this->handleData->deleteFolder($curFolder['uid'],false);
					}

					foreach($this->missingFilesFS as $curFile){
// 						print_r($curFile);
						$this->verbose .= '<br/>Deleting file record of not existing file: '.$curFile['fname'];
						$this->handleData->deleteFile($curFile['uid'],false);
					}
				}

				function removeFromFS(){
					foreach ($this->missingFilesDB as $curFile){
						if(unlink($curFile)){
							$this->verbose .= '<br/>Successfully deleted file from fs: '.$curFile;
						}
						else{
							$this->verbose .= '<br/>Could not delete file from fs: '.$curFile;
						}
					}
					$this->missingFoldersFS = array_reverse($this->missingFoldersFS);
					foreach ($this->missingFoldersDB as $curFolder){
						if (rmdir($curFolder)){
							$this->verbose .= '<br/>Successfully deleted folder from fs: '.$curFolder;
						}
						else{
							$this->verbose .= '<br/>Could not delete folder from fs: '.$curFolder;
						}
					}
				}

				function loadTS($pageUid) {
					$sysPageObj = t3lib_div::makeInstance('t3lib_pageSelect');
					$rootLine = $sysPageObj->getRootLine($pageUid);
					$TSObj = t3lib_div::makeInstance('t3lib_tsparser_ext');
					$TSObj->tt_track = 0;
					$TSObj->init();
					$TSObj->runThroughTemplates($rootLine);
					$TSObj->generateConfig();
					$this->conf = $TSObj->setup['plugin.'][$this->prefixIdPlugin.'.'];
				}


				function upgradeFromOldVersion($really){
					//get all files with path
					$this->folders = array();
					$this->files = array();
					$this->getFolderFileStructureFromDB($_GET['id']);
					$this->missingFoldersFS = array();
					$this->okFoldersFS = array();
					//check if folder exists
					$this->checkFoldersFS();
					//create folders
					foreach ($this->missingFoldersFS as $curFolder){
						if ($really){
							if (mkdir($curFolder['fullPath'])) {
								$this->verbose .= 'created folder: '.$curFolder['fullPath'].'<br/>';
							}
						}
						else
							$this->verbose .= 'would create folder: '.$curFolder['fullPath'].'<br/>';
					}
					//move the files
					$this->missingFilesFS = array();
					$this->okFilesFS = array();
					$this->checkFilesFS();

					foreach ($this->missingFilesFS as $curFile){
							if ($really){
								if(rename(PATH_site.$this->conf['upload_folder'].$curFile['fname'],$curFile['fpath'].$curFile['fname']))
									$this->verbose .= 'moved file : '.$curFile['fname'].' to : '.$curFile['fpath'].'<br/>';
							}
							else
								$this->verbose .= 'would move file : '.$curFile['fname'].' to : '.$curFile['fpath'].'<br/>';

					}
				}

			}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/file_explorer_check/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/file_explorer_check/mod1/index.php']);
}



// Make instance:
$SOBE = t3lib_div::makeInstance('tx_fileexplorercheck_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();


?>