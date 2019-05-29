<?php
$arguments = $argv;
array_shift($arguments);

$holder = array();

$total_width = 1;
$total_height = 1;
$width_start = 1;

$sprite_name = "sprite.png";
$css_name = "style.css";


if (empty($arguments)) {
    print("Please specify a file");
    return 0;
} else {
    switch ($arguments[0]) {

        case "-r":
        case "-recursive":
            parse_dirs_r($arguments[$argc - 2], $holder);
            get_image_dimensions($holder);
            break;

        case "-i":
        case "-output-image=":
            global $sprite_name;
            $sprite_name = $arguments[$argc - 3];
            parse_dirs_s($arguments[$argc - 2], $holder);
            get_image_dimensions($holder);
            break;

        case "-s":
        case "-output-style=":
            global $css_name;
            $css_name = $arguments[$argc - 3];
            parse_dirs_s($arguments[$argc - 2], $holder);
            get_image_dimensions($holder);
            break;

        default:
            parse_dirs_s($arguments[$argc - 2], $holder);
            get_image_dimensions($holder);
            break;
    }
}


function parse_dirs_s($directory = null, &$temp = null, $counter = 0)
{
    if ($opened_directory = opendir($directory)) {
        while (($directory_file = readdir($opened_directory)) != false) {
            if ($directory_file != "." && $directory_file != ".." && check_image_type($directory . '/' . $directory_file)) {
                $temp[$counter] = $directory . '/' . $directory_file;
                $counter++;
            }
        }
    }
}


function parse_dirs_r($directory = null, &$temp = null, $counter = 0)
{
    if ($opened_directory = opendir($directory)) {
        while (($directory_file = readdir($opened_directory)) != false) {
            if (is_dir($directory . '/' . $directory_file) && $directory_file != "." && $directory_file != "..") {
                parse_dirs_r($directory . '/' . $directory_file, $temp, $counter++);
            } else if ($directory_file != "." && $directory_file != ".." && check_image_type($directory . '/' . $directory_file)) {
                $temp[$counter] = $directory . '/' . $directory_file;
                $counter++;
            }
        }
    }
}


function check_image_type($file = null)
{
    if (@exif_imagetype($file) === IMAGETYPE_PNG) {
        $pic_convertor = imagecreatefrompng($file);
        return $pic_convertor;
    } else if (@exif_imagetype($file === IMAGETYPE_JPEG)) {
        $pic_convertor = imagecreatefromjpeg($file);
        return $pic_convertor;
    } else if (@exif_imagetype($file === IMAGETYPE_GIF)) {
        $pic_convertor = imagecreatefromgif($file);
        return $pic_convertor;
    } else {
        print("$file is not a valid image\n");
        return false;
    }
}


function get_image_dimensions($holder = null)
{
    global $holder;
    global $total_width;
    global $total_height;

    foreach ($holder as $file) {
        list($width, $height) = getimagesize($file);
        $total_width = $width + $total_width;
        $total_height = $height + $total_height / $total_width;
    }
    $global = get_background_dimensions($total_width, $total_height);
    create_sprite($holder, $global, $width, $height);
    generate_css($holder, $width, $height);
}


function get_background_dimensions($total_width = null, $total_height = null)
{
    $background = imagecreatetruecolor($total_width, $total_height);
    $transparency = imagecolorallocate($background, 0, 0, 0);
    imagecolortransparent($background, $transparency);
    return $background;
}


function create_sprite($holder = null, $final_background = null, $width = null, $height = null)
{
    global $sprite_name;
    global $holder;
    global $width_start;

    foreach ($holder as $file) {
        $pic = check_image_type($file);
        imagecopyresampled($final_background, $pic, $width_start, 0, 0, 0, $width, $height, $width, $height);
        $width_start = $width_start + $width;
    }
    imagepng($final_background, $sprite_name);
}


function generate_css($holder = null, $width = null, $height = null)
{
    global $css_name;

    $opened_css = fopen($css_name, 'w');
    foreach ($holder as $images) {
        $image_data = basename($images, ".png");
        fwrite($opened_css, "." . $image_data . "{width: " . $width . "px; height: " . $height . "px; background-image: url(" . $images . "); text-align:left; }" . "\n");
    }
    fclose($opened_css);
}