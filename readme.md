# Simple Search Algolia Driver

This package contains a Simple Search Driver for the Algolia search service.

## Basic Setup

Before installing, make sure you have signed up for an account at Algolia. (https://dashboard.algolia.com/users/sign_up)

In Algolia, make note of your Application ID, index name, and [Admin API key](https://dashboard.algolia.com/account/api-keys/all).

### Install the Extra

Install the extra through the MODX Package Manager. Go into System Settings and set your Application ID, Index Name and Admin API Key. 

Save a resource to generate the first record in Algolia.

### Configure the Index

Back on the Algolia Dashboard you should now see your first record in the index. 

Click on the index and then click on the "Configuration" tab. 

#### Searchable Attributes

Set your Searchable Attributes. These can be any field in your resource, I recommend starting out with pagetitle, longtitle, content, and description. 

#### Ranking and Sorting

Here you can set the order of the results. I recommend starting with this unaltered, but you can play around with it to see what works best for your site.

#### Facets

The algolia driver searches by the Context Key facet. So you will need to go into the Facets tab and add a new facet attribute "context_key" and set it to "filter only".

#### Other Settings in the Algolia Dashboard

You can play around with the other settings to see what works best for your site. Feel free to reference the [Algolia documentation](https://www.algolia.com/doc/) for more information on these settings.

### Additional Setup

Your site is ready to go at this point. However, I've included a couple of helpers to get you started. If you have an existing site and want to index all the data, you can run the following command in a console:

```
php /path/to/modx/core/components/ssalgolia/cron/2x.cron.php
```

If you are running MODX 3, you will replace 2x.cron.php with 3x.cron.php.

This will index all the resources in your site. If you have a large site, you may want to run this in batches. You can do this by adding a limit to the query in the cron file. 

#### Shrinking the Index

On a free Algolia account your are limited to 10kb record sizes. I've included a system setting to help reduce index sizes if you are running into this issue.

The system setting is called "ssalgolia.remove_common_words". If you set this to true, the driver will strip out common words from the index. This will reduce the size of the index, but it may also reduce the accuracy of the search results.

You can adjust which common words are removed by editing the "ssalgolia.common_words" system setting. This is a comma separated list of words to remove from the index.