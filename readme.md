An example of a way to upload files larger then 32MB(limitation of http post on AppEngine) to AppEngine using a PHP Application, PHP.ini file is used to declare max upload size and session time.

1. Download this repo
2. Add as an existing Application to AppEngine SDK Launcher
3. Test in local AppEngine sdk..uploaded files can be found SDK Console under Datastore Viewer

OR Deploy to AppEngine

3. Create a AppEngine project in console.developer.google.com 
4. Under newly created project, browse to cloud storage and create a bucket
5. Edit upload.php and replace "enter-storage-bucket-name" with name of bucket you created.
6. Deploy
