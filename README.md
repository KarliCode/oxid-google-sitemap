# oxid-google-sitemap
Very simple google Sitemap Script.

The script will create a sitemap.xml as index sitemap, which can be used to register in google search and should be mentioned in the robots.txt.
Further it will create one or more xml files (depending on the amount of sites you have), which will contain all urls.

See example files.

# Works with:
 Tested with Oxid EShop CE & PE v 6.1.5 and PHP 7.1

# Installation:

1. Create a sitemap folder within the source folder of the oxid installation.
2. Copy the google_sitemap.php file into this folder
3. Adjust the settings in the head of the google_sitemap.php file

# How to use:
- Call it via url: yourshop.url/sitemap/google_sitemap.php
- Create a cronjob
- Call it via CLI in your server backend
