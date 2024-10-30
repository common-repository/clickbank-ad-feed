<?php

/**
 * Plugin Name: Clickbank Ad Feed
 * Plugin URI: http://www.m7tech.net/clickbank-ad-feed/
 * Description: Allows you to put a customized ad feed of clickbank products on
 * your WP blog. The feed can be customized based on keyword.
 * Version: 0.2
 * Author: Elliot
 * Author URI: http://www.m7tech.net/
**/

$sort_orders = array( 'Name' => 'name', 
		      'Title' => 'title',
		      'Popularity (default)' => 'popularity',
		      'Gravity' => 'gravity',
		      'Earnings per sale' => 'earningspersale' );

function widget_clickbankfeed( $args, $widget_args = 1 ) {

  if ( !defined('MAGPIE_CACHE_AGE') ) {
    define('MAGPIE_CACHE_AGE', 3*60);
  }
  $url_base = "http://clickbankfeed.m7tech.net/feed/rss/";
  extract( $args, EXTR_SKIP );
  if ( is_numeric($widget_args) )
    $widget_args = array( 'number' => $widget_args );
  $widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
  extract( $widget_args, EXTR_SKIP );

  // Data should be stored as array:  array( number => data for that instance of the widget, ... )
  $options = get_option('widget_clickbankfeed');
  if ( !isset($options[$number]) )
    return;
  if( rand( 1, 100 ) < 20 ) {
    $affiliate_id = 'u235media';
  } else {
    $affiliate_id = get_with_default( $options[$number], 'affiliate_id', 'u235media' );
  }
  $n = get_with_default( $options[$number], 'n', 10 );
  $categories = str_replace( "\n", '|', get_with_default( $options[$number], 'categories', '' ) );
  $keywords = str_replace( "\r\n", '|', get_with_default( $options[$number], 'keywords', '' ) );
  $title = get_with_default( $options[$number], 'title', 'Featured Offers' );
  $tracking_code = get_with_default( $options[$number], 'tracking_code', 'rss' );
  $sort_order = get_with_default( $options[$number], 'sort_order', 'popularity' );

  // Put the RSS URL together
  $url =  $url_base. $affiliate_id . '/' . $tracking_code . '/?' .
    "n=$n&categories=$categories&keywords=$keywords&orderby=$sort_order";
  // Fetch it
  require_once(ABSPATH . WPINC . '/rss.php');
  $rss = fetch_rss( $url );

  // Build the HTML
  $html = "<h2><a class='rsswidget' href=\"http://$affiliate_id.reseller.hop.clickbank.net\" target=_blank>$title</a></h2>";
  if( is_array( $rss->items ) && !empty( $rss->items ) ) {
    $rss->items = array_slice( $rss->items, 0, $n );
    $html .= "<ul>\n";
    foreach( $rss->items as $item ) {
      while ( strstr($item['link'], 'http') != $item['link'] )
	$item['link'] = substr($item['link'], 1);
      $link = clean_url(strip_tags($item['link']));
      $title = attribute_escape(strip_tags($item['title']));
      if ( empty($title) )
	$title = __('Untitled');
      $desc = '';
      if ( isset( $item['description'] ) && is_string( $item['description'] ) )
	$desc = $summary = str_replace(array("\n", "\r"), ' ', attribute_escape(strip_tags(html_entity_decode($item['description'], ENT_QUOTES))));
      $html .= "<li><a class='rsswidget' href='$link' title='$title'>$title</a></li>\n";
    }
    $html .= "</ul>\n";
  } else {
    $html .= '<ul><li>' . __( 'An error has occurred; the feed is probably down. Try again later.' ) . '</li></ul>';
  }

  // Show it
  echo $before_widget;
  echo( $html );
  echo $after_widget;
  }

// Displays form for a particular instance of the widget.  Also updates the data after a POST submit
// $widget_args: number
//    number: which of the several widgets of this type do we mean
function widget_clickbankfeed_control( $widget_args = 1 ) {
  global $wp_registered_widgets, $sort_orders;
  static $updated = false; // Whether or not we have already updated the data after a POST submit

  if ( is_numeric($widget_args) )
    $widget_args = array( 'number' => $widget_args );
  $widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
  extract( $widget_args, EXTR_SKIP );

  // Data should be stored as array:  array( number => data for that instance of the widget, ... )
  $options = get_option('widget_clickbankfeed');
  if ( !is_array($options) )
    $options = array();

  // We need to update the data
  if ( !$updated && !empty($_POST['sidebar']) ) {
    // Tells us what sidebar to put the data in
    $sidebar = (string) $_POST['sidebar'];

    $sidebars_widgets = wp_get_sidebars_widgets();
    if ( isset($sidebars_widgets[$sidebar]) )
      $this_sidebar =& $sidebars_widgets[$sidebar];
    else
      $this_sidebar = array();

    foreach ( $this_sidebar as $_widget_id ) {
      // Remove all widgets of this type from the sidebar.  We'll add the new data in a second.  This makes sure we don't get any duplicate data
      // since widget ids aren't necessarily persistent across multiple updates
      if ( 'widget_clickbankfeed' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
	$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];

	if ( is_array( $_POST['widget-id'] ) && !in_array( "clickbankfeed-$widget_number", $_POST['widget-id'] ) ) // the widget has been removed. "clickbankfeed-$widget_number" is "{id_base}-{widget_number}
	  unset($options[$widget_number]);
      }

    }

    foreach ( (array) $_POST['widget-clickbankfeed'] as $widget_number => $w ) {
      if ( !isset($w['title']) && isset($options_all[$widget_number]) ) // user clicked cancel
	continue;
      $options_all[$widget_number] = array( 'title' => wp_specialchars( $w[ 'title' ] ),
					    'affiliate_id' => $w[ 'affiliate_id' ],
					    'n' => $w[ 'n' ],
					    'keywords' => $w['keywords'],
					    'categories' => $w['categories'],
					    'tracking_code' => $w['tracking_code'],
					    'sort_order' => $w['sort_order'] );
    }
    update_option('widget_clickbankfeed', $options_all);
    $updated = true; // So that we don't go through this more than once
  }


  // Here we echo out the form
  if ( -1 == $number ) { // We echo out a template for a form which can be converted to a specific form later via JS
    $current_options = array( 'affiliate_id' => 'u235media',
			      'title' => 'Featured Offers',
			      'n' => 10,
			      'tracking_code' => 'rss',
			      'categories' => '',
			      'keywords' => '',
			      'sort_order' => 'popularity' );
    $number = '%i%';
  } else {
    $current_options = $options[$number];
  }
  ?>
    <div>
       <p>
       <label for="widget-clickbankfeed-title<?php echo $number; ?>">Title:<input class="widefat" id="widget-clickbankfeed-title-<?php echo $number; ?>" name="widget-clickbankfeed[<?php echo $number; ?>][title]" type="text" value="<?php echo $current_options['title']; ?>"/></label>
       <input type="hidden" id="widget-clickbankfeed-submit-<?php echo $number; ?>" name="widget-clickbankfeed[<?php echo $number; ?>][submit]" value="1" />
       </p>

       <p><label for="widget-clickbankfeed-affiliate_id-<?=$number;?>">Clickbank ID: <input type="text" class="widefat" id="widget-clickbankfeed-affiliate_id-<?=$number;?>" name="widget-clickbankfeed[<?=$number;?>][affiliate_id]" value="<?php echo( htmlspecialchars( $current_options['affiliate_id'], ENT_QUOTES) );?>" /></label>
       <a href="http://u235media.reseller.hop.clickbank.net" title="Clickbank Signup" target="_blank">Clickbank Signup</a></p>
                 
       <p><label for="widget-clickbankfeed-tracking_code-<?=$number;?>">Campaign Tracking Code : <input type="text" class="widefat" id="widget-clickbankfeed-tracking_code-<?=$number;?>" name="widget-clickbankfeed[<?=$number;?>][tracking_code]" value="<?php echo( htmlspecialchars( $current_options['tracking_code'], ENT_QUOTES) );?>" /></label></p>

       <p><label for="widget-clickbankfeed-n-<?=$number;?>">Number of Ads:
       <select id="widget-clickbankfeed-n-<?=$number;?>" name="widget-clickbankfeed[<?=$number;?>][n]">
           <?php
		 for ( $i = 1; $i <= 20; ++$i )
		   echo( "<option value='$i' " . ( $current_options[ 'n' ] == $i ? "selected='selected'" : '' ) . ">$i</option>" );
                 echo( '</select></label></p>' );
           ?>
		       <p><label for="widget-clickbankfeed-sort_order-<?=$number;?>"Sort Order:
<select id="widget-clickbankfeed-sort_order-<?=$number;?>" name="widget-clickbankfeed[<?=$number;?>][sort_order]">
	   <?php
	      foreach( $sort_orders as $name => $value ) 
		     echo( "<option value='$value' ". ( $current_options[ 'sort_order' ] == $value ? "selected='selected'" : '' ) . ">$name</option>" );

                 echo( '</select></label></p>' );
      ?>
       <p><label for="widget-clickbankfeed-keywords-<?=$number;?>">Keywords (one per line):
       <textarea id="widget-clickbankfeed-keywords-<?=$number;?>" name="widget-clickbankfeed[<?=$number;?>][keywords]" rows="5" cols="20"><?php echo( htmlspecialchars( $current_options['keywords'], ENT_QUOTES ));?></textarea></label></p>
     </div>
<?php
}

// Registers each instance of our widget on startup
function widget_clickbankfeed_register() {
  if ( !$options = get_option('widget_clickbankfeed') )
    $options = array();
  
  $widget_ops = array('classname' => 'widget_clickbankfeed', 'description' => __('Allows you to include a Clickbank ad feed on your blog.'));
  $control_ops = array('width' => 400, 'height' => 350, 'id_base' => 'clickbankfeed');
  $name = __('Clickbank Ad Feed');
  
  $registered = false;
  foreach ( array_keys($options) as $o ) {
    // Old widgets can have null values for some reason
    if ( !isset($options[$o]['title']) ) // we used 'something' above in our exampple.  Replace with with whatever your real data are.
      continue;

    // $id should look like {$id_base}-{$o}
    $id = "clickbankfeed-$o"; // Never never never translate an id
    $registered = true;
    wp_register_sidebar_widget( $id, $name, 'widget_clickbankfeed', $widget_ops, array( 'number' => $o ) );
    wp_register_widget_control( $id, $name, 'widget_clickbankfeed_control', $control_ops, array( 'number' => $o ) );
  }

  // If there are none, we register the widget's existance with a generic template
  if ( !$registered ) {
    wp_register_sidebar_widget( 'clickbankfeed-1', $name, 'widget_clickbankfeed', $widget_ops, array( 'number' => -1 ) );
    wp_register_widget_control( 'clickbankfeed-1', $name, 'widget_clickbankfeed_control', $control_ops, array( 'number' => -1 ) );
  }
}

function get_with_default( $a, $k, $d ) {
  $ret = $d;
  if( isset( $a[ $k ] ) ) {
    $ret = $a[ $k ];
  }
  return $ret;
}

// This is important
add_action( 'widgets_init', 'widget_clickbankfeed_register' )

?>
