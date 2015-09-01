An example of a way to upload files larger then 32MB(limitation of http post on AppEngine) to AppEngine using a PHP Application. PHP.ini file is used to declare max upload size and session time.

This commit is a basic boilerplate of how to use CloudSQL database.

You will need to deploy on cloud.google.com using AppEngine Launcher. Sorry, this version will give errors when testing on your local system.

Deploy to AppEngine

1. Create a AppEngine project in console.developers.google.com. Make a note of your project id

2. Under newly created project, browse to cloud storage and create a bucket. The name needs to be differen't from all the bucket names already existing on cloud stroage

3. Download this repo and unzip

4. Edit app.yaml file and replace your-project-id with your actual project id

5. Edit dbconn.php and replace values inside "" with correct values for $dbHost , $dbUser , $dbPass , $dbDatabaseName

6. Import existing project using GoogleAppEngineLanucher by clicking File -> Add Existing Application

7. Click browse and select unzipped folder from step 3 above.

8. Press deploy (blue button with up arrow)

9. Logs button to see what's going on under the hood as application is being deployed

Libraries Used

PHP App Engine Launcher and SDK
<a href="https://cloud.google.com/appengine/downloads" target="_blank">PHP App Engine Launcher and SDK</a>

CSS Library for performance and easier UI development
<a href="http://topcoat.io/" target="_blank">TopCoat.io</a>

Found this very easy to understand MySQL wrapper class:
(http://www.phpclasses.org/package/5205-PHP-MySQL-access-wrapper-based-on-static-functions.html)

Please see examples of how to use DB wrapper class under /notes/do_example.php


