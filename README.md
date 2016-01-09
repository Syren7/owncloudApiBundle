# owncloudApiBundle

This Bundle allows you to manage owncloud users throug provisioning api and managing files through sabre dav

# Installation

## Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require syren/owncloudApiBundle "1.1.*"
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

    owncloud_host: 'UrlToYouOwncloudServerWithTrailingSlash' #e.g. https://cloud.yourhost.com/ or https://yourhost.com/owncloud/
    owncloud_user: 'YourOwncloudUsername' #if you want to access user specific functions (like adding and removeing), make sure your owncloud user has enough rights
    owncloud_pass: 'YourOwncloudPassword'
    owncloud_folder: 'RootFolderForStoringFilesWithTrailingSlash' # You can add here some folder where you want to store your files. Leave empty if you want to user the users root directory

```

## Step 4: Using the Bundle

You have now two services for contacting an owncloud server. *syren7_owncloud.filesystem* for accessing the webDav service and *syren7_owncloud.user* for managing users, groups and subadmins on your owncloud instance.
```php
 // in some controller file you can do the following
 $this->get('syren7_owncloud.filesystem'); //using filesystem tools on owncloud
 $this->get('syren7_owncloud.user'); //managing users and groups
```

# Examples

This chaptes is currently missing. For the moment please read the documentation within the two service files [Service/OwncloudFilesystem.php](Service/OwncloudFilesystem.php) and [Service/OwncloudUser.php](Service/OwncloudUser.php)!

# Development

This bundle is still under development and if you would like to participate, let me know! You can also write an [email](mailto:konstantin@tuemmler.org)

# Donations

This is a completely free project and you can use it wherever you want. If you like this bundle and would like to donate some "coffee" feel free to do that on [PayPal](https://paypal.me/tuemmlerkon)

