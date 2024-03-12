<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://qtechstudios.com
 * @since      1.0.0
 *
 * @package    Qts_Video_Testimonials
 * @subpackage Qts_Video_Testimonials/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Qts_Video_Testimonials
 * @subpackage Qts_Video_Testimonials/public
 * @author     QTech Studios <info@qtechstudios.com>
 */
class Qts_Video_Testimonials_Public {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->qts_settings = get_option( 'qts_settings' );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/qts-video-testimonials-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

	}

	/**
	 * Register all the public facing shortcodes.
	 *
	 * @since    1.0.0
	 */
	public function init_shortcodes() {
		add_shortcode( 'video_recorder', array( $this, 'tesitmonial_video_recorder' ) );
	}
	
	/**
	 * Login Check
	 *
	 * @since    1.0.0
	 */
    public function login_check() {
		if ( is_page( 'customer-dashboard' ) && !is_user_logged_in() ) {
        	wp_redirect( home_url( '/login/' ) );
        	exit();
    	}
	}
	
	/**
	 * Testimonial video recorder widget.
	 *
	 * @since    1.0.0
	 */
    public function tesitmonial_video_recorder( $atts ) {
		
		wp_enqueue_script( 'recorder', plugin_dir_url( __FILE__ ) . 'js/recorder.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'recorder-api', plugin_dir_url( __FILE__ ) . 'js/recorder-api.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'recorder-html5', plugin_dir_url( __FILE__ ) . 'js/recorder-html5.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/qts-video-testimonials-public.js', array( 'jquery' ), $this->version, false );
		
		$selectable_questions = $this->qts_settings['selectable_questions'];
		$nochange_copy = $this->qts_settings['nochange'];
		$blurface_copy = $this->qts_settings['blurface'];
		$changevoice_copy = $this->qts_settings['changevoice'];
		$review_message = $this->qts_settings['review'];

		$script_data = array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'security' => wp_create_nonce('qts-video-testimonial'),
			'selectable_questions' => $selectable_questions,
			'review_message' => $review_message,
		);
		wp_localize_script( $this->plugin_name, 'qts_object', $script_data);
		
		$terms = get_terms(array(
		    'taxonomy'   => 'question-groups',
		    'hide_empty' => true,
		));
		$html = '<div class="qts-questions-wrapper">';
		foreach($terms as $term){
			$args = array(
				'post_type' => 'questions',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'tax_query' => array(
					array(
						'taxonomy' => 'question-groups',
						'field'    => 'slug',
						'terms'    => $term->slug,
					)
				)
			);
		
			$questions = get_posts($args);
			if($term == reset($terms))
				$html .= '<ul class="qts-questions-container">';
			else
				$html .= '<ul class="qts-questions-container qts-hidden">';

			$html .= '<li class="qts-legend">'.$term->name.'</li>';
			foreach($questions as $question){
				$html .= '<li class="qts-question"><input type="checkbox" id="question-id-'.$question->ID.'"><label for="question-id-'.$question->ID.'">'.$question->post_title.'</label></li>';
			}
			$html .= '</ul>';
		}
		$html .= <<<EOD
			<div class="qts-questions-control">
				<p>You have selected <span id="qts-selected">0</span> questions out of <span id="qts-selectable">$selectable_questions</span></p>
				<button class="qts-nav-btn prev qts-hidden">Previous</button>
				<button class="qts-nav-btn next">Next</button>
			</div>
			<div class="qts-control-info qts-hidden">
				<p>Do you want to remain anonymous?</p>
				<p><input type="radio" name="anonymous" id="nochange" value="0" checked><label for="nochange">$nochange_copy</label></p>
				<p><input type="radio" name="anonymous" id="blurface" value="1"><label for="blurface">$blurface_copy</label></p>
				<p><input type="radio" name="anonymous" id="both" value="2"><label for="both">$changevoice_copy</label></p>
				<button class="qts-save-btn">Start Recording Answers for Selected Questions</button>
			</div>
		EOD;
		$html .= '</div>';

		$recorderHtml = <<<EOD
			<div id="avRecorder-fallback-ajax-wrapper"></div>
			<div id="avRecorder" class="qts-hidden">
				<div class='av-recorder panel panel-primary'>
				<div class="loading-overlay" style='display: none;'></div>
					<div class='av-recorder-preview panel-body' style='display: none;'>
						<div class='loader' style='display: none;'></div>
						<canvas class='av-recorder-meter' style='display: none;'></canvas>
						<video class='av-recorder-video' style='display: none;'></video>
						<audio class='av-recorder-audio' controls='' style='display: none;'></audio>
					</div>
					<div class='av-recorder-progress progress' style='display: none;'>
						<div class='progress-bar' role='progressbar'></div>
					</div>
					<div class='panel-heading'>
						<p class='av-recorder-status panel-title'>Click 'Agree' to enable your mic.</p>
					</div>
					<div class='panel-footer'>
						<div class='av-recorder-controls'>
							<button	class='av-recorder-enable' title='Click to enable your mic.'>Agree</button>
							<button class='av-recorder-review' title='Click to review the recorded video.' style='display: none;'> Review </button>
							<button class='av-recorder-record' title='Click to start recording.' style='display: none;'> Record </button>
							<button class='av-recorder-next' title='Go to next question.' style='display: none;'> Submit Answer </button>
							<button class='av-recorder-play' title='Click to play recording.' style='display: none;'> Play </button>
							<button class='av-recorder-stop' title='Click to stop recording.' style='display: none;'> Stop </button>
							<button class='av-recorder-settings' title='Click to access settings.' style='display: none;'> Settings </button>
							<button class='av-recorder-enable-audio' title='Click to enable your mic.' style='display: none;'> Audio </button>
							<button class='av-recorder-enable-video' title='Click to enable your camera.' style='display: none;'> Video </button>
						</div>
					</div>
				</div>
			</div>
			EOD;

        return $html.''.$recorderHtml;
    }

}
