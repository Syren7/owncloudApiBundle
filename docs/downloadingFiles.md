#Downloading OC files
This chapter gives you an example how to read files from your cloud and offer them as download to you or your users
```php
//---
//src/YourBundle/Controller/SomeController.php
//---

/**
 * @Route("/", name="SomeRouteName")
 */
public function downloadAction() {
    //get the filesystem service
    $fs = $this->get('syren7_owncloud.filesystem');
    //read file information from cloud
    $file = $fs->getFile('Some/Path/file.pdf');
    //create response and pass file content as data
    $response = new Response($file->read());
    /**
     * set content type to 'application/octet-stream' since some other mime types will be displayed in browser
     * like text or pdf (depends on installed browser extensions)
     */
    $response->headers->set('Content-Type', 'application/octet-stream');
    //set content disposition (default filename for file download)
    $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', basename($file->getPath())));
    //return your newly created response
    return $response;
}
```