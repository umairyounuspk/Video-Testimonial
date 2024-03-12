<?php
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
/**
 * @link       https://qtechstudios.com
 * @since      1.0.0
 *
 * @package    Qts_Video_Testimonials
 * @subpackage Qts_Video_Testimonials/includes
 */

/**
 * This class defines all code necessary to integrate custom WP List Table.
 *
 * @since      1.0.0
 * @package    Qts_Video_Testimonials
 * @subpackage Qts_Video_Testimonials/includes
 * @author     QTech Studios <info@qtechstudios.com>
 */
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Qts_Video_Testimonials_Table extends WP_List_Table {

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
     * The s3 client object (SDK).
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $type    blur face only or both.
     */
    private $type;

    public function __construct($type) {
        parent::__construct(array(
            'singular' => 'Testimonial',
            'plural'   => 'Testimonials',
            'ajax'     => false, // Enable AJAX support
        ));
        $this->type = $type;
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
		}
    }

    public function get_columns() {
        $columns = array(
            'cb'        => '<input type="checkbox" />',
            'Key'   => 'Fileame',
            'question'   => 'Question',
            'date'   => 'Date',
        );
        return $columns;
    }

    public function prepare_items() {
        
        $data = $this->get_objects_list($this->type);
        $testimonials = $data['Contents'];
        array_shift($testimonials);
        
        $testimonials = $this->process_bulk_action($testimonials);

        $columns = $this->get_columns();
        $this->_column_headers = array($columns);
        $this->items = $columns;

        $per_page = $this->get_items_per_page('items_per_page', 10);
        $current_page = $this->get_pagenum();
        $total_items = count($testimonials);

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ));

        $this->items = array_slice($testimonials, (($current_page - 1) * $per_page), $per_page);

    }

    public function column_default($item, $column_name) {
        return isset($item[$column_name]) ? $item[$column_name] : '';
    }

    public function column_cb($item) {
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['Key']);
    }

    public function column_Key($item) {
        $url = $this->s3client->getObjectUrl($this->qts_settings['s3bucket'], $item['Key']);

        if($this->type == 'finalized'){
            $filename = str_replace(array('finalized/', '.webm'), '', $item['Key']);
        }elseif($this->type == 'blur-face'){
            $filename = str_replace(array('blur-face/', '.webm'), '', $item['Key']);
        }else{
            $filename = str_replace(array('blur-face-change-voice/', '.webm'), '', $item['Key']);
        }

        return sprintf('<a href="%1$s" class="qts-video-link">%2$s</a>', $url, $filename);
    }

    public function column_question($item) {
        return sprintf('%s', $this->get_question($item['Key']));
    }

    public function column_date($item) {
        return sprintf('%s', $item['LastModified']->format('Y-m-d H:i:s'));
    }

    public function get_bulk_actions() {
        $actions = array(
            'delete' => 'Delete',
        );
        return $actions;
    }

    public function process_bulk_action($testimonials) {
        if ('delete' === $this->current_action()) {
            $keys = isset($_REQUEST['testimonial']) ? $_REQUEST['testimonial'] : array();
            if (is_array($keys) && !empty($keys)) {
                foreach ($keys as $key) {
                    try {
                        $result = $this->s3client->deleteObject([
                            'Bucket' => $this->qts_settings['s3bucket'],
                            'Key'    => $key
                        ]);
                        $index = array_search($key, array_column($testimonials, 'Key'));
                        unset($testimonials[$index]);
                    } catch (S3Exception $e) {
                        error_log("S3Exception (Delete): ".$e->getMessage());
                    }
                }
            }
        }

        return $testimonials;
    }

    public function delete_object($key){
        try {
            $result = $this->s3client->deleteObject([
                'Bucket' => $this->qts_settings['s3bucket'],
                'Key'    => $key
            ]);
            return true;
        } catch (S3Exception $e) {
            error_log("S3Exception (Delete): ".$e->getMessage());
            return false;
        }
    }

    public function get_question($key){
        try {
            $result = $this->s3client->headObject([
                'Bucket' => $this->qts_settings['s3bucket'],
                'Key'    => $key
            ]);
            return array_column($result->toArray(), 'question')[0];
        } catch (S3Exception $e) {
            error_log("S3Exception (Get Question): ".$e->getMessage());
        }
    }

    public function get_objects_list($prefix){
		try {
			$objects = $this->s3client->listObjectsV2([
				'Bucket' => $this->qts_settings['s3bucket'],
				"Delimiter" =>  "/",
				"Prefix" => $prefix."/",
			]);
			return $objects->toArray();
		} catch (S3Exception $e) {
            error_log("S3Exception (Object List): ".$e->getMessage());
		}
    }
}
?>
