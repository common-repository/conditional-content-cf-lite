=== Conditional Content by Crowd Favorite ===
Contributors: crowdfavorite
Tags: conditional content, personalization, adaptive content, dynamic content, replace content, website personalization, conversion, crowd favorite, dynamic web content, website customization
Requires at least: 5.0.0
Tested up to: 6.0
Stable tag: 2.1.2
Requires PHP: 7.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Custom personalization matters! Conditional Content is designed to integrate seamlessly with your editing experience!

== Description ==

Custom personalization matters! Conditional Content is designed to integrate seamlessly with your editing experience! Personalize your website to display custom content to tailor each user's experience and increase user engagement. Conditional Content is a plugin that works directly with [Gutenberg](https://wordpress.org/gutenberg/), [Beaver Builder](https://www.wpbeaverbuilder.com/) and [Elementor](https://elementor.com/) to bring customizable content to your fingertips. It gives you the power to amend or add content on your website using simple personalization settings, which can be used to engage users, customize their experience and improve conversion rates.

= Key Benefits =

1. Simple to use- No coding skills required
2. Works on all page content such as Pages, Posts, Custom Post Types, and WordPress Widgets
3. Native Integration with popular page builders [Beaver Builder](https://www.wpbeaverbuilder.com/) and [Elementor](https://elementor.com/)
4. Natively works with [Gutenberg](https://wordpress.org/gutenberg/) Blocks, skipping the need for WordPress to translate shortcodes, thereby increasing site performance, meaning it’s FAST!

= Features =
**What types of conditions does Conditional Content offer?**

Add or replace content according to one or more of the following conditions:

* IP-BASED GEOLOCATION CONTENT
	- Country, City, State, Zip Code / Postal Code, Area Code

* USER’S DEVICE TYPE
	- Mobile, Tablet, Desktop

* TIME & DATE
	- Schedule - specific days and hours **(PRO ONLY)** , Start & End Date

* USER BEHAVIOUR
	- Logged In Users, New Users, Returning Visitors, User’s Browser Language **(PRO ONLY)**

* REFERRAL SOURCE
	- Specific URL, Page on your website, Common referral websites **(PRO ONLY)**
		- Google, Facebook, Twitter, Instagram, Youtube

* DYNAMIC QUERY PARAMETER
Want to run a campaign and don’t know how to tailor your users experience with personalized content? Easily apply our dynamic query parameter condition to achieve your needs!

* VISITED PAGES
	- URL is; URL is not; URL contains **(PRO ONLY)**; URL does not contain **(PRO ONLY)**



= HOW TO USE =

Using Conditional Content is very simple:
1. Create a condition
2. Write content in your preferred page building editor. Written natively for [Gutenberg](https://wordpress.org/gutenberg/), [Beaver Builder](https://www.wpbeaverbuilder.com/) and [Elementor](https://elementor.com/).
3. Select your conditions for each custom content desired.
4. Publish!


= ADVANCED FEATURES =

* Add multiple conditions to any piece of content
* Lazy loading option for increased performance and cache-busting
* Works with popular WordPress hosting companies and industry-standard complex caching solutions
	- Server-side caching solutions like Redis
	- Database Object Caching
	- Asset Caching like minification and caching plugins
	- Newest PHP caching features like preloading
* Clean Uninstall (optionally remove all content on uninstall)
* Works with popular Geolocation Providers
	- [WPEngine](https://wpengine.com/solution-center/geo-target/)
	- [IpData](https://ipdata.co/) **(PRO ONLY)**
	- [IpInfo](https://ipinfo.io/) **(PRO ONLY)**
	- [IpStack](https://ipstack.com/) **(PRO ONLY)**

**MULTIPLE CONDITIONS TO ANY PIECE OF CONTENT (PRO ONLY)**
Add as many conditions as prefered to any content you can create with Gutenberg Blocks or popular page builders Elementor or Beaver Builder!

**EXTENSIVE CONDITION RULES (PRO ONLY)**
Easily specify rules for your Conditional Content:
1. Content is displayed when all conditions apply
2. Content is displayed when at least one condition applies
3. Content is displayed when none of the conditions apply
4. Content is displayed when at least one condition does not apply

== Installation ==

1. Go to your WordPress Dashboard
1. Click "Plugins", then "Add New"
1. Search for "conditional content"
1. Install and activate the Conditional Content by Crowd Favorite plugin
1. Use the plugin via the Dashboard menu item labeled Conditions


== Frequently Asked Questions ==

= What type of content does Conditional Content support? =
Conditional Content allows you to customize any element on the website, including titles, texts, images, videos, menu items, and design.

= Can I use Conditional Content with any theme? =
Yes! We have not found any theme that is incompatible with Conditional Content. If you are having issues, we will work with you to resolve the problem.

= Will Conditional Content still work if my website is cached? =
Yes! Leveraging the lazy-loading setting, Conditional Content will load your content asynchronously even if your web page is heavily cached.

== Screenshots ==
1. Settings Page
2. Add a Condition - Referrer
3. Add a Condition - Time & Date
4. Add a Condition - User Behaviour
5. Gutenberg Integration
6. Beaver Builder Integration
7. Elementor Integration

== Changelog ==
= 2.1.2 =
 - various miscellaneous code refactors
 - added plugin settings link

= 2.1.1 =
 - dynamically load admin assets

= 2.1.0 =
 - updated administrative UI
 - various bug fixes
 - added anchor support to CC Gutenberg block

= 2.0.1 =
 - fixed compatibility issue with conditions preview for Elementor and Beaver Builder

= 2.0.0 =
 - several performance optimizations & edge-case bug-fixes
 - added the new condition preview functionality, allowing users to preview what content will be loaded when certain conditions are applied
 - refactored Gutenberg implementation to work with a new Conditional Content Block, significantly improving compatibility and stability; **Please read the Upgrade Notice before updating!**
This is a major version update, bringing increased Gutenberg Blocks compatibility in the form of a new Conditional Content Block. **As such, your previously applied _Gutenberg_ conditions will stop working!**
After upgrading to version 3.0.0, a new Conditional Content Block will be available in the Gutenberg Editor. Please use this block to set your desired visibility rules and add your desired personalized content inside of this block.
**Estimated upgrade time: 1-5 minutes per block**

= 1.0.3 =
Fix notice throws when using Elementor Pro

= 1.0.2 =
Fix compatibility with free version of Elementor

= 1.0.1 =
Update README.txt

= 1.0.0 =
Initial Release

== Upgrade Notice ==
