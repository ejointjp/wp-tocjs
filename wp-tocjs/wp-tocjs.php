<?php
/*
Plugin Name: WP TocJS
Plugin URI: http://e-joint.jp/works/wp-tocjs/
Description: A WordPress plugin that makes Table of Contents automatically.
Version: 0.3.1
Author: e-JOINT.jp
Author URI: http://e-joint.jp
Text Domain: wp-tocjs
Domain Path: /languages
License: GPL2
*/

/*  Copyright 2018 e-JOINT.jp (email : mail@e-joint.jp)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
     published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class Wp_Tocjs {
  private $options;
  private $version;
  private $textdomain;
  private $domainpath;
  public $default_options = array();

  public function __construct(){

    $this->set_datas();
    $this->set_default_options();
    $this->options = get_option('wptjs-setting');

    // 翻訳ファイルの読み込み
    // load_plugin_textdomain($this->textdomain, false, basename(dirname(__FILE__)) . '/languages');
    add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
    // 設定画面を追加
    add_action('admin_menu', array($this, 'add_plugin_page'));
    // 設定画面の初期化
    add_action('admin_init', array($this, 'page_init'));
    add_action('wp_enqueue_scripts', array($this, 'add_styles'));
    add_action('wp_enqueue_scripts', array($this, 'add_scripts'));
    add_action('admin_menu', array($this, 'add_custom_field'));
    add_action('save_post', array($this, 'save_custom_field'));
    add_shortcode('tocjs', array($this, 'generate_shortcode'));
    add_filter('the_content', array($this, 'the_content'));
    add_action('wp_footer', array($this, 'generate_scripts'), 20);
  }

  public function load_plugin_textdomain() {
    load_plugin_textdomain($this->textdomain, false, dirname(plugin_basename(__FILE__)) . $this->domainpath);
  }

  private function set_default_options() {
    $this->default_options = array(
      'src' => '.toc-src',
      'headings' => 'h2, h3',
      'min' => '2',
      'title' => __('Contents', $this->textdomain),
      'title_element' => 'h2',
      'excludes' => 'toc-exclude'
    );
  }

  private function set_datas() {
    $datas = get_file_data(__FILE__, array(
      'version' => 'Version',
      'textdomain' => 'Text Domain',
      'domainpath' => 'Domain Path'
    ));

    $this->version = $datas['version'];
    $this->textdomain = $datas['textdomain'];
    $this->domainpath = $datas['domainpath'];
  }

  // 設定画面を追加
  public function add_plugin_page() {

    add_options_page(
      __('WP TocJS', $this->textdomain),
      __('WP TocJS', $this->textdomain),
      'manage_options',
      'wptjs-setting',
      array($this, 'create_admin_page')
    );
  }

  // 設定画面を生成
  public function create_admin_page() { ?>
    <div class="wrap">
      <h2>WP TocJS</h2>
      <?php
      global $parent_file;
      if($parent_file != 'options-general.php') {
        require(ABSPATH . 'wp-admin/options-head.php');
      }
      ?>

      <form method="post" action="options.php">
      <?php
        settings_fields('wptjs-setting');
        do_settings_sections('wptjs-setting');
        submit_button();
      ?>
      </form>

      <h3><?php echo __('Display table of contents with shortcode', $this->textdomain); ?></h3>

      <p><?php echo __('The TOC is automatically displayed at the position of the more tag, but you can also display the TOC at the desired position with the shortcode.', $this->textdomain); ?></p>
      <p><?php echo __('Shortcode', $this->textdomain); ?>: <b>[tocjs]</b></p>
    </div>
  <?php
  }

  // 設定画面の初期化
  public function page_init(){
    register_setting('wptjs-setting', 'wptjs-setting');
    add_settings_section('wptjs-setting-section-id', '', '', 'wptjs-setting');

    add_settings_field(
      'src',
      __('Source element', $this->textdomain),
      array($this, 'src_callback'),
      'wptjs-setting',
      'wptjs-setting-section-id'
    );

    add_settings_field(
      'headings',
      __('Target headings', $this->textdomain),
      array($this, 'headings_callback'),
      'wptjs-setting',
      'wptjs-setting-section-id'
    );

    add_settings_field(
      'min',
      __('Minimum', $this->textdomain),
      array($this, 'min_callback'),
      'wptjs-setting',
      'wptjs-setting-section-id'
    );

    add_settings_field(
      'title',
      __('TOC Title', $this->textdomain),
      array($this, 'title_callback'),
      'wptjs-setting',
      'wptjs-setting-section-id'
    );

    add_settings_field(
      'title_element',
      __('TOC Title tag element', $this->textdomain),
      array($this, 'title_element_callback'),
      'wptjs-setting',
      'wptjs-setting-section-id'
    );

    add_settings_field(
      'exludes',
      __('Excluldes classname', $this->textdomain),
      array($this, 'excludes_callback'),
      'wptjs-setting',
      'wptjs-setting-section-id'
    );

    // add_settings_field('includes', __('Includes classname', $this->textdomain), array($this, 'includes_callback'), 'wptjs-setting', 'wptjs-setting-section-id');

    add_settings_field(
      'heading_number',
      __('Display number in heading', $this->textdomain),
      array($this, 'heading_number_callback'),
      'wptjs-setting',
      'wptjs-setting-section-id'
    );

    add_settings_field(
      'toc_number',
      __('Display number in TOC', $this->textdomain),
      array($this, 'toc_number_callback'),
      'wptjs-setting',
      'wptjs-setting-section-id'
    );

    add_settings_field(
      'nocss',
      __('Do not use plugin\'s CSS', $this->textdomain),
      array($this, 'nocss_callback'),
      'wptjs-setting',
      'wptjs-setting-section-id'
    );

    add_settings_field(
      'nojs',
      __('Do not use plugin\'s JS', $this->textdomain),
      array($this, 'nojs_callback'),
      'wptjs-setting',
      'wptjs-setting-section-id'
    );
  }

  public function nocss_callback() {
    $checked = isset($this->options['nocss']) ? checked($this->options['nocss'], 1, false) : '';
    ?><input type="checkbox" id="nocss" name="wptjs-setting[nocss]" value="1"<?php echo $checked; ?>><?php
  }

  public function nojs_callback() {
    $checked = isset($this->options['nojs']) ? checked($this->options['nojs'], 1, false) : '';
    ?><input type="checkbox" id="nojs" name="wptjs-setting[nojs]" value="1"<?php echo $checked; ?>>
    <small><?php echo __('As the TOC function becomes invalid, please do not usually disable it.', $this->textdomain); ?></small><?php
  }

  public function src_callback() {
    $value = isset($this->options['src']) ? $this->options['src'] : $this->default_options['src'];
    ?><input type="text" id="src" class="" name="wptjs-setting[src]" value="<?php echo $value; ?>">
    <small><?php echo __('The contents of the element specified here becomes the target area of the TOC.', $this->textdomain); ?></small>
    <small><?php echo __('In the format of a jQuery selector.', $this->textdomain); ?></small><?php
  }

  public function headings_callback() {
    $value = !empty($this->options['headings']) ? $this->options['headings'] : $this->default_options['headings'];
    ?><input type="text" id="headings" class="" name="wptjs-setting[headings]" value="<?php echo $value; ?>">
    <small><?php echo __('Please enter the target headings in h1 - h6 separated by comma.', $this->textdomain); ?></small><?php
  }

  public function min_callback() {
    $value = !empty($this->options['min']) ? $this->options['min'] : $this->default_options['min'];
    ?><input type="number" id="min" class="small-text" name="wptjs-setting[min]" value="<?php echo $value; ?>">
    <small><?php echo __('If the number of target headings is less than or equal to the value, the TOC is not displayed.', $this->textdomain); ?></small>
    <?php
  }

  public function title_callback() {
    $value = isset($this->options['title']) ? $this->options['title'] : __($this->default_options['title'], $this->textdomain);
    ?><input type="text" id="title" class="" name="wptjs-setting[title]" value="<?php echo $value; ?>"><?php
  }

  public function title_element_callback() {
    $value = !empty($this->options['title_element']) ? $this->options['title_element'] : $this->default_options['title_element'];
    ?><input type="text" id="title-element" class="small-text" name="wptjs-setting[title_element]" value="<?php echo $value; ?>"><?php
  }

  public function excludes_callback() {
    $value = isset($this->options['excludes']) ? $this->options['excludes'] : $this->default_options['excludes'];
    ?><input type="text" id="excludes" class="widefat" name="wptjs-setting[excludes]" value="<?php echo $value; ?>">
    <small><?php echo __('Please input as attribute name of class, not selector.', $this->textdomain); ?></small><?php
  }

  public function heading_number_callback() {
    $checked = isset($this->options['heading_number']) ? checked($this->options['heading_number'], 1, false) : true;
    ?><input type="checkbox" id="heading-number" name="wptjs-setting[heading_number]" value="1"<?php echo $checked; ?>><?php
  }

  public function toc_number_callback() {
    $checked = isset($this->options['toc_number']) ? checked($this->options['toc_number'], 1, false) : false;
    ?><input type="checkbox" id="toc-number" name="wptjs-setting[toc_number]" value="1"<?php echo $checked; ?>><?php
  }

  // スタイルシートの追加
  public function add_styles() {
    if(!isset($this->options['nocss']) || !$this->options['nocss']) {
      if(!$this->options['nocss']) {
        wp_enqueue_style('wptjs', plugins_url('assets/css/tocjs.css', __FILE__), array(), $this->version, 'all');
      } else {
        wp_enqueue_style('wptjs', plugins_url('assets/css/tocjs.css', __FILE__), array(), $this->version, 'all');
      }
    }
  }

  // JavaScriptの追加
  public function add_scripts() {
    if(!isset($this->options['nojs']) || !$this->options['nojs']) {
      if(!$this->options['nojs']) {
        wp_enqueue_script('wptjs', plugins_url('assets/js/toc.js', __FILE__), array('jquery'), $this->version, true);
      } else {
        wp_enqueue_script('wptjs', plugins_url('assets/js/toc.js', __FILE__), array('jquery'), $this->version, true);
      }
    }
  }

  // カスタムフィールドの追加
  public function add_custom_field() {
    add_meta_box('wptjs-setting', 'WP TocJS', array($this, 'custom_field_metabox'), 'post', 'side');
    add_meta_box('wptjs-setting', 'WP TocJS', array($this, 'custom_field_metabox'), 'page', 'side');
  }

  // カスタムフィールドの中身
  public function custom_field_metabox() {
    global $post;
    $value = get_post_meta($post->ID, 'wptjs-disabled', true);

    printf('<input type="checkbox" name="wptjs-disabled" value="1"%s>%s', checked($value, 1, false) , __('Hidden on this page', $this->textdomain));
  }

  public function save_custom_field($post_id) {

    if(!empty($_POST['wptjs-disabled'])) {
      update_post_meta($post_id, 'wptjs-disabled', $_POST['wptjs-disabled']);
    } else {
      delete_post_meta($post_id, 'wptjs-disabled');
    }
  }

  private function default_option($name) {
    if(!isset($this->options[$name])) {
      return $this->default_options[$name];
    } else {
      return $this->options[$name];
    }
  }

  public function generate_scripts() { ?>
    <script>
    jQuery("<?php echo $this->default_option('src') ?>").tocjs({
      excludes: "<?php echo $this->default_option('excludes'); ?>",
      headingNumber: "<?php echo $this->options['heading_number']; ?>",
      headings: "<?php echo $this->default_option('headings'); ?>",
      min: "<?php echo $this->default_option('min'); ?>",
      output: ".toc",
      title: "<?php echo __($this->default_option('title'), $this->textdomain); ?>",
      titleElement: "<?php echo $this->default_option('title_element'); ?>",
      tocNumber: "<?php echo $this->options['toc_number']; ?>",
    });
    </script>
  <?php }

  function the_content($content) {

    $add_html = do_shortcode('[tocjs]');
    $id = get_the_ID();
    $more = sprintf('<span id="more-%d"></span>', $id);

    $html = '';
    $html .= $more;
    $html .= "\n";
    $html .= $add_html;

    if(!get_post_meta($id, 'wptjs-disabled', true)) {

      $pattern_base = '<span id="more-[0-9]+"><\/span>';
      $pattern = sprintf('/%s/', $pattern_base);
      $pattern_p = sprintf('/<p>%s<\/p>/', $pattern_base);

      if(preg_match($pattern_p, $content, $matches)) {
        $content = preg_replace($pattern_p, $html, $content);
      } else if(preg_match($pattern, $content, $matches)) {
        $content = preg_replace($pattern, $html, $content);
      }
    }
    return $content;
  }


  public function generate_shortcode($atts){
    extract(shortcode_atts( array(

    ), $atts ));

    return '<div class="toc"></div>';
  }
}

$wptjs = new Wp_Tocjs();
