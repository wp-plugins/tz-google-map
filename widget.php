<?php
/**
 * Widget - TZ Google Map Widget
 *
 * @package TZ Google Map Widget
 * @subpackage Classes
 * For another improvement, you can drop email to support@templaza.com or visit http://www.templaza.com
 *
 **/

class TZ_Googlemap_Widget extends WP_Widget {

    var $prefix;
    var $textdomain;

    function __construct(){
        // Set default variable for the widget instances
        $this->prefix = 'tz_googlemap';
        $this->textdomain = 'tz-googlemap-widget';

        // Set up the widget control options
        $control_options = array(
            'width' => 300,
            'height' => 350,
            'id_base' => $this->prefix
        );
        $widget_options = array('classname' => 'widget_tzgooglemap', 'description' => __( 'Displays Google map with many style', $this->textdomain ) );

        // Create the widget
        $this->WP_Widget($this->prefix, __('TZ Google map Widget', $this->textdomain), $widget_options, $control_options );

        // Load additional scripts and styles file to the widget admin area
        add_action( 'load-widgets.php', array(&$this, 'widget_admin') );
        // Load the widget stylesheet for the widgets screen.

        parent::__construct( 'widget_tz_googlemap', 'TZ Google Map Widget', $widget_options );

        add_action('wp_ajax_add_tzgooglemap', array($this, 'add_tzgooglemap'));
        add_action('admin_enqueue_scripts', array($this, 'upload_scripts'));
        if ( is_active_widget(false, false, $this->id_base, true) && !is_admin() ) {
            wp_enqueue_style( 'tz-googlemap', TZ_GOOGLEMAP_URL . 'css/widget.css', false, 0.7, 'screen' );
        }
    }

    /**
     * Upload the Javascripts for the media uploader
     */
    public function upload_scripts()
    {
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_enqueue_script('wp-color-picker');
        wp_register_script('upload_media_widget',plugin_dir_url(__FILE__) . 'js/tz_googlemap.js', false, '1.0', $in_footer=true);
        wp_enqueue_script('upload_media_widget');

        $php_array = array( 'admin_ajax' => admin_url( 'admin-ajax.php' ) );
        wp_localize_script( 'upload_media_widget', 'tzgooglemap_array', $php_array );
    }

    /**
     * Push additional script and style files to the widget admin area
     * @since 1.2.1
     **/
    function widget_admin() {
        wp_enqueue_style('thickbox');
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style( 'tz-googlemap-admin', TZ_GOOGLEMAP_URL . 'css/widget.css' );

    }


    /**
     * Push the widget stylesheet widget.css into widget admin page
     * @since 1.2.1
     **/
    function widget( $args, $instance ) {
        extract( $args );
        // Set up the arguments for wp_list_categories().
        $cur_arg = array(
            'title'			=> $instance['title'],
            'tzgooglemap'   =>$instance['tzgooglemap'],
            'map_height'        =>$instance['map_height'],
            'map_color'         =>$instance['map_color'],
            'map_zoom'          =>$instance['map_zoom'],
            'scrollwheel'       =>$instance['scrollwheel'],
            'navControl'        =>$instance['navControl'],
            'mapTypeControl'    =>$instance['mapTypeControl'],
            'scaleControl'      =>$instance['scaleControl'],
            'draggable'         =>$instance['draggable']
        );


        extract( $cur_arg );
        // print the before widget
        echo $before_widget;

        //var_dump($tzgooglemap);
        if ( $title )
            echo $before_title . $title . $after_title;

        // Wrap the widget
        ?>

        <div id="tz-google-map-wrapper" class="map">

            <div id="tz-google-map">
                <script src="http://maps.google.com/maps/api/js?sensor=false"></script>
                <div id="map"></div>
                <script type="text/javascript">
                    var locations = [
                        <?php foreach($tzgooglemap as $googlemap){
                        $o="";
                        $l=strlen($googlemap['address']);
                        for($i=0;$i<$l;$i++)
                        {
                            $c=$googlemap['address'][$i];
                            switch($c)
                            {
                                case '<': $o.='\\x3C'; break;
                                case '>': $o.='\\x3E'; break;
                                case '\'': $o.='\\\''; break;
                                case '\\': $o.='\\\\'; break;
                                case '"':  $o.='\\"'; break;
                                case "\n": $o.='\\x3Cbr\\x3E'; break;
                                case "\r": $o.='\\r'; break;
                                default:
                                    $o.=$c;
                            }
                        }

                        ?>
                        ['<div class="infobox"><?php echo esc_textarea($o); ?></div>', <?php echo esc_attr($googlemap['latitude']); ?>,<?php echo esc_attr($googlemap['longitude']); ?>, 2],
                        <?php
                        }
                        ?>

                    ];
                    <?php $i=0; $center_add=''; foreach($tzgooglemap as $googlemap){
                        if(isset($googlemap['center'])){
                            if($googlemap['center']=='checked'){
                                $center_add = ''.esc_attr($googlemap['latitude']).', '. esc_attr($googlemap['longitude']).'';
                            }
                        } else{
                            if($i==0){
                            $center_add = ''.esc_attr($googlemap['latitude']).', '. esc_attr($googlemap['longitude']).'';
                            }
                        }
                         $i++;
                    }
                    ?>

                    var address_center = '<?php echo esc_attr($center_add); ?>';

                    var icon = [
                        <?php foreach($tzgooglemap as $googlemap){ ?>
                        "<?php echo esc_attr($googlemap['image']); ?>",
                        <?php
                            }
                        ?>
                    ]
                    var map = new google.maps.Map(document.getElementById('map'), {
                        zoom: <?php echo esc_attr($map_zoom);?>,
                        scrollwheel: <?php echo esc_attr($scrollwheel);?>,
                        navigationControl: <?php echo esc_attr($navControl);?>,
                        mapTypeControl: <?php echo esc_attr($mapTypeControl);?>,
                        scaleControl: <?php echo esc_attr($scaleControl);?>,
                        draggable: <?php echo esc_attr($draggable);?>,
                        styles: [ { "stylers": [ { "hue": "<?php echo esc_attr($map_color);?>" }, { "gamma": 1 } ] } ],
                        center: new google.maps.LatLng(<?php echo esc_attr($center_add); ?>),
                        mapTypeId: google.maps.MapTypeId.ROADMAP
                    });

                    var infowindow = new google.maps.InfoWindow();

                    var marker, i;

                    for (i = 0; i < locations.length; i++) {

                        marker = new google.maps.Marker({
                            position: new google.maps.LatLng(locations[i][1], locations[i][2]),
                            map: map ,
                            icon: icon[i]
                        });

                        google.maps.event.addListener(marker, 'click', (function(marker, i) {
                            return function() {
                                infowindow.setContent(locations[i][0]);
                                infowindow.open(map, marker);
                            }
                        })(marker, i));
                    }
                </script>
            </div>
        </div>

        <?php
        // Print the after widget
        echo $after_widget;

    }


    /**
     * Widget form function
     * @since 1.2.1
     **/
    function form( $instance ) {
        // Set default form values.
        $defaults = array(
            'title'             => esc_attr__( 'TZ Google Map Widget', $this->textdomain ),
            'tzgooglemap'       =>  array(
                1 =>array(
                    'latitude'      =>  '',
                    'longitude'     =>  '',
                    'image'         =>  '',
                    'address'       =>  '',
                    'center'        =>  'checked'
                )
            ),
            'tab'		        => array( 0 => true, 1 => false, 2 => false ),
            'map_height'        =>'450',
            'map_color'         =>'#21C2F8',
            'map_zoom'          =>'10',
            'scrollwheel'       =>'true',
            'navControl'        =>'true',
            'mapTypeControl'    =>'false',
            'scaleControl'      =>'false',
            'draggable'         =>'true'


        );
        $instance = wp_parse_args( $instance, $defaults );
        extract( $instance );
        $tzgooglemap = is_array($tzgooglemap) ? $tzgooglemap : 0;
        $count = 1;

        $tabs = array(
            __( 'General', $this->textdomain ),
            __( 'Advanced', $this->textdomain ),
            __( 'About', $this->textdomain )
        );

        ?>
        <script type="text/javascript">
            // Tabs function
            jQuery(document).ready(function($){
                // Tabs function
                $('ul.nav-tabs li').each(function(i) {
                    $(this).bind("click", function(){
                        var liIndex = $(this).index();
                        var content = $(this).parent("ul").next().children("li").eq(liIndex);
                        $(this).addClass('active').siblings("li").removeClass('active');
                        $(content).show().addClass('active').siblings().hide().removeClass('active');

                        $(this).parent("ul").find("input").val(0);
                        $('input', this).val(1);
                    });
                });

                // Widget background
                $("#fbw-<?php echo $this->id; ?>").closest(".widget-inside").addClass("ntotalWidgetBg");

                jQuery('.my-color-field').wpColorPicker();
            });
        </script>

        <div id="tzgooglemap-<?php echo $this->id ; ?>" class="totalControls tabbable tabs-left">
            <ul class="title">
                <li>
                    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title', $this->textdomain ); ?></label>
                    <input name="<?php echo $this->get_field_name( 'title' ); ?>" id="<?php echo $this->get_field_id( 'title' ); ?>"
                           value="<?php echo $instance['title']; ?>"  type="text" class="widefat"/>
                </li>
            </ul>
            <ul class="nav nav-tabs">
                <?php foreach ($tabs as $key => $tab ) : ?>
                    <li class="fes-<?php echo $key; ?> <?php echo $instance['tab'][$key] ? 'active' : '' ; ?>"><?php echo $tab; ?><input type="hidden" name="<?php echo $this->get_field_name( 'tab' ); ?>[]" value="<?php echo $instance['tab'][$key]; ?>" /></li>
                <?php endforeach; ?>
            </ul>
            <ul class="tab-content">
                <li class="tab-pane <?php if ( $instance['tab'][0] ) : ?>active<?php endif; ?>">

                    <ul class="tzgooglemap-box">
                        <?php
                        $tzgooglemap = is_array($tzgooglemap) ? $tzgooglemap : 0;
                        $count = 1;
                        foreach($tzgooglemap as $ser) {
                            $this->tzgooglemap($ser, $count);
                            $count++;
                        }
                        ?>

                    </ul>
                    <p class="addnew">
                        <span class="button tzgooglemap_button button-primary">Add New</span>
                    </p>

                </li>

                <li class="tab-pane <?php if ( $instance['tab'][1] ) : ?>active<?php endif; ?>">
                    <ul>
                        <li>
                            <label for="<?php echo $instance['map_height']; ?>"><?php _e( 'Map Height', $this->textdomain ); ?></label>
                            <input name="<?php echo $this->get_field_name( 'map_height' ); ?>" id="<?php echo $this->get_field_id( 'map_height' ); ?>"
                                   value="<?php echo $instance['map_height']; ?>" type="text"/>
                        </li>
                        <li>
                            <label for="<?php echo $instance['map_color']; ?>"><?php _e( 'Map Color', $this->textdomain ); ?></label>
                            <input name="<?php echo $this->get_field_name( 'map_color' ); ?>"  type="text" value="<?php echo $instance['map_color']; ?>" class="my-color-field" data-default-color="#21C2F8" />
                        </li>
                        <li>
                            <label for="<?php echo $instance['map_zoom']; ?>"><?php _e( 'Map Zoom', $this->textdomain ); ?></label>
                            <input name="<?php echo $this->get_field_name( 'map_zoom' ); ?>" id="<?php echo $this->get_field_id( 'map_zoom' ); ?>"
                                   value="<?php echo $instance['map_zoom']; ?>" type="text"/>
                        </li>
                        <li>
                            <label for="<?php echo $instance['scrollwheel']; ?>"><?php _e( 'ScrollWheel', $this->textdomain ); ?></label>
                            <select name="<?php echo $this->get_field_name( 'scrollwheel' ); ?>" id="<?php echo $this->get_field_id( 'scrollwheel' ); ?>"
                                    value="<?php echo $instance['scrollwheel']; ?>">
                                <option value="true" <?php if( $instance['scrollwheel']=='true'){?>selected="selected"<?php } ?>>True</option>
                                <option value="false" <?php if( $instance['scrollwheel']=='false'){?>selected="selected"<?php } ?>>False</option>
                            </select>
                        </li>
                        <li>
                            <label for="<?php echo $instance['navControl']; ?>"><?php _e( 'navControl', $this->textdomain ); ?></label>
                            <select name="<?php echo $this->get_field_name( 'navControl' ); ?>" id="<?php echo $this->get_field_id( 'navControl' ); ?>"
                                    value="<?php echo $instance['navControl']; ?>">
                                <option value="true" <?php if( $instance['navControl']=='true'){?>selected="selected"<?php } ?>>True</option>
                                <option value="false" <?php if( $instance['navControl']=='false'){?>selected="selected"<?php } ?>>False</option>
                            </select>
                        </li>
                        <li>
                            <label for="<?php echo $instance['mapTypeControl']; ?>"><?php _e( 'mapTypeControl', $this->textdomain ); ?></label>
                            <select name="<?php echo $this->get_field_name( 'mapTypeControl' ); ?>" id="<?php echo $this->get_field_id( 'mapTypeControl' ); ?>"
                                    value="<?php echo $instance['mapTypeControl']; ?>">
                                <option value="true" <?php if( $instance['mapTypeControl']=='true'){ ?>selected="selected"<?php } ?>>True</option>
                                <option value="false" <?php if( $instance['mapTypeControl']=='false'){ ?>selected="selected"<?php } ?>>False</option>
                            </select>
                        </li>
                        <li>
                            <label for="<?php echo $instance['scaleControl']; ?>"><?php _e( 'scaleControl', $this->textdomain ); ?></label>
                            <select name="<?php echo $this->get_field_name( 'scaleControl' ); ?>" id="<?php echo $this->get_field_id( 'scaleControl' ); ?>"
                                    value="<?php echo $instance['scaleControl']; ?>">
                                <option value="true" <?php if( $instance['scaleControl']=='true'){ ?> selected="selected"<?php } ?>>True</option>
                                <option value="false" <?php if( $instance['scaleControl']=='false'){ ?> selected="selected"<?php } ?>>False</option>
                            </select>
                        </li>
                        <li>
                            <label for="<?php echo $instance['draggable']; ?>"><?php _e( 'draggable', $this->textdomain ); ?></label>
                            <select name="<?php echo $this->get_field_name( 'draggable' ); ?>" id="<?php echo $this->get_field_id( 'draggable' ); ?>"
                                    value="<?php echo $instance['draggable']; ?>">
                                <option value="true" <?php if( $instance['draggable']=='true'){ ?>selected="selected"<?php } ?>>True</option>
                                <option value="false" <?php if( $instance['draggable']=='false'){ ?>selected="selected"<?php } ?>>False</option>
                            </select>
                        </li>

                    </ul>
                </li>
                <li class="tab-pane <?php if ( $instance['tab'][2] ) : ?>active<?php endif; ?>">
                    <ul>
                        <li>
                            <span style="font-size: 11px;">
                                <a href="http://www.templaza.com/free-stuff/wordpress-plugins.html" target="_blank">
                                    <span style=" font-weight: bold;">TZ Google Map Widget</a> &copy; Copyright
                                <a href="http://www.templaza.com/">TemPlaza.com</a> <?php echo date("Y"); ?></span>.
                        </li>
                    </ul>
                </li>

            </ul>

        </div>
    <?php
    }



    function tzgooglemap(  $googlemap = array(), $count = 0  ) {
        ?>
        <li id="<?php echo $this->get_field_id('tzgooglemap') ; ?>-item-<?php echo esc_attr($count) ?>" rel="<?php echo esc_attr($count) ?>">
            <div class="tz-googlemap-header">
                <strong>Address <?php echo esc_html($count) ?></strong>
            </div>
            <div class="tz-googlemap-content">

                <p><label for="<?php echo $this->get_field_id('tzgooglemap') ?>-<?php echo esc_attr($count) ?>-latitude"><?php echo _e( 'Latitude', $this->textdomain) ; ?></label>
                    <input type="text" class="widefat" id="<?php echo $this->get_field_id('tzgooglemap') ?>-<?php echo esc_attr($count) ?>-latitude"
                           name="<?php echo $this->get_field_name('tzgooglemap') ?>[<?php echo esc_attr($count) ?>][latitude]"
                           value="<?php echo esc_attr($googlemap['latitude']) ?>">
                </p>

                <p><label for="<?php echo $this->get_field_id('tzgooglemap') ?>-<?php echo esc_attr($count) ?>-longitude"><?php echo _e( 'Longitude', $this->textdomain) ; ?></label>
                    <input type="text" class="widefat" id="<?php echo $this->get_field_id('tzgooglemap') ?>-<?php echo esc_attr($count) ?>-longitude"
                           name="<?php echo $this->get_field_name('tzgooglemap') ?>[<?php echo esc_attr($count) ?>][longitude]"
                           value="<?php echo esc_attr($googlemap['longitude']) ?>"></p>

                <p class="image_upload">
                    <label for="<?php echo $this->get_field_id('tzgooglemap') ?>-<?php echo esc_attr($count) ?>-image"><?php _e( 'Image:' ); ?></label>
                    <input name="<?php echo $this->get_field_name('tzgooglemap') ?>[<?php echo esc_attr($count) ?>][image]"
                           id="<?php echo $this->get_field_id('tzgooglemap') ?>-<?php echo esc_attr($count) ?>-image" class="widefat" type="text" size="36"
                           value="<?php echo esc_url($googlemap['image'] ); ?>" />
                    <input class="upload_image_button button" type="button" value="Select" />


                </p>
                <p>
                    <label for="<?php echo $this->get_field_id('tzgooglemap') ?>-<?php echo esc_attr($count) ?>-address"><?php _e( 'Address:' ); ?></label>
                    <textarea name="<?php echo $this->get_field_name('tzgooglemap') ?>[<?php echo esc_attr($count) ?>][address]"
                              id="<?php echo $this->get_field_id('tzgooglemap') ?>-<?php echo esc_attr($count) ?>-address" class="widefat"
                              value="<?php echo esc_textarea($googlemap['address'] ); ?>" ><?php echo esc_textarea($googlemap['address'] ); ?></textarea>
                </p>
                <p>
                    <label for="<?php echo $this->get_field_id('tzgooglemap'); ?>"><?php _e('Make Center', $this->textdomain); ?></label>
                    <input id="<?php echo $this->get_field_id('center'); ?>" name="<?php echo $this->get_field_name('tzgooglemap') ?>[<?php echo esc_attr($count) ?>][center]"
                           value="checked" <?php if(isset($googlemap['center'])){echo esc_attr($googlemap['center'] );} ?> type="checkbox"  />
                    <span> Checked to display this Address in center Map</span>
                </p>
                <p class="button_box">
                    <span class="button tzgooglemap_remove"><?php _e('Delete',$this->textdomain);?></span>
                </p>
            </div>
        </li>
    <?php
    }

    function add_tzgooglemap(){

        $count = isset($_POST['count']) ? absint($_POST['count']) : false;
        $tab = array(
            'latitude'      =>  '',
            'longitude'     =>  '',
            'image'         =>  '',
            'address'       =>  '',
            'center'        =>  'checked',
        );
        $this->tzgooglemap($tab, $count);
        die();
    }

    /**
     * Widget update function
     * @since 1.2.1
     **/
    function update( $new_instance, $old_instance ) {
        $new_instance = $new_instance;
        return $new_instance;
    }

}