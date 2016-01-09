# owncloudApiBundle

This Bundle allows you to manage owncloud users throug provisioning api and managing files through sabre dav

# Installation

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require syren/owncloudApiBundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

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

Step 3: Configure the Bundle
----------------------------

You will need to configure three parameters for using this bundle.
It's recommend to add these settings with placeholders in your `app/config/config.yml` and add the credentials in `app/config/your parameters.yml`.
**IMPORTANT: Make sure that you're using https for encryption because passwords will be transmitted in plaintext**

```yaml
// app/config/config.yml

syren7_owncloud:
    host: '%owncloud_host%'
    user: '%owncloud_user%'
    pass: '%owncloud_password%'
    folder: '%owncloud_folder%'

```

```yaml
    // app/config/parameters.yml

    owncloud_host: 'UrlToYouOwncloudServerWithTrailingSlash' #e.g. https://cloud.yourhost.com/ or https://yourhost.com/owncloud/
    owncloud_user: 'YourOwncloudUsername'
    owncloud_password: 'YourOwncloudPassword'
    owncloud_folder: 'RootFolderForStoringFilesWithTrailingSlash' # You can add here some folder where you want to store your files. Leave empty if you want to user the users root directory

```