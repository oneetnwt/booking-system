<html lang="HTML5">

<head>
    <title>PHP Quick Start</title>
</head>

<body>
    <?php

    require '../vendor/autoload.php';

    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();

    // Use the Configuration class 
    use Cloudinary\Configuration\Configuration;

    $name = $_ENV['CLOUDINARY_NAME'];
    $key = $_ENV['CLOUDINARY_KEY'];
    $secret = $_ENV['CLOUDINARY_SECRET'];

    // Configure an instance of your Cloudinary cloud
    Configuration::instance('cloudinary://' . $key . ':' . $secret . '@' . $name . '?secure=true');

    // Use the UploadApi class for uploading assets
    use Cloudinary\Api\Upload\UploadApi;

    // Upload the image
    $upload = new UploadApi();
    echo '<pre>';
    echo json_encode(
        $upload->upload('https://res.cloudinary.com/demo/image/upload/flower.jpg', [
            'public_id' => 'flower_sample',
            'use_filename' => true,
            'overwrite' => true
        ]),
        JSON_PRETTY_PRINT
    );
    echo '</pre>';

    // Use the AdminApi class for managing assets
    use Cloudinary\Api\Admin\AdminApi;

    // Get the asset details
    $admin = new AdminApi();
    echo '<pre>';
    echo json_encode(
        $admin->asset('flower_sample', [
            'colors' => true
        ]),
        JSON_PRETTY_PRINT
    );
    echo '</pre>';

    // Use the Resize transformation group and the ImageTag class
    use Cloudinary\Transformation\Resize;
    use Cloudinary\Transformation\Background;
    use Cloudinary\Tag\ImageTag;

    // Create the image tag with the transformed image
    $imgtag = (new ImageTag('flower_sample'))
        ->resize(
            Resize::pad()
                ->width(400)
                ->height(400)
                ->background(Background::predominant())
        );

    echo $imgtag;
    // The code above generates an HTML image tag similar to the following:
//  <img src="https://res.cloudinary.com/demo/image/upload/b_auto:predominant,c_pad,h_400,w_400/flower_sample">