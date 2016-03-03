# owncloudApiBundle

This Bundle allows you to manage owncloud users throug provisioning api and managing files through sabre dav

# Installation

## Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require syren7/owncloud-api-bundle "dev-master"
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

## Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel {
    public function registerBundles() {
        $bundles = array(
            // ...

            new Syren7\OwncloudApiBundle\Syren7OwncloudApiBundle(),
        );

        // ...
    }

    // ...
}
```

## Step 3: Configure the Bundle

You will need to configure three parameters for using this bundle.
It's recommend to add these settings with placeholders in your `app/config/config.yml` and add the credentials in `app/config/your parameters.yml`.

**IMPORTANT: Make sure that you're using https for encryption because passwords will be transmitted in plaintext**

```yaml
// app/config/config.yml

syren7_owncloud:
    host: '%owncloud_host%'
    user: '%owncloud_user%'
    pass: '%owncloud_pass%'
    folder: '%owncloud_folder%'

```

```yaml
    // app/config/parameters.yml

    owncloud_host: 'UrlToYouOwncloudServer' #e.g. https://cloud.yourhost.com/ or https://yourhost.com/owncloud/
    owncloud_user: 'YourOwncloudUsername' #if you want to access user specific functions (like adding and removeing), make sure your owncloud user has enough rights
    owncloud_pass: 'YourOwncloudPassword'
    owncloud_folder: 'RootFolderForStoringFiles' # You can add here some folder where you want to store your files. Leave empty if you want to user the users root directory

```

Take care about the `owncloud_folder` parameter. If you leave it empty, the bundle always uses the home (or root) directory of the user given in `owncloud_user`.
If you would like to store the data in a subfolder of the user, please set it in `owncloud_folder` **relative** to the users home directory!  
e.g. you have folder named *files of bob* in bobs home directory and you would like to store all files created by the bundle inside this directory, set `owncloud_folder` to `owncloud_folder: "files of bob/"`.  
  
As mentioned in the example `Basic example`:  
If you scan the directory with *getDirectoryContents()* with no parameters, the bundle will now scan *files of bob/* instead of bob's home directory.


## Step 4: Using the Bundle

You have now two services for contacting an owncloud server. *syren7_owncloud.filesystem* for accessing the webDav service and *syren7_owncloud.user* for managing users, groups and subadmins on your owncloud instance.
```php
 // in some controller file you can do the following
 $this->get('syren7_owncloud.filesystem'); //using filesystem tools on owncloud
 $this->get('syren7_owncloud.user'); //managing users and groups
 $this->get('syren7_owncloud.calendar'); //reading calendars and events
```

# Debbuging

For debugging purposes you could call the getLastRequest() Method from the service. It will return the response from your OwnCloud server. So you can see which type of error was thrown corresponding to the docs on OwnCloud Api.
```php
#Example of an response from server

```

# Examples

## Basic example

If you want to work with this api without symfony you can do this simply with the following example:
```php
//Basic example for the filesystem api  
  
//edit the following line, if you have your vendors not installed to vendor/ Folder  
require_once 'vendor/autoload.php';  
//create a new object with your owncloud credentials  
$fs = new \Syren7\OwncloudApiBundle\Service\OwncloudFilesystem('YourHostNameHere', 'YourOcUserName', 'YourOcPassword', 'LeaveBlankIfYouWantToWriteIntoUsersRootDirectory');  
  
//do your operations as you would do in symfony  
//List all files and directories (optional give a subdirectory as parameter)  
$fs->getDirectoryContents('Optional:YourPathToADirectory');
  
//returns a file handle from Oc. Filname as parameter  
$file = $fs->getFile('Path/To/File.txt');
//write file to local filesystem
file_put_contents('Path/To/New/Local/File.txt', $file->read());

//create a directory  
$fs->createDirectory('Path/To/New/Directory/On/Your/Oc');
  
//remove a directory  
$fs->removeDirectory('Path/To/Remove/Directory/On/Your/Oc');
  
//uploads a file to your oc  
$fs->createFile('Local/Path/To/File.txt', 'Optional: Remote/Path/To/File');
  
//removes a file from your oc  
$fs->removeFile($path='');  
  
```

# Development

This bundle is still under development and if you would like to participate, let me know! You can also write an [email](mailto:konstantin@tuemmler.org)

# Donations

This is a completely free project and you can use it wherever you want. If you like this bundle and would like to donate some "coffee" feel free to do that on [PayPal](https://paypal.me/tuemmlerkon)

