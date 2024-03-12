<?php
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://qtechstudios.com
 * @since      1.0.0
 *
 * @package    Qts_Video_Testimonials
 * @subpackage Qts_Video_Testimonials/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Qts_Video_Testimonials
 * @subpackage Qts_Video_Testimonials/admin
 * @author     QTech Studios <info@qtechstudios.com>
 */
class Qts_Video_Testimonials_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The settings of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $qts_settings    array of settings saved.
	 */
	private $qts_settings;

	/**
	 * The s3 client object (SDK).
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      object    $s3client    The S3 Client object.
	 */
	private $s3client;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->qts_settings = get_option( 'qts_settings' );

		if( !empty($this->qts_settings['s3bucket']) && !empty($this->qts_settings['s3key']) && !empty($this->qts_settings['s3secret'])  ){
			$this->s3client = new S3Client([
        	    'region' => 'us-east-1',
        	    'version' => 'latest',
        	    'credentials' => array(
        	        'key' => $this->qts_settings['s3key'],
        	        'secret' => $this->qts_settings['s3secret']
        	    )
        	]);
		}else{
			add_action('admin_notices', function(){
				echo '<div class="notice notice-error"><p>Video Testimonials: Please setup S3 Bucket Credentials <a href="'.admin_url('edit.php?post_type=questions&page=testimonials-settings').'">here</a> otherwise plugin will not work properly.</p></div>';
			});
		}
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Qts_Video_Testimonials_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Qts_Video_Testimonials_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/qts-video-testimonials-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Qts_Video_Testimonials_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Qts_Video_Testimonials_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/qts-video-testimonials-admin.js', array( 'jquery' ), $this->version, false );

	}
	
	/**
	 * Video Testimonials' Plugin Menu
	 *
	 * @since    1.0.0
	 */
	function qts_admin_menu() {
		add_submenu_page(
			'edit.php?post_type=questions',
			'Finalized Testimonials',
			'Finalized Testimonials',
			'manage_options',
			'finalized-testimonials',
			array( $this, 'qts_finalized_testimonials' )
		);

		add_submenu_page(
			'edit.php?post_type=questions',
			'Blur Face',
			'Blur Face',
			'manage_options',
			'blurface-testimonials',
			array( $this, 'qts_blurface_testimonials' )
		);

		add_submenu_page(
			'edit.php?post_type=questions',
			'Blur Face and Voice Change',
			'Blur Face and Voice Change',
			'manage_options',
			'both-testimonials',
			array( $this, 'qts_both_testimonils' )
		);

		add_submenu_page(
			'edit.php?post_type=questions',
			'Settings',
			'Settings',
			'manage_options',
			'testimonials-settings',
			array( $this, 'qts_settings_page' )
		);
	}
	
	/**
	 * Finalized Testimonials Listing Page.
	 *
	 * @since    1.0.0
	 */
	function qts_finalized_testimonials() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-qts-video-testimonials-table.php';
		$blur_face = new Qts_Video_Testimonials_Table('finalized');
		$blur_face->prepare_items();
		?>
		<div id="qts-video-container" style="display:none;"><p class="close">x</p><iframe src="" seamless=""></iframe></div>
		<div class="wrap">
    		<h2>Finalized Testimonials</h2>
    		<form method="post">
        		<input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>" />
        		<?php $blur_face->display(); ?>
    		</form>
		</div>
		<?php
	}
	
	/**
	 * Blur face only Listing Page.
	 *
	 * @since    1.0.0
	 */
	function qts_blurface_testimonials() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-qts-video-testimonials-table.php';
		$blur_face = new Qts_Video_Testimonials_Table('blur-face');
		$blur_face->prepare_items();
		?>
		<div id="qts-video-container" style="display:none;"><p class="close">x</p><iframe src="" seamless=""></iframe></div>
		<div class="wrap">
    		<h2>Blur face only Testimonials</h2>
    		<form method="post">
        		<input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>" />
        		<?php $blur_face->display(); ?>
    		</form>
		</div>
		<?php
	}
	
	/**
	 * Both, blur face and change voice Testimonials Listing Page.
	 *
	 * @since    1.0.0
	 */
	function qts_both_testimonils() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-qts-video-testimonials-table.php';
		$blur_face_change_voice = new Qts_Video_Testimonials_Table('blur-face-change-voice');
		$blur_face_change_voice->prepare_items();
		?>
		<div id="qts-video-container" style="display:none;"><p class="close">x</p><iframe src="" seamless=""></iframe></div>
		<div class="wrap">
    		<h2>Blur face and change voice Testimonials</h2>
    		<form method="post">
        		<input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>" />
        		<?php $blur_face_change_voice->display(); ?>
    		</form>
		</div>
		<?php
	}
	
	/**
	 * Testimonials Listing Page.
	 *
	 * @since    1.0.0
	 */
	function qts_testimonials_settings() {
	}

	/**
	 * Register the Post Type: Questions.
	 *
	 * @since    1.0.0
	 */
	function post_type_questions() {
	
		$labels = [
			"name" => esc_html__( "Video Testimonials", "qts-video-testimonials" ),
			"singular_name" => esc_html__( "Questions", "qts-video-testimonials" ),
			"menu_name" => esc_html__( "Video Testimonials", "qts-video-testimonials" ),
			"all_items" => esc_html__( "All Questions", "qts-video-testimonials" ),
			"add_new" => esc_html__( "Add Question", "qts-video-testimonials" ),
			"add_new_item" => esc_html__( "Add Question", "qts-video-testimonials" ),
			"edit_item" => esc_html__( "Edit Question", "qts-video-testimonials" ),
			"new_item" => esc_html__( "New Question", "qts-video-testimonials" ),
			"view_item" => esc_html__( "View Question", "qts-video-testimonials" ),
			"view_items" => esc_html__( "View Questions", "qts-video-testimonials" ),
			"search_items" => esc_html__( "Search Questions", "qts-video-testimonials" ),
			"not_found" => esc_html__( "No Question Found", "qts-video-testimonials" ),
			"not_found_in_trash" => esc_html__( "No Questions Found in Trash", "qts-video-testimonials" ),
			"parent" => esc_html__( "Parent Question", "qts-video-testimonials" ),
			"parent_item_colon" => esc_html__( "Parent Question", "qts-video-testimonials" ),
		];
	
		$args = [
			"label" => esc_html__( "Questions", "qts-video-testimonials" ),
			"labels" => $labels,
			"description" => "",
			"public" => true,
			"publicly_queryable" => false,
			"show_ui" => true,
			"show_in_rest" => false,
			"rest_base" => "",
			"rest_controller_class" => "WP_REST_Posts_Controller",
			"rest_namespace" => "wp/v2",
			"has_archive" => false,
			"show_in_menu" => true,
			"show_in_nav_menus" => false,
			"delete_with_user" => false,
			"exclude_from_search" => true,
			"capability_type" => "post",
			"map_meta_cap" => true,
			"hierarchical" => false,
			"can_export" => false,
			"rewrite" => false,
			"query_var" => false,
			"menu_icon" => "dashicons-format-video",
			"supports" => [ "title" ],
			"taxonomies" => [ "question-groups" ],
			"show_in_graphql" => false,
		];
	
		register_post_type( "questions", $args );
	}

	/**
	 * Register the Taxonomy: Question Groups.
	 *
	 * @since    1.0.0
	 */
	function taxonomy_question_groups() {

		$labels = [
			"name" => esc_html__( "Question Groups", "qts-video-testimonials" ),
			"singular_name" => esc_html__( "Question Group", "qts-video-testimonials" ),
		];
	
		
		$args = [
			"label" => esc_html__( "Question Groups", "qts-video-testimonials" ),
			"labels" => $labels,
			"public" => true,
			"publicly_queryable" => false,
			"hierarchical" => true,
			"show_ui" => true,
			"show_in_menu" => true,
			"show_in_nav_menus" => false,
			"query_var" => true,
			"rewrite" => [ 'slug' => 'question-groups', 'with_front' => false, ],
			"show_admin_column" => true,
			"show_in_rest" => true,
			"show_tagcloud" => false,
			"rest_base" => "question-groups",
			"rest_controller_class" => "WP_REST_Terms_Controller",
			"rest_namespace" => "wp/v2",
			"show_in_quick_edit" => false,
			"sort" => false,
			"show_in_graphql" => false,
		];
		register_taxonomy( "question-groups", "questions", $args );
	}
	
	function write_question_here( $title ){
		$screen = get_current_screen();
	  
		if  ( 'questions' == $screen->post_type ) {
			 $title = 'Write question here..';
		}
	  
		return $title;
   	}

	public function recording_upload(){
		check_ajax_referer('qts-video-testimonial', 'security');
		$current_user = wp_get_current_user();
		$filename = ($current_user == 0) ? date('YmdHis')."_".rand(100,999).".webm" : $current_user->display_name."_".date('YmdHis').".webm";
		
		if(intval($_REQUEST['anonymous']) === 0){
			$fullpath = "finalized/".$filename;
		}elseif(intval($_REQUEST['anonymous']) === 1){
			$fullpath = "blur-face/".$filename;
		}else{
			$fullpath = "blur-face-change-voice/".$filename;
		}
		
		if (isset($_FILES['mediaBlob'])){
			// File temp source 
            $file_temp_src = $_FILES["mediaBlob"]["tmp_name"]; 
             
            if(is_uploaded_file($file_temp_src)){
                // Upload file to S3 bucket 
                try { 
                    $result = $this->s3client->putObject([ 
                        'Bucket' => $this->qts_settings['s3bucket'],
                        'Key'    => $fullpath,
                        'ACL'    => 'public-read',
                        'SourceFile' => $file_temp_src,
                        'ContentType' => "video/webm",
    					'Metadata'   => array(
    					    'question' => $_REQUEST['question']
    					)
                    ]);

					// $objectURL = $result->get('ObjectURL');
                     
                    // if(!empty($objectURL)) {
					// 	error_log("Uploaded Object URL: ".$objectURL); 
                    // } 
                } catch (S3Exception $e) { 
                    error_log("S3Exception (Upload): ".$e->getMessage()); 
                }
			}
		}else{
			wp_send_json_error();
		}

		wp_send_json_success();
	}

	public function qts_settings_page() {
	?>
		<div class="wrap">
			<h2>Settings</h2>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'qts_settings_group' );
					do_settings_sections( 'qts-settings-admin' );
					submit_button();
				?>
			</form>
		</div>
	<?php }

	public function qts_settings_init() {
		register_setting(
			'qts_settings_group',
			'qts_settings',
			array( $this, 'settings_sanitize' )
		);

		add_settings_section(
			'settings_setting_section',
			'',
			'',
			'qts-settings-admin'
		);

		add_settings_field(
			'selectable_questions',
			'Number of Selectable Questions',
			array( $this, 'selectable_questions_callback' ),
			'qts-settings-admin',
			'settings_setting_section',
		);

		add_settings_field(
			's3bucket',
			'S3 Bucket Name',
			array( $this, 's3bucket_callback' ),
			'qts-settings-admin',
			'settings_setting_section',
		);

		add_settings_field(
			's3key',
			'S3 Bucket Key',
			array( $this, 's3key_callback' ),
			'qts-settings-admin',
			'settings_setting_section',
		);

		add_settings_field(
			's3secret',
			'S3 Bucket Secret',
			array( $this, 's3secret_callback' ),
			'qts-settings-admin',
			'settings_setting_section',
		);

		add_settings_field(
			'nochange',
			'No change option copy',
			array( $this, 'nochange_callback' ),
			'qts-settings-admin',
			'settings_setting_section',
		);

		add_settings_field(
			'blurface',
			'Blur face option copy',
			array( $this, 'blurface_callback' ),
			'qts-settings-admin',
			'settings_setting_section',
		);

		add_settings_field(
			'changevoice',
			'Blur face and change voice option copy',
			array( $this, 'changevoice_callback' ),
			'qts-settings-admin',
			'settings_setting_section',
		);

		add_settings_field(
			'review',
			'Review message copy',
			array( $this, 'review_callback' ),
			'qts-settings-admin',
			'settings_setting_section',
		);
	}

	public function settings_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['selectable_questions'] ) ) {
			$sanitary_values['selectable_questions'] = sanitize_text_field( $input['selectable_questions'] );
		}
		if ( isset( $input['s3bucket'] ) ) {
			$sanitary_values['s3bucket'] = sanitize_text_field( $input['s3bucket'] );
		}
		if ( isset( $input['s3key'] ) ) {
			$sanitary_values['s3key'] = sanitize_text_field( $input['s3key'] );
		}
		if ( isset( $input['s3secret'] ) ) {
			$sanitary_values['s3secret'] = sanitize_text_field( $input['s3secret'] );
		}
		if ( isset( $input['blurface'] ) ) {
			$sanitary_values['blurface'] = sanitize_text_field( $input['blurface'] );
		}
		if ( isset( $input['nochange'] ) ) {
			$sanitary_values['nochange'] = sanitize_text_field( $input['nochange'] );
		}
		if ( isset( $input['changevoice'] ) ) {
			$sanitary_values['changevoice'] = sanitize_text_field( $input['changevoice'] );
		}
		if ( isset( $input['review'] ) ) {
			$sanitary_values['review'] = sanitize_text_field( $input['review'] );
		}

		return $sanitary_values;
	}

	public function selectable_questions_callback() {
		printf(
			'<input class="regular-text" type="number" max=20 min=1 name="qts_settings[selectable_questions]" id="selectable_questions" value="%s">',
			isset( $this->qts_settings['selectable_questions'] ) ? esc_attr( $this->qts_settings['selectable_questions']) : ''
		);
	}

	public function s3bucket_callback() {
		printf(
			'<input class="regular-text" type="text" name="qts_settings[s3bucket]" id="s3bucket" value="%s">',
			isset( $this->qts_settings['s3bucket'] ) ? esc_attr( $this->qts_settings['s3bucket']) : ''
		);
	}

	public function s3key_callback() {
		printf(
			'<input class="regular-text" type="text" name="qts_settings[s3key]" id="s3key" value="%s">',
			isset( $this->qts_settings['s3key'] ) ? esc_attr( $this->qts_settings['s3key']) : ''
		);
	}

	public function s3secret_callback() {
		printf(
			'<input class="regular-text" type="password" name="qts_settings[s3secret]" id="s3secret" value="%s">',
			isset( $this->qts_settings['s3secret'] ) ? esc_attr( $this->qts_settings['s3secret']) : ''
		);
	}

	public function nochange_callback() {
		printf(
			'<input class="regular-text" type="text" name="qts_settings[nochange]" id="nochange" value="%s">',
			isset( $this->qts_settings['nochange'] ) ? esc_attr( $this->qts_settings['nochange']) : ''
		);
	}

	public function blurface_callback() {
		printf(
			'<input class="regular-text" type="text" name="qts_settings[blurface]" id="blurface" value="%s">',
			isset( $this->qts_settings['blurface'] ) ? esc_attr( $this->qts_settings['blurface']) : ''
		);
	}

	public function changevoice_callback() {
		printf(
			'<input class="regular-text" type="text" name="qts_settings[changevoice]" id="changevoice" value="%s">',
			isset( $this->qts_settings['changevoice'] ) ? esc_attr( $this->qts_settings['changevoice']) : ''
		);
	}

	public function review_callback() {
		printf(
			'<input class="regular-text" type="text" name="qts_settings[review]" id="review" value="%s">',
			isset( $this->qts_settings['review'] ) ? esc_attr( $this->qts_settings['review']) : ''
		);
	}
}
