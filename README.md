# wellspring_forms

Wellspring Forms is a Wordpress plugin specifically for grabbing a list of forms from the CommunityChurchBuilders API
This has been written expressly for Wellspring Christian Church, but if you for some reason need exactly this functionality (a list of CCB forms filtered by "Available" and "Public") then technically the settings are flexible enough for you to join in the party!

## Installation

Install the downloaded .zip file into the wp-content/plugins directory of your site just like any other plugin

## Usage

You'll have to add your API credentials to the added settings page in the Wordpress Admin console.
You should see these under Settings > Community Church Builder API Options
Username and password need to be for a CCB user with API access, base url is "https://wellspringchristian.ccbchurch.com" at time of writing this README

The shortcode for adding the widget to a page is [wellspring_forms]
You can change the widget's title (default "Forms") with a shortcode attribute (Example: [wellspring_forms title="Signups!"]

## Contributing
I mean if you're inclined to contribute to this project, my guess is that it's because I'm gone.  So... clone it and go wild!

## License
[MIT](https://choosealicense.com/licenses/mit/)