<?php
function get_extension($exten)
{
    $extension = explode(".", $exten);
    return ($extension == '') ? $extension : 'jpeg';
}
if (isset($_FILES['photo'])) {
    $file = $_FILES['photo']['tmp_name'];
    $path = 'uploads/'; // upload directory
    $img = $_FILES['photo']['name'];
    $tmp = $_FILES['photo']['tmp_name'];

    $ext = get_extension($img);

    $final_image = 'Croped_' . time() . '.' . $ext;
    $path = $path . strtolower($final_image);
    move_uploaded_file($tmp, $path);
    exit(0);
}
