| :exclamation:  This functionality in this repository has been merged with the  [NASA Astronomy Picture of the Day module](https://www.drupal.org/project/nasa_apod) on Drupal.org and is no longer maintained here. |
|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
# NASA Astronomy Picture of the Day

Provides a page and block with NASA's Astronomy Image of the Day.

## Purpose

This module was designed as a training tool for learning the Drupal 8 API's
and has been updated to support Drupal ^9.5 and ^10.

## Installing using Composer

To install this module using composer, you will first need to add this repository to your <code>composer.json</code>
file. In the _repositories_ section of your composer.json file, add the following:

<pre>
{
    "type": "vcs",
    "url": "git@github.com:r0nn1ef/apod.git"
}
</pre>

Once you add the repository, you can install the module as normal using the following to get the latest release:

<pre>
composer require r0nn1ef/apod:^2.0
</pre>

## Suggested Configuration

NASA does not require an API key to use the API associated with this module.
However, without an API key, rate limiting calls will be introduced. To apply
for an key, visit [https://api.nasa.gov/index.html#apply-for-an-api-key](https://api.nasa.gov/index.html#apply-for-an-api-key).

Once you have received your API key, log in to your website and browse to 
Administration &raquo; Configuration &raquo; Web Services &raquo; Astronomy Picture of the Day Settings

## Usage

Once enabled, you will be able to use the following features:

* There will be a block named &quot;_Astronomy Picture of the Day Block_&quot; 
under Structure &raquo; Block Layout. You can add this block as you would any 
other Drupal Block.
* The full sized image can be accessed by going to _{site_url}/astronomy-picture-of-the-day_ or 
_{site_url}/astronomy-picture-of-the-day/{date}_ where {date} is any valid date either in the 
_YYYY-MM-DD_ format or a UNIX timestamp.

## TODO

Refactor to use dependency injection for services, routes, and blocks.