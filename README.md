# NASA Astronomy Picture of the Day

Provides a page and block with NASA's Astronomy Image of the Day.

## Purpose

This module was designed as a training tool for learning the new Drupal 8 API's.
Full integration with other contributed modules will be added as the individual
project have stable releases.

## Version

Please checkout and use the 8.x-1.x branch as Master will remain empty.

## Usage

Once enabled, you will be able to use the following features:

* There will be a block named &quot;_Astronomy Picture of the Day Block_&quot; 
under Structure &raquo; Block Layout. You can add this block as you would any 
other Drupal Block.
* The full sized image can be accessed by going to _{site_url}_/astronomy-picture-of-the-day or 
_{site_url}_/astronomy-picture-of-the-day/{date} where {date} is any valid date either in the 
_YYYY-MM-DD_ format or a UNIX timestamp.

## TODO

This module should be refactored once the Media module for Drupal 8 has
been released.