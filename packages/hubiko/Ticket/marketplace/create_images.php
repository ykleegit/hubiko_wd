<?php
// Simple script to generate placeholder images for the marketplace

function createPlaceholderImage($width, $height, $filename, $text) {
    $image = imagecreatetruecolor($width, $height);
    
    // Set colors
    $bg_color = imagecolorallocate($image, 245, 245, 250);
    $text_color = imagecolorallocate($image, 50, 50, 150);
    $border_color = imagecolorallocate($image, 100, 100, 200);
    
    // Fill background
    imagefill($image, 0, 0, $bg_color);
    
    // Draw border
    imagerectangle($image, 0, 0, $width-1, $height-1, $border_color);
    
    // Add text
    $font_size = 5;
    $text_width = imagefontwidth($font_size) * strlen($text);
    $text_height = imagefontheight($font_size);
    
    $x = ($width - $text_width) / 2;
    $y = ($height - $text_height) / 2;
    
    imagestring($image, $font_size, $x, $y, $text, $text_color);
    
    // Save image
    imagepng($image, $filename);
    imagedestroy($image);
    
    echo "Created placeholder image: $filename\n";
}

// Create directory if it doesn't exist
$dir = __DIR__ . '/images';
if (!file_exists($dir)) {
    mkdir($dir, 0755, true);
}

// Create 4 placeholder images with different text
createPlaceholderImage(1200, 800, "$dir/image1.png", "Ticket Management Dashboard");
createPlaceholderImage(1200, 800, "$dir/image2.png", "Ticket Conversation View");
createPlaceholderImage(1200, 800, "$dir/image3.png", "Agent Assignment Interface");
createPlaceholderImage(1200, 800, "$dir/image4.png", "Categories and Priorities Management");

echo "All placeholder images created successfully.\n"; 