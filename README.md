# Responsive Simple Image Gallery
Simple Image Gallery based gallery plugin, with a focus on providing a responsive gallery for today's responsive websites.
Uses [Photoswipe](http://photoswipe.com/) and [Masonry](http://masonry.desandro.com/)

You can see this gallery in action on the [demo page](https://rsig.martinkus.eu/)

## Usage
To use this plugin simply download zip of this repository, install, enable it and include an activation tag in article where you want your gallery displayed.

Activation tag sintax:
`{gallery}my_gallery_folder{/gallery}`
by default root folder where plugin will look for mygalleryfolder defined in activation tag is images, this can be changed ins plugins advanced settings.

Caption file should be named `captions.txt` or `en-GB.txt` for multilanguage captions. Of course any langue code can be used.

Caption file example:
```
gal_1.jpg|Caption
gal_2.jpg|Another Caption
gal_3.jpg|Yet Another Caption
```
Images are currently ordered by name and not by order in the caption file.

## Settings
##### Layout
###### Flow
Displays images as inline elements, so they wrap around like text would.

###### Grid
Displays images in flexbox grid with specified number of collums per breakpoint.

###### Masonry
Displays images using [Masonry](http://masonry.desandro.com/) js library. Its recomended to set thumbnail hieght to 0 so that all images would have the same width (See below).

##### Static thumbnails
If you wish to provide thumbnails yourself, set this to true.
Thumbnails must have the same name as the main image, and must be put in thumbnails folder inside your gallery folder `my_gallery_folder/thumbnails`. If thumbnails for some images are missing, the plugin will attempt to generate them.

##### Thumbnail size
Thumbnails are resized to fit in the defined height and width, keeping original aspect ratio.

For examaple if you have an 1500x1000 image and set thubnail height to 200px and width to 200px image will be resized to 133x200, fitting the larger dimention, and resizing the other to keep the aspect ratio.

Its possible to set height to 0, so that all images would have same width and varying heights. Its also possible to set width to 0 to have same heigh and different width for all the images.

##### Image pixel density
Currently retina images have 2x the pixel density, and are loaded in all devices, in future this will be changed to use srcset attribute, and various sized images.

##### Overlay captions
Set to true if you want to use overlay captions. Pure CSS solution, won't look as good in IE8 or lower. 

##### Advanced settings
Here you can set:
* Starting (root) folder for your galleries
* Thumbnail quality
* Where from you wish to load requiered engines like Photoswipe and Masonry. By default they are loaded using CDN
* Options that are passed to Photoswipe, as per [Photoswipe documentation](http://photoswipe.com/documentation/options.html) 

Insert a space if you dont wish to pass any custom options to Photoswipe, by default `shareEl: false` option is passed.

If you want to get lightbox just like the Minimal style one in [Photoswipe demo](http://photoswipe.com/), pass these options to Photoswipe: 

`mainClass: 'pswp--minimal--dark', barsSize: {top:0,bottom:0}, captionEl: false, fullscreenEl: false, shareEl: false, bgOpacity: 0.85, tapToClose: true, tapToToggleControls: false`
