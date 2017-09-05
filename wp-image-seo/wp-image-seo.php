<?php
/*
  Plugin Name: WordPress SEO For Image
  Plugin URI: https://noorsplugin.com/2016/05/16/wordpress-image-seo-plugin/
  Description: WordPress SEO For Image allows you to add alt and title attributes to all of your blog images.
  Version: 1.1.4
  Author: naa986
  Author URI: https://noorsplugin.com/
  Text Domain: wp-image-seo
  Domain Path: /languages
 */

if (!defined('ABSPATH'))
    exit;
if (!class_exists('WP_IMAGE_SEO')) {

    class WP_IMAGE_SEO {

        var $plugin_version = '1.1.4';
        var $plugin_url;
        var $plugin_path;

        function __construct() {
            define('WP_IMAGE_SEO_VERSION', $this->plugin_version);
            define('WP_IMAGE_SEO_SITE_URL', site_url());
            define('WP_IMAGE_SEO_URL', $this->plugin_url());
            define('WP_IMAGE_SEO_PATH', $this->plugin_path());
            $this->plugin_includes();
        }

        function plugin_includes() {
            if (is_admin()) {
                add_filter('plugin_action_links', array($this, 'add_plugin_action_links'), 10, 2);
            }
            add_action('plugins_loaded', array($this, 'plugins_loaded_handler'));
            add_action('admin_menu', array($this, 'add_options_menu'));
            add_filter('the_content', array($this, 'filter_content'), 100);
        }

        function plugin_url() {
            if ($this->plugin_url)
                return $this->plugin_url;
            return $this->plugin_url = plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__));
        }

        function plugin_path() {
            if ($this->plugin_path)
                return $this->plugin_path;
            return $this->plugin_path = untrailingslashit(plugin_dir_path(__FILE__));
        }

        function add_plugin_action_links($links, $file) {
            if ($file == plugin_basename(dirname(__FILE__) . '/wp-image-seo.php')) {
                $links[] = '<a href="options-general.php?page=wp-image-seo-settings">' . __('Settings', 'wp-image-seo') . '</a>';
            }
            return $links;
        }

        function plugins_loaded_handler() {
            load_plugin_textdomain('wp-image-seo', false, dirname(plugin_basename(__FILE__)) . '/languages/');
            $this->run_installer();
        }

        function run_installer() {
            add_option('wp_image_seo_alt', '%name %title');
            add_option('wp_image_seo_title', '%title');
            add_option('wp_image_seo_override', 'on');
            add_option('wp_image_seo_override_title', 'off');
        }

        function add_options_menu() {
            if (is_admin()) {
                add_options_page(__('WP Image SEO', 'wp-image-seo'), __('WP Image SEO', 'wp-image-seo'), 'manage_options', 'wp-image-seo-settings', array($this, 'options_page'));
            }
        }

        function options_page() {

            // If form was submitted
            if (isset($_POST['submitted'])) {
                $alt_text = sanitize_text_field($_POST['alttext']);
                $title_text = sanitize_text_field($_POST['titletext']);
                $override = (!isset($_POST['override']) ? 'off' : 'on');
                $override_title = (!isset($_POST['override_title']) ? 'off' : 'on');
                update_option('wp_image_seo_alt', $alt_text);
                update_option('wp_image_seo_title', $title_text);
                update_option('wp_image_seo_override', $override);
                update_option('wp_image_seo_override_title', $override_title);

                // Show message
                echo '<div id="message" class="updated fade">' . __('WP Image SEO options saved', 'wp-image-seo') . '</div>';
            }
            // Fetch code from DB
            $alt_text = get_option('wp_image_seo_alt');
            $title_text = get_option('wp_image_seo_title');
            $override = (get_option('wp_image_seo_override') == 'on') ? "checked" : "";
            $override_title = (get_option('wp_image_seo_override_title') == 'on') ? "checked" : "";

            $imgpath = $this->plugin_url . '/i';
            $action_url = $_SERVER['REQUEST_URI'];

            // Configuration Page
            $url = "https://noorsplugin.com/wordpress-image-seo-plugin/";
            $link_text = sprintf(wp_kses(__('Please visit the <a target="_blank" href="%s">WP Image SEO</a> documentation page for details.', 'wp-image-seo'), array('a' => array('href' => array(), 'target' => array()))), esc_url($url));
            ?>
            <div class="wrap">
                <h1>WP Image SEO v<?php echo $this->plugin_version;?></h1>
                <div class="update-nag"><?php echo $link_text;?></div>
                    <form name="wpimageseoform" action="<?php echo $action_url;?>" method="post">
                            <input type="hidden" name="submitted" value="1" />
                            <h2>General Settings</h2>
                            <p>WP Image SEO automatically adds alt and title attributes to all of your blog post images specified by parameters below.</p>
                            <p>You can enter any text in a field including the following tags:</p>
                            <ul>
                                <li>%title - replaces post title</li>
                                <li>%name - replaces image file name (without extension)</li>
                                <!-- <li>%category - replaces post category</li> -->
                                <!-- <li>%tags - replaces post tags</li> -->
                            </ul>
                            <h4>Images options</h4>
                            <div>
                                <label for="alt_text"><b>ALT</b> attribute (example: %name %title)</label><br>
                                <input style="border:1px solid #D1D1D1;width:165px;"  id="alt_text" name="alttext" value="<?php echo esc_attr($alt_text);?>"/>
                            </div>
                            <br>
                            <div>
                                <label for="title_text"><b>TITLE</b> attribute (example: %name image)</label><br>
                                <input style="border:1px solid #D1D1D1;width:165px;"  id="title_text" name="titletext" value="<?php echo esc_attr($title_text);?>"/>
                            </div>
                            <br/>
                            <div>
                                <input id="check1" type="checkbox" name="override" <?php echo $override;?> />
                                <p class="description">Override default WordPress image alt tag (recommended)</p>
                            </div>
                            <br/>
                            <div>
                                <input id="check2" type="checkbox" name="override_title" <?php echo $override_title;?> />
                                <p class="description">Override default WordPress image title</p>
                            </div>
                            <br/><br/>
                            <p class="description">
                                Example:<br/>
                                If you have an image named "McLaren.jpg" in a post titled "Car Image": <br/><br/>
                                -Setting alt attribute to "%name %title" will produce alt="McLaren Car Image"<br/>
                                -Setting title attribute to "%name image" will produce title="McLaren image"
                            </p>
                            <p class="submit"><input type="submit" name="Submit" class="button button-primary" value="<?php _e('Update options', 'wp-image-seo');?>" /></p>
                    </form>
            </div>
            <?php
        }

        function filter_content($content) {
            return preg_replace_callback('/<img[^>]+/', array($this, 'process_images'), $content);
        }

        function process_images($matches) {
            global $post;
            $title = $post->post_title;
            $alttext_rep = get_option('wp_image_seo_alt');
            $titletext_rep = get_option('wp_image_seo_title');
            $override = get_option('wp_image_seo_override');
            $override_title = get_option('wp_image_seo_override_title');

            # take care of unsusal endings
            $matches[0] = preg_replace('|([\'"])[/ ]*$|', '\1 /', $matches[0]);

            ### Normalize spacing around attributes.
            $matches[0] = preg_replace('/\s*=\s*/', '=', substr($matches[0], 0, strlen($matches[0]) - 2));
            ### Get source.

            preg_match('/src\s*=\s*([\'"])?((?(1).+?|[^\s>]+))(?(1)\1)/', $matches[0], $source);

            $saved = $source[2];

            ### Swap with file's base name.
            preg_match('%[^/]+(?=\.[a-z]{3}\z)%', $source[2], $source);
            ### Separate URL by attributes.
            $pieces = preg_split('/(\w+=)/', $matches[0], -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            ### Add missing pieces.

            $postcats = get_the_category();
            $cats = "";
            if ($postcats) {
                foreach ($postcats as $cat) {
                    $cats = $cat->slug . ' ' . $cats;
                }
            }

            $posttags = get_the_tags();

            $tags = "";
            if ($posttags) {
                foreach ($posttags as $tag) {
                    $tags = $tag->name . ' ' . $tags;
                }
            }

            if (!in_array('title=', $pieces) || $override_title == "on") {
                $titletext_rep = str_replace("%title", $post->post_title, $titletext_rep);
                if(isset($source[0])){
                    $titletext_rep = str_replace("%name", $source[0], $titletext_rep);
                }
                $titletext_rep = str_replace("%category", $cats, $titletext_rep);
                $titletext_rep = str_replace("%tags", $tags, $titletext_rep);

                $titletext_rep = str_replace('"', '', $titletext_rep);
                $titletext_rep = str_replace("'", "", $titletext_rep);

                $titletext_rep = str_replace("_", " ", $titletext_rep);
                $titletext_rep = str_replace("-", " ", $titletext_rep);
                //$titletext_rep=ucwords(strtolower($titletext_rep));
                if (!in_array('title=', $pieces)) {
                    array_push($pieces, ' title="' . $titletext_rep . '"');
                } else {
                    $key = array_search('title=', $pieces);
                    $pieces[$key + 1] = '"' . $titletext_rep . '" ';
                }
            }

            if (!in_array('alt=', $pieces) || $override == "on") {
                $alttext_rep = str_replace("%title", $post->post_title, $alttext_rep);
                if(isset($source[0])){
                    $alttext_rep = str_replace("%name", $source[0], $alttext_rep);
                }
                $alttext_rep = str_replace("%category", $cats, $alttext_rep);
                $alttext_rep = str_replace("%tags", $tags, $alttext_rep);
                $alttext_rep = str_replace("\"", "", $alttext_rep);
                $alttext_rep = str_replace("'", "", $alttext_rep);
                $alttext_rep = (str_replace("-", " ", $alttext_rep));
                $alttext_rep = (str_replace("_", " ", $alttext_rep));

                if (!in_array('alt=', $pieces)) {
                    array_push($pieces, ' alt="' . $alttext_rep . '"');
                } else {
                    $key = array_search('alt=', $pieces);
                    $pieces[$key + 1] = '"' . $alttext_rep . '" ';
                }
            }
            return implode('', $pieces) . ' /';
        }

        function remove_extension($name) {
            return preg_replace('/(.+)\..*$/', '$1', $name);
        }

    }

    $GLOBALS['wp_image_seo'] = new WP_IMAGE_SEO();
}
