<?php

    /**
     * MindFlipBook main class
     *
     * @since       1.0.0
     */

    // Exit if accessed directly
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

class MindFlipBook
{
	
	/**
	 *
	 * a key sera usada para guardar como chave do pdf
	 * conseguir aumentar tamanho da imagem que está sendo gerada
	 *
	 */
	
	private static $instance = null;
	
	/**
	* Get active instance
	*
	* @access      public
	* @since       1.0.0
	* @return      self::$instance
	*/
   public static function instance()
   {
	   if ( ! self::$instance ) {
		   self::$instance = new self;
		   self::$instance->hooks();
	   }

	   return self::$instance;
   }
   
   /**
	* Run action and filter hooks
	*
	* @access      private
	* @since       1.0.0
	* @return      void
	*/
   private function hooks()
   {

       add_action('admin_menu', array( $this, 'setup_menu' ) );
	   add_action('wp_ajax_mind_edit', array($this, 'edit_flipbook'));
	   add_action('wp_ajax_mind_delete', array($this, 'delete_flipbook'));
	
   }
   
   public function setup_menu()
   {
	   //add_menu_page
	   add_options_page( 'Mind FlipBook Admin Panel', 'Mind FlipBook', 'manage_options', 'mind-flipbook', array( $this, 'admin_page' ) );
   }
   
  public function admin_page()
   {
	
		$return = $this->generate_flipbpook();
		if($return===true) {
			$message = 'success';
		} else if($return===false) {
			$message = 'error';
		}
		
		$table = '';
		$tpl = str_replace('{url-view}', plugins_url( 'public/magazine?k={key}', __DIR__ ), file_get_contents(plugin_dir_path( __DIR__ ) . 'public/view/table-admin.tpl'));
		$rowns = $this->getInsertedFlipBooks();
		$count = 1;
		foreach($rowns as $postId=>$item) {
			
			$content = str_replace('{num}',$postId, $tpl);
			$content = str_replace('{file}', basename($item['_MIND-PDF_']), $content);
			$content = str_replace('{key}', $item['_MIND-PDF-KEY_'], $content);
			$content = str_replace('{description}', $item['_MIND-PDF-DESCRIPTION_'], $content);
			$table .= str_replace('{date}', $item['_MIND-PDF-DATE_'], $content);
			$count++;
		}
		
		$table = !$table ? '<td colspan="5">No FlipBook Found</td>' : $table;
		
		$html = file_get_contents(plugin_dir_path( __DIR__ ) . 'public/view/admin.tpl');
		$html = str_replace('{submit-button}', get_submit_button('Upload'),$html);
		$html = str_replace('{inserted-flibooks}', $table, $html);
		$html = str_replace('{url-site}', site_url(), $html);
		$html = str_replace('{url-plugin}', plugins_url(), $html);
		$html = str_replace('{message}', $message, $html);
		$html = str_replace('{wp-nonce-field}', wp_nonce_field('image-submission', '_wpnonce', true, false), $html);
		
		wp_enqueue_script('pdf-form-js', plugin_dir_url( __DIR__ ) . 'public/js/script.js', array('jquery'), '0.1.0', true);
		wp_enqueue_style('pdf-form-css', plugin_dir_url( __DIR__ ) . 'public/css/style.css', '0.1.0', true);
		
		$data = array(
                'upload_url' => admin_url('async-upload.php'),
                'ajax_url'   => admin_url('admin-ajax.php'),
                'nonce'      => wp_create_nonce('media-form')
            );

		wp_localize_script( 'pdf-form-js', 'su_config', $data );
		
		echo $html;
		
   }
   
   public function generate_flipbpook()
   {
		if(isset($_FILES['flipbook_pdf'])) {
                //$pdf = $_FILES['flipbook_pdf'];
				$uploaded = media_handle_upload('flipbook_pdf', 0);
               
                if(is_wp_error($uploaded)) {
                        echo "Error uploading file: " . $uploaded->get_error_message();
                } else {

					   require_once plugin_dir_path( __DIR__ ) . 'class/ManageFlipBook.class.php';
					   
					    $mind = new ManageFlipBook();
						$mind->setFileId($uploaded);
						$mind->setFile(get_attached_file($uploaded));
						if($mind->genereteFlipBook()) {
							
							$this->addRegister($uploaded, '_MIND-PDF_', get_attached_file($uploaded));
							$this->addRegister($uploaded, '_MIND-PDF-KEY_', $mind->getKey());
							$this->addRegister($uploaded, '_MIND-PDF-DATE_', date('Y-m-d H:i:s'));
							$this->addRegister($uploaded, '_MIND-PDF-DESCRIPTION_', $_POST['pdf-description']);
							
							//echo 'All done!<br />', $mind->getKey(), ' - ', $uploaded;
							return true;

						} else {
							//echo 'ops... problem!';
							return false;
						}
                }
        }
		
		return null;
	
   }
   
   private function addRegister($post_id, $meta_key, $meta_value)
   {
		global $wpdb;
		$sql = "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES ('{$post_id}', '{$meta_key}', '{$meta_value}')";
		$wpdb->query($sql);
   }
   
   private function getInsertedFlipBooks()
   {
		$flipbooks = array();
		global $wpdb;
		$sql = "SELECT * FROM {$wpdb->postmeta} WHERE meta_key IN ('_MIND-PDF_', '_MIND-PDF-KEY_', '_MIND-PDF-DATE_', '_MIND-PDF-DESCRIPTION_')";
		$result = $wpdb->get_results ( $sql );
		foreach ( $result as $item )   {
			$flipbooks[$item->post_id][$item->meta_key] = $item->meta_value;
		}
		
		return $flipbooks;
   }
   
   public function edit_flipbook()
   {
	
		if(!isset($_POST['action']) || !isset($_POST['value']) || !isset($_POST['key'])) {
			die('Something is wrong');
		}
		
		if(!is_dir(plugin_dir_path( __DIR__ ) . "flipbook-files/{$_POST['key']}")) {
			die('Flipbook does not exist');
		}
		
		$json = json_decode(file_get_contents(plugin_dir_path( __DIR__ ) . "flipbook-files/{$_POST['key']}/conf.json"));
		if(!isset($json->fileId)) {
			die('Config file of flipbook is wrong');
		}

		global $wpdb;
		$sql  = "UPDATE {$wpdb->postmeta} SET meta_value='{$_POST['value']}' WHERE post_id ={$json->fileId} AND meta_key='_MIND-PDF-DESCRIPTION_'";
		$wpdb->query($sql);
		
		$sql = "UPDATE {$wpdb->postmeta} SET meta_value='". date('Y-m-d H:i:s') ."' WHERE post_id ={$json->fileId} AND meta_key='_MIND-PDF-DATE_'";
		$wpdb->query($sql);
		
		die('ok');
		
   }
   
   public function delete_flipbook()
   {
	
		if(!isset($_POST['action']) || !isset($_POST['key'])) {
			die('Something is wrong');
		}
		
		if(!is_dir(plugin_dir_path( __DIR__ ) . "flipbook-files/{$_POST['key']}")) {
			die('Flipbook does not exist');
		}
		
		$json = json_decode(file_get_contents(plugin_dir_path( __DIR__ ) . "flipbook-files/{$_POST['key']}/conf.json"));
		if(!isset($json->fileId)) {
			die('Config file of flipbook is wrong');
		}

		global $wpdb;
		$sql  = "DELETE FROM {$wpdb->postmeta} WHERE post_id ={$json->fileId} ";
		if(!$wpdb->query($sql)) {
			die('Something was wrong when tried to delete(Database)');
		}
		
		require_once plugin_dir_path( __DIR__ ) . 'class/ManageFlipBook.class.php';
		
		$mind = new ManageFlipBook();
		$mind->setImagesDirectory(plugin_dir_path( __DIR__ ) . "flipbook-files/{$_POST['key']}");
		if($mind->deleteFlipBookDirectory()) {
			die('ok');
		} else {
			die('Something was worng when tried to delete(Files)');
		}
		
   }

}