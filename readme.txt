=== Clickbank Ad Feed ===
Contributors: wccoder
Donate link: http://m7tech.net/
Tags: clickbank, click bank, text ad feed, sidebar, ads, text link ads
Requires at least: 2.6
Tested up to: 2.7
Stable tag: trunk

Add a Clickbank text ad feed widget to a sidebar.

== Description ==

Clickbank does not have an RSS feed, but they do have an XML version 
of their products called the Clickbank Marketplace. I wrote a simple 
backend for importing this XML file and producing RSS feeds, and 
then wrote a WordPress plugin that will show these feeds on your 
blog as a widget. Affiliate ID, number of items, campaign tracking 
tag and keywords are all available. You’re also able to put multiple 
ad feeds on your blog in different places, provided your WordPress 
theme has more than one sidebar.

== Installation ==

Installation is the same as most plugins, and if you’ve used widgets before you’ll know what to do. Please contact me if you’re having difficulty or you think something is broken. Here’s a step-by-step:

   1. Download the Clickbank Ad Feed plugin and extract clickbank_adfeed.php
   2. Copy the file into your wp-content/plugins/ folder
   3. Go to the Plugins menu in WordPress and activate the Clickbank Ad Feed plugin
   4. Go to Design > Widgets
   5. Add the Clickbank Ad Feed widget by clicking “Add”
   6. Click “Edit” and fill out the fields, or leave them as the defaults (you will want to fill in your affiliate ID!)
   7. Click “Change” then “Save Changes” and view your site

== Frequently Asked Questions ==

= How often is the data from the ad feed updated? =

Once a day. Clickbank updates their feed once a day, so there's no point in doing it more frequently.

= How are the ads ordered? =

They are ordered by Clickbank's popularity score, which can change over time. There are plans to add the ability to sort based on commission amount, recurring product or gravity.

= What if I don't have a Clickbank affiliate ID? =

You can get one by following this link: http://u235media.reseller.hop.clickbank.net/ and filling out the appropriate form. This will give you access to Clickbanks list of products that you can start selling immediately.

= What are the campaign tracking tags for? =

They are used to differentiate between ads placed on different pages or on different sites. Your affiliate reports on clickbank.net will allow break down your clicks and sales by campaign ID automatically. It makes good sense to use a different campaign ID for each site that you run ads on so you can see which ones perform the best.

= Why don't I see my own affiliate ID 100% of the time? =

The plugin author's affiliate ID gets cycled through about 20% of the time. This may vary due to caching of the RSS feed data, or internal browser caching.

= Can I generate a raw RSS feed instead of using this widget? =

Clickbank itself doesn't supply this. Raw RSS feed access is coming shortly, and will allow you to use any RSS feed reader to include a Clickbank Ad Feed on your site.

= How can I get some help? =

Contact the plugin author at info@m7tech.net

== Screenshots ==

1. Ad feed options
2. Ad feed on a blog
