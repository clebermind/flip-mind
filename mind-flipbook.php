<?php
/**
 * Mind FlipBook
 *
 * A simple, truly extensible and fully responsive options framework
 * for WordPress themes and plugins. Developed with WordPress coding
 * standards and PHP best practices in mind.
 *
 * Plugin Name:     Mind FlipBook
 * Plugin URI:      http://wordpress.org/plugins/mind-flipbook
 * Github URI:      https://github.com/clebermind/mind-flipbook
 * Description:     Mind Flipbook is a simple but powerfull flipbook generator and player from PDF file do not using third websites.
 * Author:          Cleber Mendes
 * Author URI:      https://github.com/clebermind/
 * Version:         1.0.0
 * Text Domain:     mind-flipbook
 * License:         GPL2+
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.txt
 *
 */

 ini_set('max_execution_time', 360);
 
if (!defined('ABSPATH')) {
    die( 'No script kiddies please!' );
}

require_once plugin_dir_path( __FILE__ ) . 'class/MindFlipBook.class.php';

MindFlipBook::instance();

/*
$split = new MindFlipBook();

$file = isset($_GET[f']) ? $_GET['f'] : null;

if(is_null($file)) {
    die('Have to pass the file name');
} else if(!is_file(__DIR__ . "/{$file}")) {
    die('Are you sure that it is a file???');
}
$split->setFile(__DIR__ . "/{$file}");
if($split->genereteFlipBook()) {
	echo 'all done!<br />', $split->getKey();
	
} else {
	echo 'ops... problem!';
}
*/

/*$split->setImagesDirectory(__DIR__ . /flipbook-files/f57754feab07147a4389fbeff5136f64');
if($split->deleteFlipBookDirectory()) {
	echo 'deleted';
} else {
	echo 'ops... problem!';
}*/