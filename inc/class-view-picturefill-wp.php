<?php
defined('ABSPATH') OR exit;
if(!class_exists('View_Picturefill_WP')){
  class View_Picturefill_WP{

    // Object variables
    private $model;
    private $image_sizes = array();
    private $original_image = '';
    private $image_attributes = array();
    private $image_attachment_data = array();

    // Static methods
    static function standardize_img_tags($html){
      return apply_filters('picturefill_wp_content_html', preg_replace('/(<img[^<]*?)(?:>|\/>|\s\/>)/', '$1 />', $html));
    }

    // Constructor, get data from model object
    public function __construct($model_picturefill_wp){
      $this->model = $model_picturefill_wp;
      $this->original_image = html_entity_decode(self::standardize_img_tags($this->model->get_image_xml()), ENT_COMPAT, 'UTF-8');
      $this->image_attributes = apply_filters('picturefill_wp_image_attributes', $this->model->get_image_attributes());
      $this->image_attachment_data = $this->model->get_image_attachment_data();
      $this->image_sizes = $this->model->get_image_sizes();

    }

    // Methods to render data in the templates
    public function generate_source_list(){
      $output = '';
      $template_data = array();
      $image_output_queue = $this->image_sizes;

      foreach($this->model->get_srcset_array() as $source_array){
        $output .= $this->render_template('source', $template_data);
      }

      return apply_filters('picturefill_wp_source_list', $output);
    }

    public function get_image_attribute_string(){
      $image_attributes = $this->image_attributes;

      $output_string = '';

      $ignore_attributes = array(
        'attachment_id'
      );

      if(!$this->model->get_option('output_src')){
        $ignore_attributes[] = 'src';
      }

      if(!$this->model->get_option('explicit_width')){
        $ignore_attributes[] = 'width';
        $ignore_attributes[] = 'height';
      }

      foreach($image_attributes as $attribute => $value){
        $output_string .= !empty($value) && !is_array($value) && !in_array($attribute, $ignore_attributes) ? ' ' . $attribute . '="' . html_entity_decode($value, ENT_COMPAT, 'UTF-8') . '"' : '';
      }

      return apply_filters('picturefill_wp_image_attribute_string', $output_string);
    }

    public function get_original_image_src(){
      return $this->image_attributes['src'];
    }

    public function get_original_image(){
      return $this->original_image;
    }

    public function get_image_src($image_size){
      return $this->image_attachment_data[$image_size]['url'];
    }

    public function format_srcset($sizes){
      $srcset_components = array();

      foreach($sizes as $size){
        $resolution = $this->model->get_srcset_resolution($size);
        $srcset_components[] = $this->model->get_image_url($size) . $resolution;
      }

      return implode(', ', $srcset_components);
    }

    public function get_media_query($srcset_array){
      foreach($srcset_array as $image_size){
        if('@2x' !== substr($image_size, -3)){
          $width = $image_size === $this->image_attributes['size'][1] ? $this->image_attributes['width'] : $this->image_attachment_data[$image_size]['width'];
          $breakpoint = $width + 20;
        }
      }
      return '(min-width: ' . apply_filters('picturefill_wp_media_query_breakpoint', $breakpoint, $image_size, $width, $this->image_attributes, $this->image_attachment_data, $this->image_sizes) . 'px)';
    }

    public function get_sizes(){
      if($this->model->get_option('use_sizes')){
        return !empty($this->model->get_sizes_string()) ? ' sizes="' . $this->model->get_sizes_string() . '"' : ' sizes="100vw"';
      }
    }

    // Render templates
    public function render_template($template, $template_data = array()){
      if(1 === count($this->model->get_srcset_array()) || $this->model->get_option('use_sizes')){
        $template = 'image';
        $template_data = $this->model->get_srcset_array()[0];
      }
      $template_path = apply_filters('picturefill_wp_template_path', PICTUREFILL_WP_PATH . 'inc/templates/');
      $template_file_path = apply_filters('picturefill_wp_' . $template . '_template_file_path', $template_path . $template . '-template.php', $template, $template_path);
      $view = $this;
      $template_data = apply_filters('picturefill_wp_' . $template . '_template_data', $template_data);

      ob_start();
      include($template_file_path);
      return apply_filters('picturefill_wp_' . $template . '_template', ob_get_clean());
    }
  }
}
