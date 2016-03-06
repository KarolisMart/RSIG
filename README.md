# Responsive Simple Image Gallery
Another Simple Image Gallery based gallery plugin, this time with a focus on providing responsive gallery for todays responsive websites.
Uses [Photoswipe](http://photoswipe.com/) and [Masonry](http://masonry.desandro.com/)

## Usage
To use this plugin simply download zip of this repository, install, enable it and include an activation tag in article where you want your gallery displayed.

Activation tag sintax:
`{gallery}mygalleryfolder{gallery}`
by default root folder where plugin will look for mygalleryfolder defined in activation tag is images, this can be changed ins plugins advanced settings.

## Settings
##### Layout
###### Flow
Displays images as inline elements, so they wrap around like text would.

###### Grid
Displays images in bootstrap grid in specified number of collums per breakpoint.

###### Masonry
Displays images using [Masonry](http://masonry.desandro.com/) js library. Its recomended to set thumbnail hieght to 0 so that all images would have the same width (See below).

##### Thumbnail size
Thumbnails are resized to fit in the defined height and width, keeping original aspect ratio.

For examaple if you have an 1500x1000 image and set thubnail height to 200px and width to 200px image will be resized to 133x200, fitting the larger dimention, and resizing the other to keep the aspect ratio.

Its possible to set height to 0, so that all images would have same width and varying heights. Its also possible to set width to 0 to have same heigh and different width for all the images.

##### Image pixel density
Currently retina images have 2x the pixel density, and are loaded in all devices, in future this will be changed to use srcset attribute, and various sized images.

##### Advanced settings
Here you can set:
* Starting (root) folder for your galleries
* Thumbnail quality
* Where from you wish to load requiered engines like Photoswipe and Masonry. By default they are loaded using CDN
* Options that are passed to Photoswipe, as per [Photoswipe documentation](http://photoswipe.com/documentation/options.html) 

Insert a space if you dont wish to pass any custom options to Photoswipe, by default `shareEl: true` option is passed.

If you want to get lightbox just like the Minimal style one in [Photoswipe demo](http://photoswipe.com/), pass these options to Photoswipe: 

`mainClass: 'pswp--minimal--dark', barsSize: {top:0,bottom:0}, captionEl: false, fullscreenEl: false, shareEl: false, bgOpacity: 0.85, tapToClose: true, tapToToggleControls: false`

## Coming soon
* Srcset support
* Captions with caption.js and sigplus like caption files
* Ability to set prameters in  activation tags.
* Proper demo. For now you can see this gallery in action in my [test page](https://test.martinkus.eu/)
